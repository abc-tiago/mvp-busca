<?php

namespace App\Libraries;

class FreteRapido
{
    public static function getSkusProducts($page, $perPage)
    {
        return self::get('/skus/products', [
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
    // env('FRETERAPIDO_ENDPOINT');
    // env('FRETERAPIDO_TOKEN');

    private static function get($resource, $query_string = [])
    {

        // dump("get: $resource");
        $url = env('FRETERAPIDO_ENDPOINT') . $resource;
        $query_string['token'] = env('FRETERAPIDO_TOKEN');
        // if (!empty($query_string)) {
        $url .= '?' . http_build_query($query_string);
        // }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 120,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '', //json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: Application/JSON",
            ),
        ));

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $http_code = $info['http_code'];
        curl_close($curl);

        switch ($http_code) {
            case 200:
                $body = json_decode($body);
                break;
            default:
                throw new \Exception("Código não tratado: $http_code");
                break;
        }

        return (object) [
            'url' => $url,
            'http_code' => $http_code,
            'body' => $body,
        ];
    }

    // private static function put($resource, $data)
    // {
    //     dump("put: $resource");
    //     $curl = curl_init();
    //     $url = env('FRETERAPIDO_ENDPOINT') . $resource;
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 120,
    //         CURLOPT_TIMEOUT => 300,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'PUT',
    //         CURLOPT_POSTFIELDS => json_encode($data),
    //         CURLOPT_HTTPHEADER => array(
    //             "Content-Type: application/json",
    //             "Accept: Application/JSON",
    //         ),
    //     ));

    //     $body = curl_exec($curl);
    //     $info = curl_getinfo($curl);
    //     $http_code = $info['http_code'];
    //     curl_close($curl);

    //     return (object) [
    //         'url' => $url,
    //         'http_code' => $http_code,
    //         'body' => $body,
    //     ];
    // }

    // private static function post($resource, $data)
    // {
    //     dump("post: $resource");
    //     $url = env('FRETERAPIDO_ENDPOINT') . $resource;
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 120,
    //         CURLOPT_TIMEOUT => 300,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => json_encode($data),
    //         CURLOPT_HTTPHEADER => array(
    //             "Content-Type: application/json",
    //             "Accept: Application/JSON",
    //         ),
    //     ));

    //     $body = curl_exec($curl);
    //     $info = curl_getinfo($curl);
    //     $http_code = $info['http_code'];
    //     curl_close($curl);

    //     return (object) [
    //         'url' => $url,
    //         'http_code' => $http_code,
    //         'body' => $body,
    //     ];
    // }

    // private static function delete($resource)
    // {
    //     dump("delete: $resource");
    //     $url = env('FRETERAPIDO_ENDPOINT') . $resource;
    //     // dump($url);
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 120,
    //         CURLOPT_TIMEOUT => 300,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'DELETE',
    //         CURLOPT_POSTFIELDS => '', //json_encode($data),
    //         CURLOPT_HTTPHEADER => array(
    //             "Content-Type: application/json",
    //             "Accept: Application/JSON",
    //         ),
    //     ));

    //     $body = curl_exec($curl);
    //     $info = curl_getinfo($curl);
    //     $http_code = $info['http_code'];
    //     curl_close($curl);

    //     return (object) [
    //         'url' => $url,
    //         'http_code' => $http_code,
    //         'body' => $body,
    //     ];
    // }
}
