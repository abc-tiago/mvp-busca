<?php
namespace App\Libraries;

use Illuminate\Support\Facades\Mail;

class MailLibrary
{
    public static function gerar_senha($email, $link)
    {
        $data = [
            'email' => $email,
            'link' => $link,
            'subject' => 'API de Fulfillment - Redefinição de Senha',
        ];

        Mail::send('mails/gerar_senha', $data, function ($message) use ($data) {
            $message->to($data['email'], $data['email'])->subject($data['subject']);
            $message->from('no-reply.pedidos@devabc.com.br', 'API Fullfilment');
        });
    }
    public static function enviar_token_api($sistema, $mantenedores, $token)
    {
        $data = [
            'sistema' => $sistema,
            'token' => $token,
            'subject' => 'API de Fulfilment - Alteração do token de acesso',
        ];

        foreach ($mantenedores as $mantenedor) {
            $data['to'] = $mantenedor;
            // dump($data);
            Mail::send('mails/token_api', $data, function ($message) use ($data) {
                $message->to($data['to'], $data['to'])->subject($data['subject']);
                $message->from('no-reply.pedidos@devabc.com.br', 'API Fulfillment');
            });
        }
    }
}

// $data = [
//     'email' => $email,
//     'link' => $link,
//     'subject' => 'Recuperação de Senha',
// ];
// Mail::send('mails/gerar_senha', $data, function ($message) use ($data) {
//     $message->to($data['email'], $data['email'])->subject($data['subject']);
//     $message->from('no-reply.fulfillment@abcdaconstrucao.com.br', 'API Fulfilment');
// });
