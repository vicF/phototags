<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

try {

    require_once '../common.php';

    $query = new Database('select i.image_id, i.title, o.path from images i left join image_files f on f.image_id = i.image_id and f.server = 1 left join image_files o on o.image_id = i.image_id where f.image_file_id is null');

    $flickr = Service::Flickr();


    foreach ($query as $row) {
        echo $row['path'] . "\n";

        $set = basename(dirname($row['path']));

        $title = @$row['title'];

        $tag = '';

        $parameters = [
            'title' => $title,
            //'tags' => 'DPZFlickr'
        ];

        //$parameters['photo'] = '@' . $photo['tmp_name'];
        $parameters['photo'] = new \CURLFile($row['path']);


        $response = $flickr->upload($parameters);


        $ok = @$response['stat'];

        if ($ok == 'ok') {
            $photoId = (int)$response['photoid']['_content'];
            $flickr->addToSet($photoId, $set);

            Service::Database()->exec("INSERT INTO image_files (image_id, server, service_id)
              VALUES ({$row['image_id']}, 1, {$photoId})");
            //$photos = $response['photos'];
            //$message = "Photo uploaded";
        } else {
            throw new \Exception(@print_r($response['err'], 1));
            //$message = "Error: " . @$err['msg'];
        }

    }
} catch (\Throwable $e) {
    echo $e;
}