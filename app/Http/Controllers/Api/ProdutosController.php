<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;


use App\Services\ProdutosService;
use App\Libraries\ApiProdutos;
use App\Libraries\MemoryGetUsage;

ini_set("memory_limit", "512M");

class ProdutosController
{


    public function consultar(Request $request)
    {

        $pagina = $request->get('pagina') ?? 0;
        $ipp = $request->get('ipp') ?? 50;

        $produtos = ProdutosService::buscarProdutoComPaginacao($pagina, $ipp);

        // $key = sprintf("produtos_para_busca_%s", $filial);
        // $produtos = Cache::get($key);
        // if(empty($produtos)){
        //     $produtos = ProdutosService::buscarProdutoComPaginacao($filial, $pagina, $ipp);
        //     Cache::put($key, $produtos, 24 * 3600);
        // }

        return response()->json($produtos, Response::HTTP_OK);
    }

    public static function rotinaBuscaProduto() {

        // Medir a memória antes de realizar operações
        $memoryBefore = MemoryGetUsage::memory();

        $key = sprintf(env("CACHE_KEY_BUSCA"));

        $produtos = Cache::get($key);

        if (empty($produtos)) {
            $produtos = ApiProdutos::transformeProdutos(1);
            $produtos = $produtos->elements;
            Cache::put($key, $produtos, 24 * 3600);
        }

        // Medir a memória após as operações
        $memoryAfter = MemoryGetUsage::memory();

        // Calcular a quantidade de memória usada
        $memoryUsed = MemoryGetUsage::calculeMemory($memoryAfter, $memoryBefore);

        // Exibir a quantidade de memória usada
        dump("Memória usada: " . MemoryGetUsage::formatBytes($memoryUsed));

        dd(count($produtos));
        dd('fim');

        return true;
    }

    public static function rotinaBuscaProdutoSalvarArquivo() {

        // Medir a memória antes de realizar operações
        $memoryBefore = MemoryGetUsage::memory();


        $produtos = ApiProdutos::transformeProdutos(1);
        $produtos = $produtos->elements;

        $jsonData = json_encode($produtos, JSON_PRETTY_PRINT);

        // Caminho para o arquivo onde você deseja salvar o JSON
        // $filePath = storage_path('app/data.json');

        File::put('data.json', $jsonData);

        // Medir a memória após as operações
        $memoryAfter = MemoryGetUsage::memory();

        // Calcular a quantidade de memória usada
        $memoryUsed = MemoryGetUsage::calculeMemory($memoryAfter, $memoryBefore);

        // Exibir a quantidade de memória usada
        dump("Memória usada: " . MemoryGetUsage::formatBytes($memoryUsed));

        dd(count($produtos));
        dd('fim');

        return true;
    }

}
