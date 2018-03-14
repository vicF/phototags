<?php

namespace Fokin\PhotoTags;

require_once '../common.php';

$db = new \SQLite3('db/phototags1.db');
if (!$db) {
    $error = (file_exists('../db/phototags1.db')) ? "Impossible to open, check permissions" : "Impossible to create, check permissions";
    die($error);
}
$db->enableExceptions(true);

set_time_limit(0);
$db->busyTimeout(10000);

$total = 0;
$duplicates = 0;

try {
    $sql = "select f.rowid as file_id, * from image_files f left join images i on i.image_id = f.image_id order by filesize, title, timestamp";
    $results = $db->query($sql);
} catch (\Throwable $e) {
    echo $sql . "\n";
    die($e);
}
$oldString = '';
$oldId = null;
if ($results !== false) {
    while ($row = $results->fetchArray()) {
        //echo "Processing: {$row['path']}\n";
        $newString = $row['filesize'] . '=' . $row['title'] . '=' . $row['timestamp'];
        if ($newString == $oldString) {
            echo "Duplicate!!! $oldString\n";
            $sql = "update image_files set image_id = {$oldId} where rowid = {$row['image_file_id']}";
            echo $sql . "\n";
            $duplicates++;
            $db->query($sql);
        } else {
            $oldId = $row['image_id'];
            $oldString = $newString;
        }
        $total++;


        //print_r($row);
    }

    echo "total: $total, duplicates:$duplicates\n";
}