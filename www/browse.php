<?php
namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;
use Fokin\PhotoTags\Tpl\Tpl;

try {
    require_once '../common.php';

    if (!empty($_POST)) {
        $where = '';
        if (!empty($_POST['source'])) {
            $source = [];
            foreach ($_POST['source'] as $serverId) {
                $source[] = "server=" . (int)$serverId;
            }
            $source = implode(' OR ', $source);
            $where .= $source;
        }
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        $sql = "from image_files f left join images i on f.image_id = i.image_id {$where} order by filesize, i.image_id";
    } else {
        $sql = "from image_files f left join images i on f.image_id = i.image_id order by filesize, i.image_id";
    }

    $total = Service::Database()->querySingle('select count(*) ' . $sql);

    $sql = 'select * ' . $sql . ' limit 1000 offset ' . (int)@$_POST['page'] * 1000;

    $dbIterator = new Database($sql);

    Tpl::startBody();
    Tpl::startHeader();
    Tpl::header($total, @$_POST);
    Tpl::endHeader();

    $lastId = null;

    foreach ($dbIterator as $image) {
        if (!$image) {
            break;
        }
        Tpl::showAnyImage($image);
    }

    Tpl::endBody();
} catch (\Throwable $e) {
    echo $e;
}