<?php

namespace Fokin\PhotoTags;

//use getID3;


/**
 * Class ImageFile
 *
 * @package Fokin\PhotoTags
 */
class ImageFile
{
    protected $_file;
    protected $_exif = null;

    /**
     * ImageFile constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->_file = $name;
    }

    protected function _readExif()
    {
        if (is_null($this->_exif)) {
            $this->_exif = @exif_read_data($this->_file);
        }
        return is_array($this->_exif);
    }

    /**
     * @return false|int
     */
    public function timestamp()
    {
        //$getID3 = new getID3();
        //$ThisFileInfo = $getID3->analyze($this->_file);
        if ($this->_readExif()) {
            $timeIndexes = ['DateTimeOriginal', 'DateTime', 'FileDateTime'];
            foreach ($timeIndexes as $index) {
                if (array_key_exists($index, $this->_exif)) {

                    $time = strtotime($this->_exif[$index]);
                    if ($time > 0) {
                        return $time;
                    } else if (is_numeric($this->_exif[$index])) {
                        return $this->_exif[$index]; // already time stamp
                    }
                }
            }
        }
        return filemtime($this->_file);
    }

    /**
     * @return int
     */
    public function size()
    {
        if ($this->_readExif() AND array_key_exists('FileSize', $this->_exif)) {
            return $this->_exif['FileSize'];
        } else {
            return filesize($this->_file);
        }
    }

    /**
     * @return null
     */
    public function width()
    {
        if ($this->_readExif() AND array_key_exists('COMPUTED', $this->_exif)) {
            return $this->_exif['COMPUTED']['Width'];
        } else {
            return null;
        }
    }

    /**
     * @return null
     */
    public function height()
    {
        if ($this->_readExif() AND array_key_exists('COMPUTED', $this->_exif)) {
            return $this->_exif['COMPUTED']['Height'];
        } else {
            return null;
        }
    }
}