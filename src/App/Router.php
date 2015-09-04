<?php
namespace App;

use App\Session;
use App\Db\Mongo;

class Router {

    public static $fenom;
    public static $session;

    public static function start() {
        Mongo::setDb('copypaste');
        self::$session = Session::getSession();
        self::$fenom = \Fenom::factory('../templates', '../templates/cache', [
            'force_compile' => true,
            'strip' => true
        ]);
        
        if (isset($_GET['link'])) {
            $pin = isset($_GET['pin']) ? intval(substr($_GET['pin'], 1)) : null;
            $id = $_GET['link'];
            // Обработка короткой ссылки
            // link не может быть пустым, так что можно не проверять
            Api::followShortUrl($id, $pin);

        } else if (isset($_GET['url'])) {
            $url = $_GET['url'];
            // Обработка стандартных запросов
            if ($url == '') {
                // Если пустой
                self::indexPage();
            } else {
                switch ($url) {
                    case 'api/short':
                        // Сюда будет стучатсься AJAX'ом
                        Api::getShortUrl('url');
                        break;
                    case 'api/uploadFile':
                        // Загружаем
                        Api::processFiles();
                        break;
                    case 'api/debug':
                        // Дебаг методов
                        break;
                    case 'api/pin':
                        self::pinPage();
                        break;
                    default:
                        // Для дебага
                        // На неопределенные url'ы дампим $_SERVER
                        echo '<pre>';
                        var_dump($_SERVER);
                        echo '</pre>';
                        break;
                }
            }
        } else {
            self::indexPage();
        }
    }

    public static function indexPage() {
        // Две выборки, последнии ссылки и топ-ссылок по 10 штук
        $top_links = Api::getTopLinks(10);
        $last_links = Api::getLastLinks(10);

        self::$fenom->display('index.tpl',[
            'top_links' => $top_links['success'] ? $top_links['data'] : [],
            'last_links' => $last_links['success'] ? $last_links['data'] : []
        ]);
    }

    public static function pinPage() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : false;
        if (!$id) {
            header("Location: http://copypaste.su");
            die();
        }
        
        $link = Mongo::findOne('links', ['id' => $id]);

        if ($link) {
            self::$fenom->display('pin.tpl',[
                'link' => $link
            ]);
        } else {
            self::$fenom->display('link_not_found.tpl',[
                'id' => $id
            ]);
        }
    }
}
