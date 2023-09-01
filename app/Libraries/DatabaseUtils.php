<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;

class DatabaseUtils extends DB
{
    public const DIFF_TODOS_CAMPOS = ['*'];
    public const DIFF_NENHUM_CAMPOS = [];

    private static function prepareParams(&$params)
    {
        $params = array_map(function ($elem) {
            if (is_null($elem)) {
                return null;
            } else {
                return (string) $elem;
            }
        }, $params);
    }

    public static function replaceTable($conn, $tbl, $data)
    {
        $tbl_new = $tbl . "_new";
        $tbl_old = $tbl . "_old";
        self::statement($conn, "DROP TABLE IF EXISTS $tbl_new");
        self::statement($conn, "CREATE TABLE $tbl_new LIKE $tbl");
        self::replaceBatch($conn, $tbl_new, $data);
        self::statement($conn, "RENAME TABLE $tbl TO $tbl_old, $tbl_new TO $tbl");
        self::statement($conn, "DROP TABLE IF EXISTS $tbl_old");
    }

    public static function processaArrayUtilsCompareDiff($conn, $tbl, $keys, $diff_result)
    {

        // if (!empty($columnToIndex)) {
        //     $diff_result['INSERT'] = ArrayUtils::columnToIndex($diff_result['INSERT'], $columnToIndex);
        //     $diff_result['UPDATE'] = ArrayUtils::columnToIndex($diff_result['UPDATE'], $columnToIndex);
        //     $diff_result['DELETE'] = ArrayUtils::columnToIndex($diff_result['DELETE'], $columnToIndex);
        // }

        // if (count($diff_result['INSERT'])) {
        //     dd($diff_result['INSERT']);
        // }

        dump('$diff_result[INSERT]: ' . count($diff_result['INSERT']));
        self::replaceBatch($conn, $tbl, $diff_result['INSERT']);
        dump('$diff_result[UPDATE]: ' . count($diff_result['UPDATE']));
        self::updateBatch($conn, $tbl, $keys, $diff_result['UPDATE']);
        dump('$diff_result[DELETE]: ' . count($diff_result['DELETE']));
        self::deleteBatch($conn, $tbl, $keys, $diff_result['DELETE']);
    }

    public static function replaceBatch($connection, $table, $data)
    {
        if (!count($data)) {
            return;
        }

        $data = array_map(function ($elem) {
            return (array) $elem;
        }, $data);
        // dump('total: ' . count($data));
        $dataChunk = array_chunk($data, 65000 / count($data[array_key_first($data)]));

        foreach ($dataChunk as $data) {
            // dump('$data: ' . count($data));
            $firstKey = array_key_first($data);
            $fields = implode(',', array_keys($data[$firstKey]));
            $values = array_fill(0, count($data[$firstKey]), '?');
            $values = '(' . implode(',', $values) . ')';
            $values = array_fill(0, count($data), $values);
            $values = implode(',', $values);
            $sql = sprintf('REPLACE INTO %s(%s) VALUES %s', $table, $fields, $values);
            $tmp = [];
            foreach ($data as $dt) {
                $tmp = array_merge($tmp, array_values($dt));
            }
            // dump($sql, $tmp);
            self::statement($connection, $sql, $tmp);
        }
    }

    public static function insertBatch($connection, $table, $data)
    {
        if (!count($data)) {
            return;
        }

        $data = array_map(function ($elem) {
            return (array) $elem;
        }, $data);

        $dataChunk = array_chunk($data, 65000 / count($data[array_key_first($data)]));

        foreach ($dataChunk as $data) {
            $firstKey = array_key_first($data);
            $fields = implode(',', array_keys($data[$firstKey]));
            $values = array_fill(0, count($data[$firstKey]), '?');
            $values = '(' . implode(',', $values) . ')';
            $values = array_fill(0, count($data), $values);
            $values = implode(',', $values);
            $sql = sprintf('INSERT INTO %s(%s) VALUES %s', $table, $fields, $values);
            $tmp = [];
            foreach ($data as $dt) {
                $tmp = array_merge($tmp, array_values($dt));
            }
            self::statement($connection, $sql, $tmp);
        }
    }

