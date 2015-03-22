<?php
namespace App;

use App\Db\Mongo;

class Api {
	public static function getShortUrl() {
		if (isset($_POST['url'])) {
			$id = substr(uniqid(), 3, 10); // Новый Id
			$url = urlencode($_POST['url']);
			$result = Mongo::insert('links', [
				'type' => 'url',
				'original_url' => $url,
				'id' => $id,
				'click_count' => 0,
				'last_click' => new \MongoDate() 
			]);

			self::ajaxResponse([
				'success' => true,
				'id' => $id
			]);

		} else {
			self::ajaxResponse(self::_error('Miss url!'));
		}
	}

	public static function followShortUrl($id) {
		$result = Mongo::findAndModify('links', [
				'id' => $id,
				'type' => 'url'
			], [
				'$inc' => ['click_count' => 1],
				'$set' => ['last_click' => new \MongoDate()]
		]);

		if ($result) {
			$url = urldecode($result['original_url']);
			header("Location: $url");
			die();
		}
	}

	public static function getLastLinks($num) {
		$result = Mongo::find('links', ['type'=>'url'], ['_id' => -1], $num);
		if ($result) {
			return [
				'success' => true,
				'data' => Mongo::clearMongo($result)
			];
		} else {
			return self::_error('Something wrong');
		}
	}

	public static function getTopLinks($num) {
		$result = Mongo::find('links', ['type'=>'url'], ['click_count' => -1], $num);
		if ($result) {
			return [
				'success' => true,
				'data' => Mongo::clearMongo($result)
			];
		} else {
			return self::_error('Something wrong');
		}
	}

	public static function ajaxResponse($response) {
        header('Content-Type: application/json');
        print json_encode($response);
    }

    public static function _error($message) {
        return [
            'success' => false,
            'message' => $message
        ];
    }
}