<?php
namespace App\Models;

use App\Db\Mongo;
use App\Db\RedisCache;

/**
 * Class ModelAbstract
 * @package App\Models
 */
abstract class ModelAbstract {

    public $schema = [];
    public $keys = ['_id'];

    public $db;
    public $counters;
    public $container;
    public $name;

    public function __construct() {
        $this->name = self::getBaseClassName();
        $this->container = Mongo::$db->selectCollection($this->name);
        $this->counters = Mongo::$db->selectCollection('counters');
    }

    public static function getBaseClassName() {
        $class = explode('\\', get_called_class());
        return strtolower(array_pop($class));
    }

    /**
     * @param $object
     * @return array
     */
    public function checkSchema($object) {
        $new_object = [];
        foreach ($this->schema as $key => $value) {
            if (isset($object[$key])) {
                $obj_type = gettype($object[$key]);
                if ($value['value_type'] != $obj_type) {
                    switch ($value['value_type']) {
                        case 'integer':
                            $new_object[$key] = intval($object[$key]);
                            break;
                        case 'double':
                            $new_object[$key] = floatval($object[$key]);
                            break;
                        case 'array':
                            $new_object[$key] = explode(', ', $object[$key]);
                            break;
                        case 'coords':
                            $items = explode(',', $object[$key]);
                            $new_object[$key] = [
                                floatval($items[0]),
                                floatval($items[1]),
                            ];
                            break;
                        case 'string':
                            $new_object[$key] = implode(', ', $object[$key]);
                            break;
                        case 'date':
                            if ($object[$key] instanceof \MongoDate) {
                                $new_object[$key] = $object[$key];
                            } else {
                                $new_object[$key] = new \MongoDate($object[$key]);
                            }
                            break;
                    }
                } else {
                    $new_object[$key] = $object[$key];
                }
            } else {
                $new_object[$key] = $this->schema[$key]['default'];
                if ($value['value_type'] == 'date' && $this->schema[$key]['default'] == 'now') {
                    $new_object[$key] = new \MongoDate();
                }
            }
        }
        // Pass id and MongoId if exist
        isset($object['_id']) && $new_object['_id'] = $object['_id'];
        return $new_object;
    }

    public function getKey($object) {
        $values = [];
        foreach ($this->keys as $key) {
            $values[] = static::clearMongo($object[$key]);
        }
        return $this->name.':'.implode(':', $values);
    }

    public function queryToKey($query) {
        $result = [$this->name];
        foreach ($this->keys as $key) {
            $query_keys = array_keys($query);
            $index = array_search($key, $query_keys);
            if ($index > -1) {
                $result[] = $query[$query_keys[$index]];
            } else {
                $result[] = '*';
            }
        }
        return !empty($result) ? implode(':', $result) : null;
    }

    public function add($object) {
        $object = $this->checkSchema($object);
        $object['_id'] = new \MongoId();
        $key = $this->getKey($object);
        return RedisCache::cache($key, 100, $object);
    }

    public function getAll($query) {
        $result = [];
        $keys = RedisCache::getKeys($this->queryToKey($query));
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $result[] = RedisCache::cache($key);
            }
        } else {
            $result = static::clearMongo(iterator_to_array($this->container->find($query)));
        }
        return $result;
    }

    public function getOne($query) {
        $keys = RedisCache::getKeys($this->queryToKey($query));
        if (!empty($keys)) {
            $result = RedisCache::cache($keys[0]);
        } else {
            $result = static::clearMongo($this->container->findOne($query));
        }
        return $result;
    }

    public function getById($_id) {
        return $this->getOne([
            '_id' => $_id
        ]);
    }

    public function del($query) {
        $keys = RedisCache::getKeys($this->queryToKey($query));
        if (!empty($keys)) {
            foreach ($keys as $key) {
                RedisCache::del($key);
            }
        }
    }

    public function collect($resource) {
        return $this->checkSchema($resource);
    }

    public static function clearMongo($data) {
        if(is_array($data)) {
            foreach($data as &$attr) {
                if($attr instanceof \MongoId) {
                    $attr = (string) $attr;
                }
                if($attr instanceof \MongoDate) {
                    $attr = $attr->sec;
                }
                if(is_array($attr)) {
                    $attr = self::clearMongo($attr);
                }
            }
            unset($attr);
        } else {
            return  $data instanceof \MongoDate ? $data->{'sec'} : (string) $data;
        }
        return $data;
    }

    public static function checkId($id) {
        if (!($id instanceof \MongoId)) {
            $id = new \MongoId($id);
        }
        return $id;
    }

    public function getFields() {
        return array_keys($this->schema);
    }
}