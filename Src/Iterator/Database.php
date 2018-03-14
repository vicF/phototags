<?php

namespace Fokin\PhotoTags\Iterator;

use Fokin\PhotoTags\Service;


/**
 * Class Database
 *
 * @package Fokin\PhotoTags\Iterator
 */
class Database implements \Iterator
{
    protected $_sql;

    protected $_result;

    protected $_current;

    protected $_key = 0;


    /**
     * Database constructor.
     *
     * @param $sql
     */
    public function __construct($sql)
    {
        $this->_sql = $sql;
    }

    /**
     * @return resource|\SQLite3Result
     */
    protected function _getResource()
    {
        if (!is_object($this->_result)) {

            $this->_result = Service::Database()->query($this->_sql);
        }
        return $this->_result;
    }

    /**
     *
     */
    public function current()
    {
        if (empty($this->_current)) {
            $this->_current = $this->_getResource()->fetchArray(SQLITE3_ASSOC);
        }
        return $this->_current;
    }

    /**
     *
     */
    public function key()
    {
        return $this->_key;
    }

    public function next()
    {
        $this->_current = $this->_getResource()->fetchArray(SQLITE3_ASSOC);
        $this->_key++;
    }

    public function rewind()
    {
        $this->_getResource()->reset();
    }

    /**
     *
     */
    public function valid()
    {
        return $this->_current !== false;
    }
}