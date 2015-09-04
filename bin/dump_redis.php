#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: masterx2
 * Date: 04.09.15
 * Time: 17:12
 */
require_once __DIR__.'/../vendor/autoload.php';
define('BASE_DIR', __DIR__.'/../');

use App\Db\RedisDriver;
use App\Db\RedisCache;
use App\Db\Mongo;

class Dumper {

    /** @var \Noodlehaus\Config */
    public $config;

    /** @var  \Monolog\Logger */
    public $log;

    /**
     * Dumper constructor.
     */
    public function __construct() {
        $this->config = new \Noodlehaus\Config([BASE_DIR.'config/base.json','?'.BASE_DIR."config/local.json"]);
        RedisDriver::connect($this->config['redis']);
        Mongo::connect($this->config['mongo']);
    }

    public function dropToDb() {
        $now = time();
        $pool = RedisDriver::$redis->keys(RedisCache::$last_access_key.'*');
        foreach ($pool as $key) {
            $parts = explode(':', $key);
            $container = Mongo::$db->selectCollection($parts[1]);
            $last_access = (int) RedisDriver::$redis->get($key);
            if ($now - $last_access > 60*10) {
//                RedisCache::cache()
//                $container->insert()
            }
        }
    }

    public static function test() {
        $link = new \App\Models\Link();

        $link->add([
            'original_url' => 'http://google.com',
            'short_url' => 'aa',
            'sid' => 'masterx2'
        ]);

        $f = $link->getAll([
            'sid' => 'masterx2'
        ]);

        $s = $link->getAll([
            '_id' => $f[0]['_id']
        ]);

        var_dump([$f,$s]);

        $link->del([
            'sid' => 'masterx2'
        ]);
    }
}

$dumper = new Dumper();

$dumper->test();