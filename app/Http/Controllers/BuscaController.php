<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Libraries\ArrayUtils;

ini_set("memory_limit", "512M");

class BuscaController extends Controller
{

    public function index()
    {
        return view('welcome');
    }


    public function produtosAjax(Request $request)
    {
        $termosBuscado = trim(strtolower($request->get('q')));
        /* TRANSFORMA EM ARRAY CADA PALAVRA DIGITADA */
        $arrTermoBuscado = preg_split('|[^a-záéíóúâêîôûãõÁÉÍÓÚÂÊÎÔÛÃÕA-Z0-9]+|', $termosBuscado, -1, PREG_SPLIT_NO_EMPTY);

        $produtos = json_decode(file_get_contents('data.json'));
        // dd($produtos[1]);
        // $key = sprintf(env("CACHE_KEY_BUSCA"));
        // $produtos = Cache::get($key);


        if(empty($produtos)){
            return response()->json(["error" => true, "messagem" =>  "É necessario montar o cache novamente..."], 402);
        }

        $produtos = collect($produtos)->filter(function ($product) use ($arrTermoBuscado) {
                $qtdTermo = count($arrTermoBuscado);
                $score = 0;

                foreach ($arrTermoBuscado as $termo) {
                    if (stripos($product->termos, $termo) !== false) {
                        $score++;
                    }
                }
                return $score == $qtdTermo;
        })->take(5)->values()->all();

        return response()->json($produtos);
    }

    public function produtosAjax1(Request $request)
    {
        $termosBuscado = trim(strtolower($request->get('q')));
        /* TRANSFORMA EM ARRAY CADA PALAVRA DIGITADA */
        $arrTermoBuscado = preg_split('|[^a-záéíóúâêîôûãõÁÉÍÓÚÂÊÎÔÛÃÕA-Z0-9]+|', $termosBuscado, -1, PREG_SPLIT_NO_EMPTY);

        $key = sprintf(env("CACHE_KEY_BUSCA"));
        $produtos = Cache::get($key);


        if(empty($produtos)){
            return response()->json(["error" => true, "messagem" =>  "É necessario montar o cache novamente..."], 402);
        }

        $produtos = collect($produtos)->filter(function ($product) use ($arrTermoBuscado) {
                $qtdTermo = count($arrTermoBuscado);
                $score = 0;
                foreach ($arrTermoBuscado as $termo) {
                    if (stripos($product['termos'], $termo) !== false) {
                        $score++;
                    }
                }
                return $score == $qtdTermo;
        })->take(5)->values()->all();

        return response()->json($produtos);
    }
}
