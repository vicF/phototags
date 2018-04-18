<?php
namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;
use Fokin\PhotoTags\Tpl\Tpl;

require_once '../common.php';

$dbIterator = new Database("select * from image_files f left join images i on f.image_id = i.image_id order by filesize, image_id limit 1000 offset 200");

Tpl::startBody();

foreach($dbIterator as $image) {
    Tpl::showAnyImage($image);
}

Tpl::endBody();