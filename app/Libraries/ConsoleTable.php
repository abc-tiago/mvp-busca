<?php
namespace App\Libraries;

class ConsoleTable
{
    private $headers = [];
    private $rows = [];

    public function headers($data)
    {
        foreach ($data[array_key_first($data)] as $header => $row) {
            $this->headers[$header] = mb_strlen($header, 'UTF8');
        }
        return $this;
    }

    public function addData($data)
    {
        foreach ($data as $row) {
            foreach ($this->headers as $k => $l) {
                $len = mb_strlen($row->{$k}, 'UTF8');
                if ($l < $len) {
                    $this->headers[$k] = $len;
                }
            }
            $this->rows[] = $row;
        }
        return $this;
    }

    private function hr($pad = '-')
    {
        foreach ($this->headers as $k => $v) {
            echo $pad . str_pad($pad, $v, $pad, STR_PAD_BOTH);
        }
        echo '|' . PHP_EOL;
    }

    private function printHeaders()
    {
        foreach ($this->headers as $k => $v) {
            echo '|' . str_pad($k, $v, " ", STR_PAD_BOTH);
        }
        echo '|' . PHP_EOL;

    }

    public function getTable($title = '')
    {
        $this->hr();

        if (!empty($title)) {
            $total = array_sum($this->headers) + count($this->headers) - 1;
            echo '|' . str_pad($title, $total, " ", STR_PAD_BOTH) . '|' . PHP_EOL;
            $this->hr();
        }
        // foreach ($this->headers as $k => $v) {
        //     echo '|' . str_pad($k, $v, " ", STR_PAD_BOTH);
        // }
        // echo '|' . PHP_EOL;
        $this->printHeaders();
        $this->hr();

        foreach ($this->rows as $rk => $row) {
            // echo $rk . '===' . PHP_EOL;
            foreach ($this->headers as $k => $v) {
                echo '|' . str_pad($row->{$k}, $v, " ", STR_PAD_BOTH);
            }
            echo '|' . PHP_EOL;

            if (!($rk % 50) && $rk > 0) {
                // $this->hr('+');
                $this->printHeaders();
            }
        }
        $this->hr();
        echo PHP_EOL;
    }

    public static function dd($data, $title = '')
    {
        if (!empty($data)) {
            echo (new ConsoleTable())
                ->headers($data)
                ->addData($data)
                ->getTable($title);
        } else {
            echo '';
        }
    }
}
