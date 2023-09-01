<?php

namespace App\Libraries;

class StringUtils
{
    public static function slugify($string)
    {
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        $string = strtr($string, $unwanted_array);
        $string = StringUtils::strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        $string = preg_replace('/[\-]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }

    public static function prepararAtributo($nome)
    {
        $nome = mb_convert_encoding($nome, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
        $nome = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $nome);
        $nome = trim($nome);
        // $nome = htmlentities($nome);
        // $nome = preg_replace('|[^a-zA-Zªº²³åáâãéêíóôõúçàÁÂÃÉÊÍÓÔÕÚÇÀüÜ≤≥/ =   \-\d\.,]|', '', $nome);
        // $nome = json_encode($nome);
        return $nome;
    }

    public static function substr($text, $start, $len)
    {
        return mb_substr($text, $start, $len, mb_detect_encoding($text));
    }

    public static function utf8($text)
    {
        return mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'ASCII, JIS, UTF-8, EUC-JP, SJIS'));
    }

    public static function strtolower($str)
    {
        return mb_strtolower($str, mb_detect_encoding($str, 'ASCII, JIS, UTF-8, EUC-JP, SJIS'));
    }

    public static function strtoupper($str)
    {
        return mb_strtoupper($str, mb_detect_encoding($str, 'ASCII, JIS, UTF-8, EUC-JP, SJIS'));
    }

    public static function ucwords($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return StringUtils::substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

}
