<?php

namespace App\Libraries;

use stdClass;

class PaginacaoUtils
{
    public static function formataLink($parametros, $pagina)
    {
        $parametros['pagina'] = $pagina;
        return '?' . http_build_query($parametros);
    }

    public static function extendida($dados, $total, $ipp, $pagina, $paginaIdx, $filtros)
    {

        $resultado = (object) [];
        $resultado->dados = $dados;

        $resultado->paginacao = (object) [];
        $resultado->paginacao->ipp = $ipp;
        $resultado->paginacao->pagina = $pagina == 0 ? 1 : $pagina;
        $resultado->paginacao->total = $total;
        $resultado->paginacao->links = (object) [];

        while ($paginaIdx <= ceil($total / $ipp)) {
            $resultado->paginacao->links->{$paginaIdx} = (object) [];
            $resultado->paginacao->links->{$paginaIdx}->label = $paginaIdx;
            $resultado->paginacao->links->{$paginaIdx}->pagina = $paginaIdx;
            $resultado->paginacao->links->{$paginaIdx}->link = self::formataLink($filtros, $paginaIdx);
            $resultado->paginacao->links->{$paginaIdx}->selecionado = $paginaIdx == $pagina;
            $paginaIdx++;
        }

        return $resultado;
    }

    public static function simples($dados, $total, $ipp, $pagina, $filtros)
    {
        $paginacao = (object) [];

        $paginacao->dados = $dados;

        $paginacao->ipp = $ipp;
        $paginacao->pagina = $pagina;
        $paginacao->total = $total;
        $paginacao->links = (object) [];

        $paginacao->links->primeira = (object) [];
        $paginacao->links->primeira->label = 0;
        $paginacao->links->primeira->pagina = 0;
        $paginacao->links->primeira->link = self::formataLink($filtros, 0);
        $paginacao->links->primeira->selecionado = false;
        // $paginacao->links->primeira->visivel = $total > 0;

        $paginacao->links->anterior = (object) [];
        $paginacao->links->anterior->label = $pagina - 1;
        $paginacao->links->anterior->pagina = $pagina - 1;
        $paginacao->links->anterior->link = self::formataLink($filtros, $pagina - 1);
        $paginacao->links->anterior->selecionado = false;
        // $paginacao->links->anterior->visivel = ($pagina - 1) >= 0;

        $paginacao->links->atual = (object) [];
        $paginacao->links->atual->label = $pagina;
        $paginacao->links->atual->pagina = $pagina;
        $paginacao->links->atual->link = self::formataLink($filtros, $pagina);
        $paginacao->links->atual->selecionado = true;
        // $paginacao->links->atual->visivel = $total > 0;

        $paginacao->links->proxima = (object) [];
        $paginacao->links->proxima->label = $pagina + 1;
        $paginacao->links->proxima->pagina = $pagina + 1;
        $paginacao->links->proxima->link = self::formataLink($filtros, $pagina + 1);
        $paginacao->links->proxima->selecionado = false;
        // $paginacao->links->proxima->visivel = false;

        $paginacao->links->ultima = (object) [];
        $paginacao->links->ultima->label = ceil($total / $ipp);
        $paginacao->links->ultima->pagina = ceil($total / $ipp);
        $paginacao->links->ultima->link = self::formataLink($filtros, ceil($total / $ipp));
        $paginacao->links->ultima->selecionado = false;
        // $paginacao->links->ultima->visivel = false;



        return $paginacao;
    }


    public static function offset($limit, $offset = 0) {
        $offset = (int)$offset;

        if($offset <= 0) {
            $offset = 1;
        }
        return ($offset - 1) * (int)$limit;
    }

    /**
     * Retorna apenas os links da paginação
     * @param int $total
     * @param int $ipp
     * @param int $pagina
     * @param array $filtros
     * @return stdClass
     */
    public static function addLinks($total, $ipp, $pagina, $filtros): stdClass
    {
        $links = new stdClass;

        for ($paginaIdx = 1; $paginaIdx <= ceil($total / $ipp); $paginaIdx++) {
            $links->{$paginaIdx} = new stdClass;;
            $links->{$paginaIdx}->label = $paginaIdx;
            $links->{$paginaIdx}->pagina = $paginaIdx;
            $links->{$paginaIdx}->link = self::formataLink($filtros, $paginaIdx);
            $links->{$paginaIdx}->selecionado = $paginaIdx == $pagina;
        }

        return $links;
    }
}
