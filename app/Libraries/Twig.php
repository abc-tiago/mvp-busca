<?php

namespace App\Libraries;

/**
 * Undocumented class
 */
class Twig
{
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private static $loader = null;
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private static $twig = null;

    /**
     * Undocumented function
     *
     * @return void
     */
    private static function init()
    {
        if (self::$twig != null) {
            return;
        }

        self::$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../resources/views');

        self::$twig = new \Twig\Environment(
            self::$loader,
            [
                // 'cache' => '/path/to/compilation_cache',
                'debug' => app()->environment('local', 'staging'),
                'cache' => false,
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $viewPath asd
     * @param array  $params   asd
     *
     * @return void
     */
    public static function render($viewPath, $params = [])
    {
        self::init();
        $params['sys_funcao_usuario'] = $_SERVER['sys_funcao_usuario'] ?? 'USR';
        return self::$twig->render($viewPath, $params);
    }
}
