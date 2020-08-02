<?php

namespace TexLab\MyDB;

use Exception;
use mysqli;

/**
 * Class DB
 *
 * Create a new connection to the MySql server.
 *
 * @package TexLab\MyDB
 */
class DB implements DBInterface
{
    /**
     * @var mysqli[]
     */
    private static $instances = [];

    /**
     * @var callable|null
     */
    private static $errorHandler = null;

    /**
     * @var array<string, mixed>
     */
    private const DEFAULT_OPTIONS = [
        'host' => null,
        'username' => null,
        'password' => null,
        'dbname' => null,
        'port' => null,
        'socket' => null
    ];

    /**
     * @param array<string, mixed> $options
     * @return mysqli
     */
    private static function new(array $options): mysqli
    {
        $mysqli = @new mysqli(
            $options['host'],
            $options['username'],
            $options['password'],
            $options['dbname'],
            $options['port'],
            $options['socket']
        );

        if ($mysqli->connect_errno) {
            static::errorHandler($mysqli);
        }

        return $mysqli;
    }

    /**
     * @param mysqli $mysqli
     * @return void
     */
    protected static function errorHandler(mysqli $mysqli)
    {
        if (is_callable(static::$errorHandler)) {
            (static::$errorHandler)($mysqli);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param callable|null $errorHandler
     * @return mysqli
     */
    public static function link(array $options, callable $errorHandler = null): mysqli
    {
        if (is_callable($errorHandler)) {
            static::$errorHandler = $errorHandler;
        } else {
            /**
             * @param mysqli $mysqli
             */
            static::$errorHandler = function (mysqli $mysqli): void {
                throw new Exception("MySql connect error : $mysqli->connect_error", $mysqli->connect_errno);
            };
        }

        $key = serialize($options);

        if (empty(static::$instances[$key])) {
            static::$instances[$key] = static::new(
                array_merge(static::DEFAULT_OPTIONS, $options)
            );
        }

        return static::$instances[$key];

//        return static::$instances[$key] ?? static::$instances[$key] = static::new(
//                array_merge(static::DEFAULT_OPTIONS, $options)
//            );
    }
}
