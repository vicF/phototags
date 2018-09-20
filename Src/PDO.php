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
        try {
            $st = $this->prepare($sql);
            $st->execute($arguments);
        } catch (\Throwable $e) {
            echo $e;
            echo 'RETRYING ...';
            sleep(3);
            return $this->do($sql, $arguments);
        }
        return $st;
    }
}