    public static function updateBatch($connection, $table, $key, $infos)
    {
        $diferencas = [];
        if (count($infos)) {
            if (is_string($key)) {
                $innerJoinCondition = "t1.$key = t2.data->>\"$.$key\"";
            } else {
                $tmpJoin = [];
                foreach ($key as $kk) {
                    $tmpJoin[] = "t1.$kk = t2.data->>\"$.$kk\"";
                }
                $innerJoinCondition = implode(' AND ', $tmpJoin);
            }

            // GARANTINDO QUE OS OBJETOS DE DADOS SAO ARRAYS
            $infos = array_map(function ($elem) {
                return (array) $elem;
            }, $infos);

            // O NOME DA TABELA TEMPORARIA CONSISTE DO NOME DA TABELA PASSADO COM UM '_' COMO PREFIXO
            $tmpTableName = '_' . $table;
            // ---------------------------------------------------------------------------------------
            self::statement($connection, "DROP TEMPORARY TABLE IF EXISTS $tmpTableName;", []);

            // OBTENDO AS COLUNAS QUE SERAO ATUALIZADAS
            $columns = array_keys($infos[array_key_first($infos)]);

            // ---------------------------------------------------------------------------------------
            // CRIANDO A TABELA TEMPORARIA QUE ARMAZENARA TEMPORARIAMENTE OS DADOS DURANTE A ATUALIZACAO
            // COM APENAS UMA COLUNA NO FORMATO JSON. ISSO ABTRAI A COMPLEXIDADE DE MODELAGEM DELA.
            $tmptableSql = "CREATE TEMPORARY TABLE  $tmpTableName
            (
                `data` JSON NULL
            );";
            self::statement($connection, $tmptableSql, []);
            // ---------------------------------------------------------------------------------------
            // POPULANDO A TABELA TEMPORARIA COM OS DADOS QUE SERA ATUALIZADOS
            $infoChunks = array_chunk($infos, 60000 / count($infos[array_key_first($infos)]));
            foreach ($infoChunks as $k => $infoChunk) {
                $colParams = array_fill(0, count($infoChunk), '(?)');
                $colParams = implode(',', $colParams);
                $tmpTableInsertSql = "INSERT INTO $tmpTableName(data) VALUES $colParams";
                $params = [];
                foreach ($infoChunk as $info) {
                    $params[] = json_encode($info);
                }
                self::statement($connection, $tmpTableInsertSql, $params);
                // dd('fff');
            }
            // ---------------------------------------------------------------------------------------
            // FORMATANDO E EXECUTANDO UM UPDATE JOIN
            $sets = [];
            foreach ($columns as $col) {
                $sets[] = "t1.$col = t2.data->>\"$.$col\"";
            }
            $sets = implode(',', $sets);
            $tmpTableUpdate =
                "UPDATE $table t1
                    INNER JOIN $tmpTableName t2 ON $innerJoinCondition
                SET
                    $sets";
            // dump($tmpTableUpdate);
            self::statement($connection, $tmpTableUpdate, []);
            return $diferencas;
        }
    }

    public static function deleteBatch($connection, $table, $keys, $infos)
    {

        if (!empty($keys) && !empty($infos)) {
            if (is_string($keys)) {
                $keys = [$keys];
            }
            $sql = "DELETE FROM $table WHERE ";
            $where = [];
            foreach ($keys as $key) {
                $where[] = "$key=?";
            }
            $sql .= implode(' AND ', $where);

            foreach ($infos as $info) {
                $params = [];
                foreach ($keys as $key) {
                    $params[] = $info->{$key};
                }
                // dump($sql, $params);
                self::delete($connection, $sql, $params);
            }
        }
    }

    public static function deleteBatchBy($connection, $table, $key, $values)
    {
        // dd($connection, $table, $key, $values);
        if (!empty($key) && !empty($values)) {
            $fill = array_fill(0, count($values), '?');
            $fill = implode(',', $fill);
            $sql = "DELETE FROM $table WHERE $key IN ($fill)";
            self::delete($connection, $sql, array_values($values));
        }
    }

    // public static function updateBatchInfo($infos, $table, $key, $connection, $computarAtualizacoes = self::DIFF_NENHUM_CAMPOS)
    // {
    //     // dump($infos);
    //     $diferencas = [];
    //     if (count($infos)) {
    //         if (is_string($key)) {
    //             $innerJoinCondition = "t1.$key = t2.data->>\"$.$key\"";
    //         } else {
    //             $tmpJoin = [];
    //             foreach ($key as $kk) {
    //                 $tmpJoin[] = "t1.$kk = t2.data->>\"$.$kk\"";
    //             }
    //             $innerJoinCondition = implode(' AND ', $tmpJoin);
    //         }

    //         // GARANTINDO QUE OS OBJETOS DE DADOS SAO ARRAYS
    //         $infos = array_map(function ($elem) {
    //             return (array) $elem;
    //         }, $infos);

    //         // O NOME DA TABELA TEMPORARIA CONSISTE DO NOME DA TABELA PASSADO COM UM '_' COMO PREFIXO
    //         $tmpTableName = '_' . $table;
    //         // ---------------------------------------------------------------------------------------
    //         self::statement($connection, "DROP TEMPORARY TABLE IF EXISTS $tmpTableName;", []);

    //         // OBTENDO AS COLUNAS QUE SERAO ATUALIZADAS
    //         $columns = array_keys($infos[array_key_first($infos)]);

