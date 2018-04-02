<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

try {

    require_once '../common.php';

    $dbIterator = new Database("select i.image_id, d.path, di.title, d.image_file_id from image_files f left join image_files l on f.image_id = l.image_id AND l.server = 2  left join images i on i.image_id = f.image_id LEFT JOIN image_files d on f.filesize = d.filesize and f.image_id != d.image_id and d.server = 2 left join images di on d.image_id = di.image_id and i.title = di.title WHERE f.server = 1 AND l.image_file_id is null and di.title is not null;");

    $db = Service::Database();


    foreach ($dbIterator as $photo) {
        echo "{$photo['path']}  \n";
        $sql = "UPDATE image_files SET image_id = {$photo['image_id']}  WHERE image_file_id = '{$photo['image_file_id']}'\n";
        $db->exec($sql);
    }


} catch (\Throwable $e) {
    echo $e;
}