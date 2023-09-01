<?php

namespace App\Libraries;
use App\Libraries\MemoryGetUsage;
use PhpParser\Node\Expr\Cast\Object_;

ini_set("memory_limit", "512M");

class ApiProdutos
{


    public static function dimensoes($referencias)
    {
        $rsp = self::get('/api/v1/feeds/fulfillment/dimensoes', [
            'referencias' => $referencias,
        ]);
        return $rsp;
    }

    public static function getProdutos(int $pagina)
    {
        $rsp = self::get("/api/v1/produtos/all", [
            'pagina' => $pagina,
            'ipp' => 1000,
        ]);
        return $rsp;
    }

    public static function transformeProdutos($pageNumber = 1)
    {
        // Medir a memória antes de realizar operações
        $memoryBefore = MemoryGetUsage::memory();

        $rsp = self::getProdutos($pageNumber);

        $totalPages = json_decode(json_encode($rsp->body->links), true);
        $totalPages = array_key_last($totalPages);
        // $totalPages = 1;
        $totalElements = $rsp->body->total; // total registros


        $arr = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $rsp = self::getProdutos($i);
            // $dados = json_decode(json_encode($rsp->body->dados));
            $arr = array_merge($arr, json_decode(json_encode($rsp->body->dados), true));
        }

        // Medir a memória após as operações
        $memoryAfter = MemoryGetUsage::memory();
        // Calcular a quantidade de memória usada
        $memoryUsed = MemoryGetUsage::calculeMemory($memoryAfter, $memoryBefore);
        // Exibir a quantidade de memória usada
        dump("Memória usada: " . MemoryGetUsage::formatBytes($memoryUsed));

        return (object)["elements" => $arr, "totalElements" => $totalElements];
    }




    private static function get($url, $params = [])
    {
        $url = env('API_PRODUTOS_ENDPOINT') . $url;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        dump("get: $url");

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
                "apikey: ".env('API_PRODUTOS_TOKEN')
                // "Content-Type: application/json",
                // "gumgaToken: " . env('ANYMARKET_TOKEN'),
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
                NewRelic::notice_error("Retorno não mapeado ($http_code): " . $body);
                break;
        }

        return (object) [
            'url' => $url,
            'http_code' => $http_code,
            'body' => $body,
        ];
    }
}
