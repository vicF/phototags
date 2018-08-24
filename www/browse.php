<?php
namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;
use Fokin\PhotoTags\Tpl\Tpl;

try {
    require_once '../common.php';

    if (!empty($_POST)) {
        $where = '';
        if(!empty($_POST['source'])) {
            $source = [];
            foreach($_POST['source'] as $serverId) {
                $source[] = "server=".(int)$serverId;
            }
            $source = implode(' OR ', $source);
            $where .= $source;
        }
        if(!empty($where)) {
            $where = ' WHERE '.$where;
        }
        $sql = "select * from image_files f left join images i on f.image_id = i.image_id {$where} order by filesize, image_id limit 1000 offset 200";
    } else {
        $sql = "select * from image_files f left join images i on f.image_id = i.image_id order by filesize, image_id limit 1000 offset 200";
    }

    $dbIterator = new Database($sql);

    Tpl::startBody();
    Tpl::startHeader();
    Tpl::header();
    Tpl::endHeader();

    $lastId = null;

    foreach ($dbIterator as $image) {
        //if (!is_null($lastId))
        Tpl::showAnyImage($image);
    }

    Tpl::endBody();
} catch (\Throwable $e) {
    echo $e;
}