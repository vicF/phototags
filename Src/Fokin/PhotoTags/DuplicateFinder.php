<?php

namespace Fokin\PhotoTags;


/**
 * Class DuplicateFinder
 *
 * @package Fokin\PhotoTags
 */
class DuplicateFinder
{
    public function select()
    {
        return new Iterator\Database('SELECT * FROM media_files where filesize in (SELECT filesize
FROM
    media_files
GROUP BY filesize
HAVING COUNT(filesize) > 1)');
    }

    public function processDuplicates() {
        foreach($this->select() as $row) {

        }
    }

}