<?php

namespace App\Libraries;

class NumberUtils
{

    static $arrPct = [];

    public static function money($value)
    {
        $new_value = number_format($value, 5, '.', '');
        return $new_value;
    }

    public static function excelToDb($value, $decimal = 2): float
    {
        $numericValue = preg_replace("/[^0-9,.]+/i", "", $value);

        if (is_float($numericValue) || is_numeric($numericValue)) {
            return floatval(str_replace(',', '.', $numericValue));
        } elseif (preg_match('~\.\d+$~', $numericValue)) {
            return floatval(str_replace(',', '', $numericValue));
        } else {
            return floatval(number_format(str_replace(",", ".", str_replace(".", "", $numericValue)), $decimal, '.', ''));
        }
    }

    public static function bdDinheiro($value)
    {
        $new_value = ($value * 100);
        return $new_value;
    }

    public static function corvDinheiro($value)
    {
        $new_value = ($value / 100);
        return $new_value;
    }

    public static function dinheiro($value)
    {
        $new_value = ($value / 100);
        // $new_value = floor($value);
        // $new_value = bcdiv($value, 100, 2);
        // dump('dinheiro', $value, $new_value);
        return $new_value;
    }

    public static function centavos($value)
    {
        $new_value = floor(bcmul($value, 100));
        return $new_value;
    }

    public static function metro($value)
    {
        $new_value = bcdiv($value, 100, 2);
        // dump('metro', $value, $new_value);
        return $new_value;
    }

    public static function centimetros($value)
    {
        $new_value = floor(bcmul($value, 100));
        return $new_value;
    }

    public static function peso($value)
    {
        $new_value = bcdiv($value, 1000, 3);
        // dump('peso', $value, $new_value);
        return $new_value;
    }

    public static function gramas($value)
    {
        $new_value = floor(bcmul($value, 1000));
        return $new_value;
    }

    public static function qtdDecimais($value)
    {
        return strlen(substr(strrchr($value, "."), 1));
    }

    public static function arredondar($value)
    {
        return intval($value * 100) / 100;
    }

    public static function roundPriceMu($qtd = 0, $value = 0)
    {
        return is_int(self::arredondar($qtd)) ? self::arredondar($qtd) * $value : self::arredondar($qtd * $value);
    }

    public static function barraProgresso($total, $item)
    {
        $percent = ceil(($item + 1) / $total * 100);

        if (!array_key_exists($percent, self::$arrPct)) {
            self::$arrPct[$percent] = 1;
            dump("QTD processada: {$percent}%");
        }

        return $percent;
    }

    public static function validatePedidos($pedidos)
    {
        return preg_match('~^[0-9,]+$~', $pedidos);
    }

    public static function stringToNumber($numero)
    {
        return preg_replace('/[^0-9]/', '', $numero);
    }

    public static function validarPedidoCatalogo($numero)
    {
        $numero = preg_replace('|[^\d]|', '', $numero);

        if (strlen($numero) != 6) {
            return false;
        }
        return $numero;
    }

    public static function gerarCodigoPedido()
    {
        return str_pad(rand(1, 999999), 4, 0, STR_PAD_LEFT);
    }
}
