<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

try {

    require_once '../common.php';

    $flickr = Service::Flickr();
    $db = Service::Database();

    $startTime = time();
    $page = 1;

    $parameters = [
        'user_id'  => FLICKR_USER,
        'per_page' => 100,
        'extras'   => 'description,date_taken,o_dims,url_o,url_t,path_alias,original_format,last_update,geo,tags,machine_tags,o_dims,views,media',
        'sort'     => 'date-taken-asc',
        'page'     => $page
    ];


    do {
        $response = $flickr->call('flickr.photos.search', $parameters);
        $photos = $response['photos'];
        foreach ($photos['photo'] as $photo) {
            echo "{$photo['datetaken']} {$photo['title']} \n";

            $results = $db->query("SELECT image_id FROM image_files WHERE service_id = '{$photo['id']}'");
            if ($results !== false) {
                while ($row = $results->fetchArray()) {
                    echo "Already in database, id: {$row['image_id']}!!!\n";
                    $results1 = $db->query("SELECT image_id FROM image_files WHERE server=2 AND path like '%{$photo['description']['_content']}'");
                    $dup = $results->fetchArray();
                    if($dup===false) {
                        continue 2;
                    } else {
                        echo '';
                    }

                }
            }
            $headers = get_headers($photo['url_o'], 1);
            $timestamp = strtotime($photo['datetaken']);

            //$res = $db->exec("INSERT INTO images (`title`, `timestamp`) VALUES ('{$photo['title']}', $timestamp)");

            //$image_id = $db->lastInsertRowid();
            /*$query = $db->exec("
        INSERT INTO image_files (image_id, server, path, filesize, width, height, service_id, thumb_url)
        VALUES ({$image_id}, 1, '{$photo['url_o']}', '{$headers['Content-Length']}', '{$photo['width_o']}', '{$photo['height_o']}', '{$photo['id']}', '{$photo['url_t']}')");*/


        }


        $page++;
        $parameters['page'] = $page;
    } while (true);  // Add condition to stop !!!!!!
} catch (\Throwable $e) {
    echo $e;
}