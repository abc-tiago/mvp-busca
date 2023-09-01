<?php

namespace App\Models\Produtos;

use App\Libraries\DatabaseUtils;
use Illuminate\Support\Facades\DB;
use App\Libraries\PaginacaoUtils;

class ProdutosModel
{
    private static $conn = 'produtos';


    /* TIAGO */
    public static function todosProdutos($limit = null, $offset = null)
    {
        /*** IMPORTANTE PARA PAGINAÇÃO E EXPORTAÇÃO *** */
        if(!empty($limit)){
            $filtro_limites = "LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = PaginacaoUtils::offset($limit, $offset);
        }


            $sql = "SELECT
                        p.referencia,
                        c3.nome departamento,
                        c2.nome categoria,
                        c1.nome subcategoria,
                        f.nome  fornecedor,
                        p.nome_original,
                        p.nome_amigavel,
                        p.modelo,
                        b.termos,
                        count(*) over() as _total
                    FROM produtos p
                        LEFT JOIN categorias                      c1  ON p.id_categoria=c1.id
                        LEFT JOIN categorias                      c2  ON c1.pai=c2.id
                        LEFT JOIN categorias                      c3  ON c2.pai=c3.id
                        LEFT JOIN fornecedores                    f   ON p.id_fornecedor=f.id
                        LEFT JOIN buscas						  b   ON b.referencia = p.referencia
                    WHERE 1=1
                        AND p.disponibilidade in('DE LINHA', 'ENCOMENDA')
                        $filtro_limites
                ";

       // dd(DatabaseUtils::debug(self::$conn, $sql, $params));
       $result =  DatabaseUtils::select(self::$conn, $sql, $params);

       return [
           'total' => $result[0]->_total ?? 0,
           'response' => $result,
       ];

    }

    public static function porReferencia($referencias)
    {
        if (!empty(count($referencias))) {

            $refValues = array_values($referencias);
                $params = $refValues;
                $condicao_referencias = array_fill(0, count($referencias), '?');
                $condicao_referencias = implode(',', $condicao_referencias);
                $condicao_referencias = " AND p.referencia IN ($condicao_referencias)";


            $sql = "SELECT
                        IF(exists(
                            select 1
                            from tintometrico t
                            where p.codpro=t.codigo_produto_final_erp
                        ), 'TINTOMETRICO', 'CONVENCIONAL') tipo,
                        c3.id   departamento_id,
                        c3.nome departamento,
                        c2.id   categoria_id,
                        c2.nome categoria,
                        c1.id   subcategoria_id,
                        c1.nome subcategoria,
                        f.id    fornecedor_id,
                        f.nome  fornecedor,
                        p.*
                    FROM produtos p
                        LEFT JOIN categorias                      c1  ON p.id_categoria=c1.id
                        LEFT JOIN categorias                      c2  ON c1.pai=c2.id
                        LEFT JOIN categorias                      c3  ON c2.pai=c3.id
                        LEFT JOIN fornecedores                    f   ON p.id_fornecedor=f.id
                    WHERE 1=1
                        AND p.disponibilidade in('DE LINHA', 'ENCOMENDA')
                        $condicao_referencias
                ";

            return DatabaseUtils::select(self::$conn, $sql, $params);
        } else {
            return [];
        }

    }

}
