<?php

namespace Fokin\PhotoTags;


/**
 * Class Service
 */
class Service
{
    /**
     * @return \SQLite3
     * @deprecated
     */
    public static function Database()
    {
        static $db;
        if (empty($db)) {
            $db = new \SQLite3('db/phototags1.db');
            if (!$db) {
                $error = (file_exists('../db/phototags1.db')) ? "Impossible to open, check permissions" : "Impossible to create, check permissions";
                die($error);
            }
            $db->enableExceptions(true);

            set_time_limit(0);
            $db->busyTimeout(10000);
        }
        return $db;
    }

    /**
     * @return PDO
     */
    public static function PDO () {
        // Create (connect to) SQLite database in file
        $db = new PDO('mysql:host=127.0.0.1;dbname=simplefacts;charset=utf8', 'simplefacts', 'factssimple');
        // Set errormode to exceptions
        $db->setAttribute(\PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    /**
     * @param string $mode
     * @return Flickr
     * @throws \Exception
     */
    public static function Flickr($mode = 'write')
    {
        static $flickr;

        if (empty($flickr)) {
            session_start();
            $_SESSION[Flickr::SESSION_OAUTH_DATA] = unserialize(file_get_contents('sessions/1.dat'));

            $callback = sprintf('%s://%s:%d%s',
                (@$_SERVER['HTTPS'] == "on") ? 'https' : 'http',
                @$_SERVER['SERVER_NAME'],
                @$_SERVER['SERVER_PORT'],
                @$_SERVER['SCRIPT_NAME']
            );

            $flickr = new Flickr(FLICKR_API_KEY, FLICKR_API_SECRET, $callback);

            if (!$flickr->authenticate($mode)) {
                throw new \Exception("Unable to authenticate to Flickr ...\n");
            }
        }

        return $flickr;
    }
}