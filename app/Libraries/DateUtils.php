<?php

namespace App\Libraries;

use DateTime;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DateUtils
{
    public static function calcularIntervalo($dataInicial, $dataFinal)
    {

        $dataInicial         = new DateTime($dataInicial);
        $dataFinal         = new DateTime($dataFinal);

        $dataIntervalo = array();
        $anos = [];
        $meses = [];
        $diasSemana = [];

        while ($dataInicial <= $dataFinal) {
            $month = date('F', strtotime($dataInicial->format('Y-m-d')));
            $month = self::convertMesPTBR($month);
            $number = date('m', strtotime($dataInicial->format('Y-m-d')));
            $ano = date('Y', strtotime($dataInicial->format('Y-m-d')));

            if (!in_array($month, $meses)) {
                $meses[$number] = $month;
            }
            if (!in_array($ano, $anos)) {
                $anos[] = $ano;
            }

            $mesIntervalo[] = $dataInicial->format('m');
            $dataIntervalo[] = $dataInicial->format('Y-m-d');
            $diasFormatadado[] = $dataInicial->format('d');
            $diasSemana[] = self::convertDiadaSemana(date('w', strtotime($dataInicial->format('Y-m-d'))));
            $dataInicial = $dataInicial->modify('+1day');
        }

        $result = [
            "meses" => $meses,
            "anos" => $anos,
            "mesIntervalo" => array_count_values($mesIntervalo),
            "diasCompleto" => $dataIntervalo,
            "diasFormatadado" => $diasFormatadado,
            "diasSemana" => $diasSemana
        ];

        return $result;
    }

    public static function convertMesPTBR($mes)
    {

        $mes_extenso = array(
            'January' => 'Janeiro',
            'February' => 'Fevereiro',
            'March' => 'Marco',
            'April' => 'Abril',
            'May' => 'Maio',
            'June' => 'Junho',
            'July' => 'Julho',
            'August' => 'Agosto',
            'November' => 'Novembro',
            'September' => 'Setembro',
            'October' => 'Outubro',
            'December' => 'Dezembro'
        );

        $ret = $mes_extenso[$mes];

        return $ret;
    }

    public static function convertDiadaSemana($dia)
    {
        $diaSemana = array('D', 'S', 'T', 'Q', 'Q', 'S', 'S');
        return $diaSemana[$dia];
    }

    public static function dataHoje($formato)
    {
        return Carbon::now()->timezone('America/Sao_Paulo')->format($formato);
    }

    public static function diffInterval(Carbon $inicio)
    {
        return $inicio->locale('pt_BR')->diffForHumans(
            Carbon::now(),
            CarbonInterface::DIFF_ABSOLUTE,
            false,
            4
        );
    }
}
