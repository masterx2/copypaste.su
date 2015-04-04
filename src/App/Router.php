<?php
namespace App;

use App\Db\Mongo;

class Router {

    public static $fenom;

    public static function start() {

        Mongo::setDb('copypaste');

        self::$fenom = \Fenom::factory('../templates', '../templates/cache', [
            'force_compile' => true,
            'strip' => true
        ]);
        
        if (isset($_GET['link'])) {
            $id = $_GET['link'];
            // Обработка короткой ссылки
            // link не может быть пустым, так что можно не проверять
            Api::followShortUrl($id);

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
                        Api::uploadFile();
                        break;
                    case 'api/debug':
                        // Дебаг методов
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
        $last_links = Api::getLastLinks(10)['data'];
        $top_links = Api::getTopLinks(10)['data'];

        self::$fenom->display('index.tpl',[
            'last_links' => $last_links,
            'top_links' => $top_links
        ]);
    }
}
