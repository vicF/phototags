<?php
namespace Fokin\PhotoTags;

/**
 * Class Yandex
 */
class Yandex
{
    protected static $_searches = ['disk:/', ' ', ',', ':', ';'];
    protected static $_replacements = ['%2Fdisk%2F', '%20', '%2C', '%3A', '%3B'];

    /**
     * @param $string
     * @return mixed
     */
    public static function urlEncodePath($string)
    {
        return str_replace(self::$_searches, self::$_replacements, $string);
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function urlEncodeParameter($string)
    {
        // Additionally replace slashes
        return str_replace('/', '%2F', self::urlEncodePath($string));
    }
}