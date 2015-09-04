<?php
/**
 * Created by PhpStorm.
 * User: masterx2
 * Date: 04.09.15
 * Time: 14:57
 */

namespace App\Db;

class RedisDriver {
    /**
     * @var \Redis
     */
    public static $redis;

    public static function connect($config) {
        self::$redis = new \Redis();
        if ($config['autoconnect'] != false) {
            self::$redis->connect($config['host'], $config['port'], $config['timeout']);
        }
    }
}