<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Produtos\ProdutosModel;
use App\Models\Produtos\PrecosModel;
use App\Models\Produtos\ImagensModel;
use App\Libraries\ArrayUtils;
use App\Libraries\PaginacaoUtils;
use App\Libraries\StringUtils;

class ProdutosService
{

    public static function buscarProdutoComPaginacao(int $pagina, int $ipp)
    {

        $arr = [];

        // $precos = PrecosModel::porFiliais($filial, $ipp, $pagina);
        // $total = $precos['total'];

        // $arrPrecos = array_column($precos['response'], 'referencia');
        // $precos = ArrayUtils::columnToIndex($precos['response'], 'referencia');


        $produtos =  ProdutosModel::todosProdutos($ipp, $pagina);
        $total = $produtos['total'];
        $arrProdutos = array_column($produtos['response'], 'referencia');

        $imanges = ImagensModel::porReferenciasImagemPrincipal($arrProdutos);

        //transforme
        foreach($produtos['response'] as $produto) {
            $arr[] = [
                "referencia" => $produto->referencia,
                "nome_original" => $produto->nome_original,
                "nome_amigavel" => $produto->nome_amigavel,
                "slug" => !empty($produto->nome_amigavel) ?
                    StringUtils::slugify($produto->nome_amigavel) : // "Base de Registro de Pressão 3/4\" 4416 Deca",
                    StringUtils::slugify($produto->nome_original), // "DECA BASE REG PRE 4416 3/4"
                "departamento" => $produto->departamento,
                "categoria" => $produto->categoria,
                "subcategoria" => $produto->subcategoria,
                "fornecedor" => $produto->fornecedor,
                "modelo" => $produto->modelo,
                "termos" => $produto->termos,

                "imagem" => !empty($imanges[$produto->referencia]) ? $imanges[$produto->referencia][0]->url : '',
            ];
        }

        return PaginacaoUtils::extendida($arr, $total, $ipp, $pagina, 1, []);

    }

}
