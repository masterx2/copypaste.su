<?php
namespace App\Db;

trait RedisCache {
    public static $namespace = 'cache:';
    public static $last_access_key = 'cache-access:';

    public static function cache($key, $ttl = null, $value = null) {
        if(empty($key)) return false;
        $last_access_key = self::$last_access_key.$key;
        $key = self::$namespace.$key;

        if($value === null  && $ttl === null) {
            $return_value = RedisDriver::$redis->get($key);
            if (!empty($return_value)) {
                RedisDriver::$redis->set($last_access_key, time());
                return unserialize($return_value);
            }
        } elseif($value !== null  && intval($ttl) > 0) {
            RedisDriver::$redis->del($last_access_key);
            return RedisDriver::$redis->setex($key, $ttl, serialize($value));
        } elseif($value === null  && intval($ttl) > 0) {
            return RedisDriver::$redis->expire($key, $ttl);
        } else {
            return false;
        }
    }

    public static function getKeys($key) {
        $key = self::$namespace.$key;
        return array_map(function($item) {
            return substr($item, strlen(self::$namespace));
        },RedisDriver::$redis->keys($key));
    }

    public static function del($key) {
        if(empty($key)) return false;
        $key = self::$namespace.$key;
        $last_access_key = self::$last_access_key.$key;

        RedisDriver::$redis->del($last_access_key);
        RedisDriver::$redis->delete('cache:' . $key);
    }
}