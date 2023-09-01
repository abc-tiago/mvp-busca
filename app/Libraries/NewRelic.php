<?php

namespace App\Libraries;

class NewRelic
{
    /**
     * @param string $string
     *
     * @return void
     */
    public static function name_transaction(string $string)
    {
        if (extension_loaded('newrelic')) {
            newrelic_name_transaction($string);
        }
    }

    /**
     * @param array|string $error
     * @param bool         $fatal
     *
     * @return void
     * @throws \Exception
     */
    public static function notice_error(array|string $error, bool $fatal = true)
    {
        if (is_array($error)) {
            $error = json_encode($error);
        }

        if (extension_loaded('newrelic')) {
            newrelic_notice_error(new \Exception($error));

            if ($fatal) {
                throw new \Exception($error);
            }
        } else {
            if ($fatal) {
                throw new \Exception("NEWRELIC: $error");
            }
        }
    }
}
