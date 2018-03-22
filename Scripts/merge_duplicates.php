<?php


namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

require_once '../common.php';
$db = Service::Database();

$total = 0;
$duplicates = 0;

$dbIterator = new Database("select f.rowid as file_id, * from image_files f left join images i on i.image_id = f.image_id order by  title, timestamp, filesize");


$oldString = '';
$oldId = null;

//if ($results !== false) {
    foreach ($dbIterator as $row) {
        //  while ($row = $results->fetchArray()) {
        //echo "Processing: {$row['path']}\n";
        $newString = //$row['filesize'] . '=' .
            $row['title'] . '=' . $row['timestamp'];
        if ($newString == $oldString and $oldId != $row['image_id']) {
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
//}