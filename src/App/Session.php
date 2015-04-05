<?php 

namespace App;

use App\Db\Mongo;

class Session {
    public static function createSession() {
        $sid = self::generateSessionID();
        $session = [
            'ctime' => new \MongoDate(),
            'sid' => $sid,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'hits' => 1,
            'last_activity' => new \MongoDate(),
            'agent' => $_SERVER['HTTP_USER_AGENT']
        ];
        Mongo::insert('session', $session);
        setcookie("sid", $sid, strtotime('+10 years'));
        return $sid;
    }

    public static function updateSession($sid) {
        $result = Mongo::findAndModify('session', [
                'sid' => $sid
            ], [
                '$inc' => ['hits' => 1],
                '$set' => ['last_activity' => new \MongoDate()]
        ]);
        if ($result) {
            return $sid;
        } else {
            return self::createSession();
        }
    }

    public static function getSession() {
        if (isset($_COOKIE['sid'])) {
            return self::updateSession($_COOKIE['sid']);
        } else {
            return self::createSession();
        }
    }

    public static function getSessionData($sid) {
        Mongo::findOne('session', [
            'sid' => $sid
        ]);
        return $session_data ? Mongo::clearMongo($session_data) : false;
    }

    private static function generateSessionID() {
        return md5(
            $_SERVER['HTTP_USER_AGENT']
            .'THIS_IS_SALT'
            .$_SERVER['REMOTE_ADDR']
        ).uniqid();
    }
}