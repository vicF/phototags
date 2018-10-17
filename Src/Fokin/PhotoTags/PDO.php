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
     * @throws \Exception
     */
    public function do($sql, $arguments = [])
    {
        try {
            $st = $this->prepare($sql);
            $st->execute($arguments);
            return $st;
        } catch (\Throwable $e) {
            Throw new \Exception("Error executing: \"{$sql}\" with args:" . print_r($arguments, 1), 0, $e);
        }
    }

}