<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

try {

    require_once '../common.php';

    $startTime  = time();
    $uploaded = 0;

    $query = new Database('select i.image_id, i.title, o.path from images i left join image_files f on f.image_id = i.image_id and f.server = 1 left join image_files o on o.image_id = i.image_id where f.image_file_id is null');

    $flickr = Service::Flickr();

    $counter = 0;
    foreach ($query as $row) {
        echo $row['path'] . "\n";

        $set = basename(dirname($row['path']));

        $title = @$row['title'];

        $tag = '';
        $description=$row['path'];

        $parameters = [
            'title' => $title,
            //'tags' => 'DPZFlickr'
            'description' => $description,
        ];

        //$parameters['photo'] = '@' . $photo['tmp_name'];
        $parameters['photo'] = new \CURLFile($row['path']);


        $response = $flickr->upload($parameters);


        $ok = @$response['stat'];

        if ($ok == 'ok') {
            $photoId = (int)$response['photoid']['_content'];
            $flickr->addToSet($photoId, $set);

            $filesize = filesize($row['path']);

            if($filesize == 0) {
                echo "Zero size!!!!\n";
                continue;
            }

            Service::Database()->exec("INSERT INTO image_files (image_id, server, service_id, filesize)
              VALUES ({$row['image_id']}, 1, {$photoId}, {$filesize})");
            //$photos = $response['photos'];
            //$message = "Photo uploaded";
        } else {
            throw new \Exception(@print_r($response['err'], 1));
            //$message = "Error: " . @$err['msg'];
        }

        if($counter++ > 100) {
            Base::fillFlickrDataForRecentUploads($startTime-600);
            $counter= 0;
            $now = new \DateTime("now");
            $stop = new \DateTime("09:00");
            if($now > $stop) {
                echo 'Exiting in the morning';
                break;
            }
        }



    }
    if($counter!= 0 ) {
        Base::fillFlickrDataForRecentUploads($startTime-600);
    }
} catch (\Throwable $e) {
    echo $e;
}