<?php
/**
 * phototags
 * User: fokin
 * Created: 2019-06-20
 */

namespace Fokin\PhotoTags;

require_once '../common.php';

$db = Service::PDO();
foreach (new Iterator\Database('SELECT * FROM media where uuid IS NULL') as $photo) {
    $uuid = Service::uuid();
    $value = str_replace('-', '', $uuid);
    $db->run('
              UPDATE media SET uuid = UNHEX(?) 
              WHERE media_id = ?', [$value, $photo['media_id']]);

    echo "\nInserted media $uuid";
}