<?php

namespace App\Libraries;
use Illuminate\Support\Facades\Log;

class ApiPagSeguro
{
    public static function validacaoCredencias()
    {
        $clientID = env('API_PAGSEGURO_CLIENTEID');
        $token = env('API_PAGSEGURO_TOKEN');

        $rsp = self::get("users/{$clientID}/token/{$token}");
        return $rsp;
    }

    public static function getMovimentacoes($dataMovimento, $pageNumber)
    {
        $version = env('API_PAGSEGURO_VENSION');

        $rsp = self::get("{$version}/movimentos", [
            'dataMovimento' => $dataMovimento,
            'pageNumber' => $pageNumber,
            'pageSize' => 1000,
            'tipoMovimento' => 1
        ], true);

        if ($rsp->http_code != 200) {
            return false;
        }

        return $rsp;
    }

    public static function movimentacoes($dataMovimento, $pageNumber = 1)
    {
        $validarCredencias = self::validacaoCredencias();
        if(empty($validarCredencias)){
            Log::error("Ocorreu um erro na validação das credencias na API PAGSEGURO do dia {$dataMovimento}");
            return false;
        }

        $rsp = self::getMovimentacoes($dataMovimento, $pageNumber);
        if ($rsp->http_code != 200) {
            return false;
        }

        $totalPages = $rsp->body->pagination->totalPages;
        $page = $rsp->body->pagination->page; // pagina atual
        $totalElements = $rsp->body->pagination->totalElements; // total registros
        $totalPages = $rsp->body->pagination->totalPages; // total paginas

        $arr = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $rsp = self::getMovimentacoes($dataMovimento, $i);
            // echo count($rsp->body->detalhes) . ' ---- ';
            $arr = array_merge($arr, $rsp->body->detalhes);
        }

        return (object)["elements" => $arr, "totalElements" => $totalElements];
    }



    private static function get($url, $params = [], $auth = false)
    {

        $Authorization = "";
        $url = env('API_PAGSEGURO_ENDPOINT') . $url;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        // dump("get: $url");

        $clientID = env('API_PAGSEGURO_CLIENTEID');
        $token = env('API_PAGSEGURO_TOKEN');

        if ($auth && $clientID && $token) {
            $Authorization = 'Authorization: Basic ' . base64_encode("$clientID:$token");
        }

        // dump("get Auth: $Authorization");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 120,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '', //json_encode($data),

            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                // "Content-Type: application/json",
                $Authorization
            ),
        ));
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $http_code = $info['http_code'];
        curl_close($curl);

        switch ($http_code) {
            case (200):
                $body = json_decode($body);
                break;
            default:
                Log::error("Retorno não mapeado ($http_code): " . $body);
                break;
        }

        return (object) [
            'url' => $url,
            'http_code' => $http_code,
            'body' => $body,
        ];
    }
}
