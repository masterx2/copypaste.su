<?php

namespace App;

use App\Router;
use App\Db\Mongo;

class Api {
    /** @var string */
    protected static $api_key;
    /** @var string */
    protected static $api_url;

    /**
     * Генерирует коротку ссылку
     * @param string $type тип ссылки
     * @param null $raw_url
     * @param null $extra_data
     */
    public static function getShortUrl($type, $raw_url=null, $extra_data=null) {
        if (isset($_POST['url']) || isset($raw_url)) {
            $id = Mongo::getNextSequence('link');
            $url = isset($raw_url) ? $raw_url : $_POST['url'];
            $pin = isset($_POST['secure']) && $_POST['secure'] != 'false' ? substr(mt_rand(10000,20000), 1) : false;

            $result = Mongo::insert('links', [
                'type' => $type,
                'original_url' => urlencode($url),
                'url' => self::id2url($id),
                'id' => $id,
                'sid' => Router::$session,
                'extra_data' => $extra_data,
                'click_count' => 0,
                'last_click' => new \MongoDate(),
                'pin' => $pin
            ]);

            self::ajaxResponse([
                'id' => $id,
                'success' => true,
                'url' => self::id2url($id),
                'pin' => $pin
            ]);

        } else {
            self::ajaxResponse(self::_error('Miss url!'));
        }
    }

    public static function followShortUrl($url, $pin) {
        $result = Mongo::findAndModify('links', [
                'url' => $url
            ], [
                '$inc' => ['click_count' => 1],
                '$set' => ['last_click' => new \MongoDate()]
        ]);

        if ($result) {
            if (!empty($result['pin'])) {
                if (intval($result['pin']) != $pin) {
                    header("Location: http://copypaste.su/api/pin?id=".$result['id']);
                    die();
                }
            }
            $url = urldecode($result['original_url']);
            header("Location: $url");
            die();
        }
    }

    public static function processFiles() {
        if (self::getApiParams()) {
            if (isset($_FILES['cppt_file'])) {
                $files_error = $_FILES['cppt_file']['error'];
                $file_index = 0;
                $filename = count($files_error) == 1 ? $_FILES['cppt_file']['name'][0] : (string)count($files_error).'_files_'.uniqid();                 
                if (isset($_POST['zip']) && $_POST['zip'] == 1) {    
                    $zipfile = "/tmp/".$filename.".zip";
                    $zip = new \ZipArchive();
                    if ($zip->open($zipfile, \ZipArchive::CREATE)) {
                        foreach ($files_error as $err) {
                            if ($err == 0) {
                                $files_props[] = [
                                    'name' => $_FILES['cppt_file']['name'][$file_index],
                                    'size' => $_FILES['cppt_file']['size'][$file_index]
                                ];
                                $zip->addFile($_FILES['cppt_file']['tmp_name'][$file_index],
                                              $_FILES['cppt_file']['name'][$file_index]);
                            }
                            $file_index += 1;
                        }
                        $zip->close();
                        self::uploadToStorage([
                            'path' => $zipfile,
                            'name' => $filename.'.zip',
                            'size' => filesize($zipfile)
                        ], 'zip', $files_props);
                    } else {
                        self::ajaxResponse(self::_error('Can\'t create zip!'));
                    }
                } else {
                    self::uploadToStorage([
                        'path' => $_FILES['cppt_file']['tmp_name'][0],
                        'name' => $_FILES['cppt_file']['name'][0],
                        'size' => $_FILES['cppt_file']['size'][0]
                    ]);
                }
            } else {
                self::ajaxResponse(self::_error('File(s) not found!'));
            }
        } else {
            self::ajaxResponse(self::_error('Can\'t get storage api key!'));
        }
    }


    public static function uploadToStorage($file, $type='file', $files_props=null) {
        $headers = [
            "X-Auth-Token: ".self::$api_key
            // "X-Delete-After: 604800"
        ];
        $real_filename = urlencode($file['name']);
        $file_res = fopen($file['path'], 'r');
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_PUT            => true,
            CURLOPT_INFILE         => $file_res,
            CURLOPT_INFILESIZE     => $file['size']
        ];

        $upload_url = self::$api_url.'Storage/'.$real_filename;
        $response = self::parseHeader(
            self::HTTP($upload_url, $headers, $options)
        );
        fclose($file_res);

        if (isset($response['error'])) {
            self::ajaxResponse(self::_error('Error parsing response from hosting: '.$response['error']));
        } else if ($response['status'] == 201) {
            self::getShortUrl($type, 'http://storage.copypaste.su/'.$real_filename, $files_props);
            unlink($file['path']);
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
        self::$api_key = $auth_key;
        self::$api_url = $auth_url;
        return true;
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
        if (!empty($result)) {
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
        if (!empty($result)) {
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
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Max-Age: 1000');
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public static function _error($message) {
        return [
            'success' => false,
            'message' => $message
        ];
    }
}
