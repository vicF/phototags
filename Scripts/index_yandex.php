<?php
namespace Fokin\PhotoTags;

use Siyahmadde\Disk;
use Siyahmadde\Iterator;

$token = 'AQAAAAAAP_f_AAUvB2YHj5HqtkqVm2TpzKmFrUM';


try {

    require_once '../common.php';


    $disk = new Disk('f05ba99bcbf441a69565e75c36547574');
    $disk->setReturnDecoded();
    $disk->setToken($token);
    $files = new Iterator($disk, '/Archive/Photo', [
        Iterator::RECURSIVE      => true,
        Iterator::RETURN_FOLDERS => false
    ]);


    foreach ($files as $file) {
        echo $file->path . "\n";
    }


} catch (\Throwable $e) {
    echo $e;
}