    //         // ---------------------------------------------------------------------------------------
    //         // CRIANDO A TABELA TEMPORARIA QUE ARMAZENARA TEMPORARIAMENTE OS DADOS DURANTE A ATUALIZACAO
    //         // COM APENAS UMA COLUNA NO FORMATO JSON. ISSO ABTRAI A COMPLEXIDADE DE MODELAGEM DELA.
    //         $tmptableSql = "CREATE TEMPORARY TABLE  $tmpTableName
    //         (
    //             `data` JSON NULL
    //         );";
    //         self::statement($connection, $tmptableSql, []);
    //         // ---------------------------------------------------------------------------------------
    //         // POPULANDO A TABELA TEMPORARIA COM OS DADOS QUE SERA ATUALIZADOS
    //         $infoChunks = array_chunk($infos, 1000);
    //         foreach ($infoChunks as $k => $infoChunk) {
    //             $colParams = array_fill(0, count($infoChunk), '(?)');
    //             $colParams = implode(',', $colParams);
    //             $tmpTableInsertSql = "INSERT INTO $tmpTableName(data) VALUES $colParams";
    //             $params = [];
    //             foreach ($infoChunk as $info) {
    //                 $params[] = json_encode($info);
    //             }
    //             self::statement($connection, $tmpTableInsertSql, $params);
    //         }
    //         // ---------------------------------------------------------------------------------------
    //         if (!empty($computarAtualizacoes)) {
    //             $computarAtualizacoes = ($computarAtualizacoes == self::DIFF_TODOS_CAMPOS) ? $colParams : $computarAtualizacoes;
    //             // dd($computarAtualizacoes);
    //             $diffs = [];
    //             $camposComparados = [];
    //             foreach ($columns as $col) {
    //                 $diffs[] = "t1.$col <> t2.data->>\"$.$col\"";
    //                 $camposComparados[] = "t1.$col as t1_$col";
    //                 $camposComparados[] = "t2.data->>\"$.$col\" as t2_$col";
    //             }
    //             $diffs = implode(' OR ', $diffs);
    //             $camposComparados = implode(',', $camposComparados);

    //             $tmpTableDiff =
    //                 "SELECT
    //                     $camposComparados
    //                 FROM
    //                     $table t1
    //                     INNER JOIN $tmpTableName t2 ON $innerJoinCondition
    //                 WHERE $diffs";

    //             $diferencas = self::select($connection, $tmpTableDiff, []);
    //             foreach ($diferencas as &$elem) {
    //                 $diff = [];
    //                 if (is_string($key)) {
    //                     $diff[$key] = $elem->{"t1_$key"};
    //                 } else {
    //                     foreach ($key as $kk) {
    //                         $diff[$kk] = $elem->{"t1_$kk"};
    //                     }
    //                 }

    //                 foreach ($columns as $col) {
    //                     if ($elem->{"t1_$col"} != $elem->{"t2_$col"}) {
    //                         $diff[$col] = [
    //                             'new' => $elem->{"t2_$col"},
    //                             'old' => $elem->{"t1_$col"},
    //                         ];
    //                     }
    //                 }
    //                 $elem = $diff;
    //             }
    //         }
    //         // ---------------------------------------------------------------------------------------
    //         // FORMATANDO E EXECUTANDO UM UPDATE JOIN
    //         $sets = [];
    //         foreach ($columns as $col) {
    //             $sets[] = "t1.$col = t2.data->>\"$.$col\"";
    //         }
    //         $sets = implode(',', $sets);
    //         $tmpTableUpdate =
    //             "UPDATE $table t1
    //                 INNER JOIN $tmpTableName t2 ON $innerJoinCondition
    //             SET
    //                 $sets";
    //         // dump($tmpTableUpdate);
    //         self::statement($connection, $tmpTableUpdate, []);
    //         return $diferencas;
    //     }
    // }

    public static function beginTransaction($connection)
    {
        self::statement($connection, 'START TRANSACTION');
    }

    public static function commitTransaction($connection)
    {
        self::statement($connection, 'COMMIT');
    }

    // public static function rollbackTransaction($connection)
    // {
    //     self::statement($connection, 'ROLLBACK');
    // }

    public static function replaceInto($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        return DB::connection($connection)->statement($sql, $params);
    }

    public static function delete($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        return DB::connection($connection)->statement($sql, $params);
    }

    public static function select($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        $rsp = DB::connection($connection)->select($sql, $params);
        return $rsp;
    }

    public static function statement($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        return DB::connection($connection)->statement($sql, $params);
    }

    public static function update($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        return DB::connection($connection)->update($sql, $params);
    }

    public static function insert($connection, $sql, $params = [])
    {
        DB::connection($connection)->insert($sql, array_values((array) $params));
        return DB::connection($connection)->getPdo()->lastInsertId();
    }

    public static function upsert($connection, $table, $valeus, $keys)
    {
        return DB::connection($connection)->table($table)->upsert($valeus, $keys);
    }

    public static function insertLarvel(string $connection, string $table, string $valeus)
    {
        DB::connection($connection)->table($table)->create(["json" => json_encode($valeus)]);
    }

    public static function debug($connection, $sql, $params = [])
    {
        self::prepareParams($params);
        foreach ($params as $param) {
            $sql = preg_replace('|\?|', "'$param'", $sql, 1);
        }

        echo "<pre>
        Connexao: $connection
        Consulta: $sql
        </pre>";
        // dd();
    }

    // public static function formatRowFill($data)
    // {
    //     $colCount = count($data);
    //     $fill = array_fill(0, $colCount, '?');
    //     $fill = implode(',', $fill);
    //     return "($fill)";
    // }
}
