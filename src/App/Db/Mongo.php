<?php

namespace App\Db;

class Mongo {

	public static $connection;
	public static $dbname;
	public static $db;

	public static function init() {
		!self::$connection && self::$connection = new \MongoClient();
	}

	public static function setDb($dbname) {
		self::$dbname = $dbname;
	}

	public static function selectDb($dbname) {
		!self::$connection && self::init();
		self::$db = self::$connection->selectDb(self::$dbname);
		return true;
	}

	public static function checkAndConnect() {
		!(isset(self::$db) && self::$db instanceof \MongoDB) && self::selectDb(self::$dbname);		
	}

	public static function insert($collection, $document) {
		self::checkAndConnect();
		return self::$db->selectCollection($collection)->insert($document);
	}

	public static function find($collection, $query, $sort=[], $limit=10) {
		self::checkAndConnect();
		return iterator_to_array(self::$db->selectCollection($collection)
			->find($query)
			->sort($sort)
			->limit($limit)
		);
	}

	public static function findOne($collection, $query) {
		self::checkAndConnect();
		return self::$db->selectCollection($collection)->findOne($query);
	}

	public static function findAndModify($collection, $query, $update, $fields=null, $options=null) {
		self::checkAndConnect();
		return self::$db->selectCollection($collection)->findAndModify($query, $update, $fields, $options);
	}

	private static function getNextSequence($name){
        $retval = self::findAndModify('counters',
            ['_id' => $name],
            ['$inc' => ["seq" => 1]],
            null,
            ["new" => true]
        );

        if (!isset($retval['seq'])) {
            self::insert('counters', [
                '_id' => $name,
                'seq' => 1
            ]);
            return self::getNextSequence($name);
        }
        return $retval['seq'];
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
            return (string) $data;
        }
        return $data;
    }
}