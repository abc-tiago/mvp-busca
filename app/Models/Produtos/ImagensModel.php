<?php

namespace App\Models\Produtos;

use App\Libraries\DatabaseUtils;
use Illuminate\Support\Facades\DB;
use App\Libraries\ArrayUtils;

class ImagensModel
{
    private static $conn = 'produtos';


    public static function porReferenciasImagemPrincipal($referencias)
    {
        $ret = [];
        if (!empty($referencias)) {
            $fill = array_fill(0, count($referencias), '?');
            $fill = implode(',', $fill);

            $sql = "SELECT
                        referencia,
                        sha256
                    FROM
                        imagens
                    WHERE
                        referencia IN ($fill)
                        AND position = 1
                        AND status = 'active'
                        AND sha256 IS NOT NULL AND sha256 <> '' and status<>'error'
                    ORDER BY
                        position ASC";

            $rsp = DatabaseUtils::select(self::$conn, $sql, $referencias);
            $rsp = array_map(function ($img) {
                return (object) [
                    'referencia' => $img->referencia,
                    'url' => env('API_PRODUTOS_CDN') . 'imagens_processadas/' . $img->referencia . '/' . $img->sha256 . '-500x500.jpg',
                ];
            }, $rsp);
            $ret = ArrayUtils::collapse($rsp, 'referencia');
        }
        return $ret;
    }

}
