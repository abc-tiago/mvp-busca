<?php

namespace App\Libraries;

class MemoryGetUsage
{

    // Medir a memória antes de realizar operações
    public static function memory() {
        return memory_get_usage();
    }

    // Calcular a quantidade de memória usada
    public static function calculeMemory($memoryAfter, $memoryBefore) {
        $memoryUsed = $memoryAfter - $memoryBefore;
        return $memoryUsed;
    }

    // Função para formatar bytes de forma mais legível
    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }


}
