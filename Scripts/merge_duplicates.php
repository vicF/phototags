<?php


namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

require_once '../common.php';
$db = Service::Database();

$total = 0;
$duplicates = 0;

$dbIterator = new Database("select f.* from media_files f  order by  f.filename, f.created, f.filesize");


$oldNameString = '';
$oldTimeString = '';
$oldRow = [];
$oldId = null;

//if ($results !== false) {
foreach ($dbIterator as $row) {
    if($row['filename'] == 'olin_dedushka.jpg') {
        echo '';
    }
    //  while ($row = $results->fetchArray()) {
    //echo "Processing: {$row['path']}\n";
    $newNameString = $row['filesize'] . '=' . $row['filename']; // . '=' . $row['created'];
    $newTimeString = $row['filesize'] . '=' . $row['created'];
    if (($newNameString === $oldNameString || $newTimeString === $oldTimeString) && !is_null($row['media_id']) && $oldId != $row['media_id']) {
        if(is_null($oldRow['media_id'])) {
            // Create media record
        }
        echo "Possible Duplicate!!! \n" . $row['filesize'] . '=' . $row['filename'] . '=' . $row['created'] . "\n" .
            $oldRow['filesize'] . '=' . $oldRow['filename'] . '=' . $oldRow['created'];
        $sql = "update image_files set media_id = {$oldId} where media_file_id = {$row['media_file_id']}";
        echo $sql . "\n";
        $duplicates++;
        $db->query($sql);
    } else {
        $oldId = $row['media_id'];
        $oldRow = $row;
        $oldNameString = $newNameString;
        $oldTimeString = $newTimeString;
    }
    $total++;


    //print_r($row);
}

echo "total: $total, duplicates:$duplicates\n";
//}