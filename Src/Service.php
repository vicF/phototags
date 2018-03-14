<?php

namespace Fokin\PhotoTags;

/**
 * Class Service
 */
class Service
{
    /**
     * @return \SQLite3
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
}