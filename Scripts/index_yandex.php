<?php
namespace Fokin\PhotoTags;

use Siyahmadde\Disk;
use Siyahmadde\Iterator;


$token = 'AQAAAAAAP_f_AAUvB2YHj5HqtkqVm2TpzKmFrUM';


try {

    require_once '../common.php';
    $startTime = time();

    $db = Service::PDO();
    $disk = new Disk('f05ba99bcbf441a69565e75c36547574');
    $disk->setReturnDecoded();
    $disk->setToken($token);
    $files = new Iterator($disk, '/Archive/Photo', [
        Iterator::RECURSIVE      => true,
        Iterator::RETURN_FOLDERS => false
    ]);


    foreach ($files as $file) {
        echo $file->path . "\n";
        if (!in_array($file->media_type, ['image', 'video'])) {
            // Unsupported type
            continue;
        }

        $creationDate = strtotime(@$file->exif->date_time ?: $file->created);
        $res = $db->do('SELECT media_file_id FROM media_files WHERE server_type = ' . Base::YANDEX . ' AND service_id = ?', [$file->resource_id]);
        if ($res->fetch()) {
            // Already exists
            // Just update revision
            $db->do('UPDATE media_files 
              SET revision = ?, status = 1, created = ?, filename = ?
              WHERE service_id = ?',
                [$startTime, $creationDate, $file->name, $file->resource_id]);
            echo " - Already exists\n";
            continue;
        } else {
            $width = null;
            $height = null;
            switch ($file->media_type) {
                case 'image':
                    /** @noinspection PhpAssignmentInConditionInspection */
                    if ($sizeInfo = getimagesize($file->file)) {
                        $width = $sizeInfo[0];
                        $height = $sizeInfo[1];
                    }
                    break;
                default;
                    // Probably video
            }

            /*$getID3 = new getID3();
            $ThisFileInfo = $getID3->analyze($file->file);*/
            $db->do('INSERT INTO media_files 
              (media_id, server_type, path, filesize, filename, 
              width, height, created, service_id, 
              thumb_url, revision, status) 
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                [null, Base::YANDEX, $file->path, $file->size, $file->name,
                    $width, $height, $creationDate, $file->resource_id,
                    @$file->preview, $startTime, 1]);
            echo " - Added\n";
        }
    }

} catch (\Throwable $e) {
    echo $e;
}