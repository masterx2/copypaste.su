<?php
namespace App;

use App\Db\Mongo;

class Api {
	public static function getShortUrl() {
		if (isset($_POST['url'])) {
			$id = Mongo::getNextSequence('link');
			$url = urlencode($_POST['url']);
			$result = Mongo::insert('links', [
				'type' => 'url',
				'original_url' => $url,
				'url' => self::id2url($id),
				'id' => $id,
				'click_count' => 0,
				'last_click' => new \MongoDate() 
			]);

			self::ajaxResponse([
				'success' => true,
				'url' => self::id2url($id),
				'id' => $id
			]);

		} else {
			self::ajaxResponse(self::_error('Miss url!'));
		}
	}

	public static function followShortUrl($url) {
		$result = Mongo::findAndModify('links', [
				'url' => $url,
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

	public static function id2url($id) {
		return self::convBase($id, '0123456789', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}

	public static function url2id($url) {
		return self::convBase($url, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', '0123456789');
	}

	private static function convBase($numberInput, $fromBaseInput, $toBaseInput) {
    	if ($fromBaseInput==$toBaseInput) return $numberInput;
    	
    	$fromBase = str_split($fromBaseInput, 1);
    	$toBase = 	str_split($toBaseInput, 1);
    	$number = 	str_split($numberInput, 1);
    	$fromLen = 	strlen($fromBaseInput);
    	$toLen=		strlen($toBaseInput);
    	$numberLen=	strlen($numberInput);
    	
    	$retval='';

    	if ($toBaseInput == '0123456789') {
        	$retval=0;
        	for ($i = 1;$i <= $numberLen; $i++) {
            	$retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
        	}
        	return $retval;
    	}
    
    	if ($fromBaseInput != '0123456789') {
        	$base10=convBase($numberInput, $fromBaseInput, '0123456789');
    	} else {
        	$base10 = $numberInput;
    	}
    	
    	if ($base10<strlen($toBaseInput)) {
        	return $toBase[$base10];
    	}
    	
    	while($base10 != '0') {
        	$retval = $toBase[bcmod($base10,$toLen)].$retval;
        	$base10 = bcdiv($base10,$toLen,0);
    	}

    	return $retval;
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