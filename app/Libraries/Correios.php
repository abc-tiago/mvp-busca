<?php

namespace App\Libraries;

class Correios
{
    public static function consultaCep($cep)
    {
        // {
        // "erro": false,
        // "mensagem": "DADOS ENCONTRADOS COM SUCESSO.",
        // "total": 1,
        // "dados": [
        // {
        // "uf": "PR",
        // "localidade": "Curitiba",
        // "locNoSem": "",
        // "locNu": "",
        // "localidadeSubordinada": "",
        // "logradouroDNEC": "Rua Nunes Machado - até 397/398",
        // "logradouroTextoAdicional": "",
        // "logradouroTexto": "",
        // "bairro": "Centro",
        // "baiNu": "",
        // "nomeUnidade": "",
        // "cep": "80250000",
        // "tipoCep": "2",
        // "numeroLocalidade": "",
        // "situacao": "",
        // "faixasCaixaPostal": [],
        // "faixasCep": []
        // }
        // ]
        // }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://buscacepinter.correios.com.br/app/endereco/carrega-cep-endereco.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: \"Chromium\";v=\"92\", \" Not A;Brand\";v=\"99\", \"Google Chrome\";v=\"92\"';
        $headers[] = 'Cache-Control: no-store, no-cache, must-revalidate';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Accept: */*';
        $headers[] = 'Origin: https://buscacepinter.correios.com.br';
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: https://buscacepinter.correios.com.br/app/endereco/index.php';
        $headers[] = 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7';
        // $headers[] = 'Cookie: buscacep=lblc7b8lls4csvpmilut43g5tr; svp-47873-%3FEXTERNO_2%3Fpool_svp_ext_443=BDABKIMALLAB';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $vars = [
            'pagina' => '/app/endereco/index.php',
            'cepaux' => '',
            'mensagem_alerta' => '',
            'endereco' => $cep,
            'tipoCEP' => 'ALL',
        ];
        // dump($vars);
        $postvars = http_build_query($vars); // . "\n";

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($result);
    }
}
