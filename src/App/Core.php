<?php
namespace App;

use App\Db\Mongo;
use App\Db\RedisDriver;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\Config;
use Symfony\Component\HttpFoundation\Request;

class Core {

    /** @var \Fenom */
    public static $fenom;

    /** @var Request */
    public static $request;

    /** @var string Session ID */
    public static $session;

    /** @var Logger */
    public static $log;

    /** @var Config */
    public static $config;

    public static function start() {
        self::$config = new Config([BASE_DIR.'config/base.json','?'.BASE_DIR."config/local.json"]);
        self::$log = new Logger('copypaste');

        $handler = new StreamHandler(BASE_DIR.'logs/'.self::$config['log']);
        self::$log->pushHandler($handler);

        RedisDriver::connect(self::$config['redis']);
        Mongo::connect(self::$config['mongo']);

        self::$request = Request::createFromGlobals();
        self::$session = Session::getSession();

        self::$fenom = \Fenom::factory(BASE_DIR.'templates', BASE_DIR.'var/template_cache', [
            'force_compile' => true,
            'strip' => true
        ]);

        // Главный роутинг
        $link = self::$request->query->getAlnum('link');
        $url = self::$request->query->get('url');
        $pin = self::$request->query->getInt('pin');

        if ($link) {
            Api::followShortUrl($link, $pin);
        } elseif (!empty($url)) {
            // Обработка стандартных запросов
            if ($url == '') {
                // Если пустой
                self::indexPage();
            } else {
                self::dispatch(self::$request);
            }
        } else {
            self::indexPage();
        }
    }

    public static function dispatch(Request $request) {
//        switch ($url) {
//            case 'api/short':
//                // Сюда будет стучатсься AJAX'ом
//                Api::getShortUrl('url');
//                break;
//            case 'api/uploadFile':
//                // Загружаем
//                Api::processFiles();
//                break;
//            case 'api/debug':
//                // Дебаг методов
//                break;
//            case 'api/pin':
//                self::pinPage();
//                break;
//            default:
//                // Для дебага
//                // На неопределенные url'ы дампим $_SERVER
//                echo '<pre>';
//                var_dump($_SERVER);
//                echo '</pre>';
//                break;
//        }
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
