<?php

namespace App;

use App\Router;
use App\Db\Mongo;

class Api {
    public static function getShortUrl($type, $raw_url=null) {
        if (isset($_POST['url']) || isset($raw_url)) {
            $id = Mongo::getNextSequence('link');
            $url = isset($raw_url) ? $raw_url : $_POST['url'];
            $result = Mongo::insert('links', [
                'type' => $type,
                'original_url' => urlencode($url),
                'url' => self::id2url($id),
                'id' => $id,
                'sid' => Router::$session,
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
                'url' => $url
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

    public static function uploadFile() {
        $file = $_FILES['cppt_file'];
        if ($file['error'] == 0) {
            list($key, $url) = self::getApiParams();
            if (isset($key, $url)) {
                $headers = [
                    "X-Auth-Token: $key",
                    "X-Delete-After: 604800"
                ];
                $real_filename = urlencode($file['name']);
                $file_res = fopen($file['tmp_name'], 'r');
                $options = [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => true,
                    CURLOPT_PUT            => true,
                    CURLOPT_INFILE         => $file_res,
                    CURLOPT_INFILESIZE     => $file['size']
                ];

                $upload_url = $url.'Storage/'.$real_filename;
                $response = self::parseHeader(
                    self::HTTP($upload_url, $headers, $options)
                );
                fclose($file_res);
                
                if (isset($response['error'])) {
                    self::ajaxResponse(self::_error('Error parsing response from hosting: '.$response['error']));
                } else if ($response['status'] == 201) {
                    self::getShortUrl('file', $upload_url);
                }
            } else {
                self::ajaxResponse(self::_error('Can\'t get storage api key!'));
            }
        } else {
            self::ajaxResponse(self::_error('Upload Error!'));
        }
    }

    public static function getApiParams() {
        $redis = new \Redis();
        $redis->connect('localhost');

        $auth_key = $redis->get('storage_api_key');
        $auth_url = $redis->get('storage_api_url');

        if (!$auth_key) {
            $headers = [
                "X-Auth-User:36471_cppt",
                "X-Auth-Key:eVeelvP7Zz"
            ];

            $options = [
                CURLOPT_HEADER         => true,
                CURLOPT_RETURNTRANSFER => true
            ];

            $response = self::parseHeader(self::HTTP('https://auth.selcdn.ru/', $headers, $options));
            
            if (isset($response['error'])) {
                return false;
            } else if (isset($response['status']) && $response['status'] == 204){
                if (isset($response['headers']) && array_key_exists('X-Auth-Token', $response['headers'])) {
                    $auth_key = $response['headers']['X-Auth-Token'];
                    $auth_url = $response['headers']['X-Storage-Url'];
                    $key_ttl = intval($response['headers']['X-Expire-Auth-Token']);
                    $redis->set('storage_api_key', $auth_key, $key_ttl);
                    $redis->set('storage_api_url', $auth_url, $key_ttl);
                }
            }
        }
        return [$auth_key, $auth_url];
    }

    public static function HTTP($url, $headers=[], $custom_options=[]) {
        $curl = curl_init();
        $options = array_replace([
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120
        ], $custom_options);
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        return $response;
    }

    public static function parseHeader($response) {
        // Parse Response
        $raw_headers = explode("\n", trim($response));
        if (isset($raw_headers[0])) {   
            // Parse status string
            $status = array_shift($raw_headers);
            preg_match('/^([A-Z]+)\/([0-9.]{3,5}) ([0-9]{3}) (.+)$/', $status, $preg_result);
            array_shift($preg_result);
            list($protocol_type, $protocol_version, $status_code, $status_message) = $preg_result;
            if (isset($protocol_type, $protocol_version, $status_code, $status_message)) {
                // HTTP/1.1 100 Continue
                if (intval($status_code) == 100) {
                    return self::parseHeader(implode("\n",array_filter($raw_headers)));
                }
                $headers = array_reduce($raw_headers, function($accum, $item) {
                    $accum[strstr($item, ': ', true)] = trim(substr(strstr($item, ': '), 2));
                    return $accum;
                }, []);

                return [
                    'status' => intval($status_code),
                    'protocol' => $protocol_type,
                    'version' => $protocol_version,
                    'status_message' => $status_message,
                    'headers' => $headers
                ];
            } else {
                return ['error' => 'Bad status string'];
            }
        } else {
            return ['error' => 'Bad header format'];
        }
        return ['error' => 'This is not a header'];
    }

    public static function getTopLinks($num) {
        $result = Mongo::find('links', ['sid' => Router::$session], ['click_count' => -1], $num);
        if ($result) {
            return [
                'success' => true,
                'data' => Mongo::clearMongo($result)
            ];
        } else {
            return self::_error('Something wrong');
        }
    }

    public static function getLastLinks($num) {
        $result = Mongo::find('links', ['sid' => Router::$session], ['_id' => -1], $num);
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
        $toBase =   str_split($toBaseInput, 1);
        $number =   str_split($numberInput, 1);
        $fromLen =  strlen($fromBaseInput);
        $toLen=     strlen($toBaseInput);
        $numberLen= strlen($numberInput);
        
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
