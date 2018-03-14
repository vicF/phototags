<?php

namespace Fokin\PhotoTags;

require_once '../common.php';

try {

    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($photosDir));

    $it->rewind();
    $i = 0;


    $db = new \SQLite3('db/phototags1.db', SQLITE3_OPEN_READWRITE);
    if (!$db) {
        $error = (file_exists('../db/phototags1.db')) ? "Impossible to open, check permissions" : "Impossible to create, check permissions";
        die($error);
    }
    $db->enableExceptions(true);
    $db->busyTimeout(1000);

    $pageSize = 1000000000;

    $page = (int)@$_GET['page'];
    if ($page > 0) {
        $pos = 0;
        $start = $page * $pageSize;
        while ($it->valid()) {
            if (!$it->isDot()) {
                if ($pos++ >= $start) {
                    break;
                }
            }
            $it->next();
        }
    }

    while ($it->valid() AND $i++ < $pageSize) {

        if (!$it->isDot()) {
            $title = basename($it->getSubPathName());
            $comment = '';
            if (preg_match('/(\.jpg|\.png|\.bmp\.mov\.gif\.tiff\.mp4\.3gp)$/i', $it->getBasename())) {
                try {
                    $sql = "SELECT image_id FROM image_files WHERE server = 2 AND path = '" . $db->escapeString($it->key()) . "'";
                    $results = $db->query($sql);
                } catch (\Throwable $e) {
                    echo $sql . "\n";
                    echo $e;
                    die();
                }
                if ($results !== false) {
                    while ($row = $results->fetchArray()) {
                        $comment .= "Already in database, id: {$row['image_id']}!!!";
                        //$file = new ImageFile($it->key());
                        //$timestamp = $file->timestamp();
                        //$db->exec("UPDATE images set `timestamp` = $timestamp WHERE image_id = ".$row['image_id']);
                        //Tpl\Tpl::showImage($it->getSubPathName(), $it->getSubPathName(), $comment);
                        echo $it->getSubPathName() . "\n$comment\n\n";
                        $it->next();
                        continue 2;
                    }
                }
                $file = new ImageFile($it->key());

                $timestamp = $file->timestamp();

                $path = $db->escapeString($it->key());
                $filesize = $file->size();
                $width = $file->width();
                $height = $file->height();


                $title = $db->escapeString($title);
                $db->exec("INSERT INTO images (`title`, `timestamp`) VALUES ('{$title}', $timestamp)");

                $image_id = $db->lastInsertRowid();
                $db->exec("
            INSERT INTO image_files (image_id, server, path, filesize, width, height, service_id, thumb_url) 
            VALUES ({$image_id}, 2, '{$path}', '{$filesize}', '{$width}', '{$height}', null, null)");
                $comment .= 'Added, id:' . $image_id;

                //Tpl\Tpl::showImage($it->getSubPathName(), $it->getSubPathName(), $comment);
                echo $it->getSubPathName() . "\n$comment\n\n";
            }
        }
        $it->next();
    }
    echo 'SubPathName: ' . $it->getSubPathName();
    //Tpl\Tpl::end();

} catch (\Throwable $e) {
    echo $e;
}
//Tpl\Tpl::endBody();
?>
