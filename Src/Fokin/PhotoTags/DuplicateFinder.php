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
        return new Iterator\Database('SELECT * FROM media_files where media_id IS NULL AND filesize in (SELECT filesize
FROM
    media_files
GROUP BY filesize
HAVING COUNT(filesize) > 1) ORDER BY filesize, filename');
    }

    public function processDuplicates()
    {
        $db = Service::PDO();
        $previousPhoto = null;
        foreach ($this->select() as $photo) {
            if ($previousPhoto === null || $photo['filesize'] !== $previousPhoto['filesize']) {
                $previousPhoto = $photo;
                continue; // Going to next group
            }
            //print_r($photo);

            // Compare this photo with previous
            foreach (['filename', 'width', 'height', 'media_type'] as $key) {
                $matched = true;
                if ($previousPhoto[$key] !== $photo[$key]) {
                    $matched = false;
                }
            }
            if ($matched) {

                echo "\n\nSame photo:\n{$previousPhoto['filename']}--{$previousPhoto['path']}\n{$photo['filename']}--{$photo['path']}";


                $db->run('
              INSERT INTO media (filename, media_type, created)
              VALUES (?,?,?)', [$photo['filename'], $photo['media_type'], min($photo['created'], $previousPhoto['created'])]);
                $id = $db->lastInsertId();

                $db->run('
              UPDATE media_files SET media_id = ? WHERE media_file_id IN (?,?)
             ', [$id, $photo['media_file_id'], $previousPhoto['media_file_id']]);
                echo "\nInserted media $id";
            }
        }
    }

    public function assignMediaIdsToSingle() {
        $db = Service::PDO();
        foreach (new Iterator\Database('SELECT * FROM media_files where media_id IS NULL ORDER BY filesize, filename') as $photo) {
            $db->run('
              INSERT INTO media (filename, media_type, created)
              VALUES (?,?,?)', [$photo['filename'], $photo['media_type'], $photo['created']]);
            $id = $db->lastInsertId();

            $db->run('
              UPDATE media_files SET media_id = ? WHERE media_file_id = ?
             ', [$id, $photo['media_file_id'] ]);
            echo "\nInserted media $id";
        }
    }

}