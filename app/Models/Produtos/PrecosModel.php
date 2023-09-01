<?php

namespace App\Models\Produtos;

use App\Libraries\DatabaseUtils;
use Illuminate\Support\Facades\DB;
use App\Libraries\ArrayUtils;
use App\Libraries\PaginacaoUtils;

class PrecosModel
{
    private static $conn = 'produtos';

    public static function porFiliais($filiais, $limit = null, $offset = null)
    {
        if (is_string($filiais)) {
            $filiais = [$filiais];
        }

        if (!empty($filiais)) {
            $params = array_merge($filiais);

            $fill_filiais = array_fill(0, count($filiais), '?');
            $fill_filiais = implode(',', $fill_filiais);

            /*** IMPORTANTE PARA PAGINAÇÃO E EXPORTAÇÃO *** */
            if(!empty($limit)){
                $filtro_limites = "LIMIT ? OFFSET ?";
                $params[] = (int)$limit;
                $params[] = PaginacaoUtils::offset($limit, $offset);
            }

            $sql = "SELECT
                        pp.*,
                        p.venda_minima,
                        count(*) over() as _total
                    FROM
                        precos pp
                        INNER JOIN produtos p ON pp.referencia=p.referencia
                    WHERE
                        pp.filial IN ($fill_filiais)
                    -- LIMIT 100
                    $filtro_limites
                    ";
            // dd(DatabaseUtils::debug(self::$conn, $sql, $params));
            $result =  DatabaseUtils::select(self::$conn, $sql, $params);

            return [
                'total' => $result[0]->_total ?? 0,
                'response' => $result,
            ];
        } else {
            return [];
        }
    }

}
