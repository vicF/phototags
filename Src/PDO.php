<?php

namespace Fokin\PhotoTags;

/**
 * Class PDO
 *
 * @package Fokin\PhotoTags
 */
class PDO extends \PDO
{
    /**
     * @param $sql
     * @param array $arguments
     * @return \PDOStatement
     */
    public function do($sql, $arguments)
    {
        $st = $this->prepare($sql);
        $st->execute($arguments);
        return $st;
    }

}