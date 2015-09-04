<?php

namespace App\Db;

/**
 * Статический класс коннекта к MongoDB
 * Class Mongo
 * @package App\Db
 */
class Mongo {
    /**
     * @var \MongoDB
     */
    public static $db;
    /**
     * Коннек к базе
     * @param string $dbname
     */
    public static function connect($config) {
        $mc = new \MongoClient("mongodb://".$config['host'].":".$config['port']);
        self::$db = $mc->$config['database'];
    }
}