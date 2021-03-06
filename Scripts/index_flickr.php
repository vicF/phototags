<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

try {

    require_once '../common.php';

    $flickr = Service::Flickr();
    $db = Service::Database();
    $pdo = Service::PDO();

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

            $description = html_entity_decode($photo['description']['_content']);
            $path = \SQLite3::escapeString("{$description}/{$photo['title']}");
            echo "{$photo['datetaken']} {$path} \n";
            $localImageId = $db->querySingle("SELECT image_id FROM image_files WHERE server=" . Base::FLICKR . " and service_id = {$photo['id']}");

            $media = Flickr::getFlickrMediaType($photo);
            $data = json_encode(Flickr::getAdditionalData($photo));
            if (!empty($localImageId)) {
                // that's OK! Already in database
                $pdo->do("UPDATE image_files SET revision = ?, status = ?, data = ?  WHERE image_id = ?", [$startTime, 1, $data, $localImageId]);

                $pdo->do("UPDATE images SET media = ? WHERE image_id = ?", [$media, $localImageId]);
                echo "OK!\n";
                continue;
            }
            $localImageId = null;
            if (strlen($description) > 14) {
                // description may look like 2012/2012-01-01

                $localImageId = $db->querySingle("SELECT image_id FROM image_files WHERE server=2 and path like '%{$path}'");
                if (!empty($localImageId)) {
                    // There is an image for this in database. Now let's check if it is already linked to this flickr
                    $flickrImageId = $db->querySingle("SELECT image_id FROM image_files WHERE service_id = '{$photo['id']}'");
                    if (!empty($flickrImageId)) {
                        // There's already record for flickr
                        if ($flickrImageId == $localImageId) {
                            // that's OK! Nothing to do!
                            $pdo->do("UPDATE image_files SET revision = ?, status = ?, data = ?  WHERE image_id = ?", [$startTime, 1, $localImageId, $data]);

                            $pdo->do("UPDATE images SET media = ? WHERE image_id = ?", [$media, $localImageId]);

                            echo "OK!\n";
                            continue;
                        } else {
                            // Files are the same but linked to different image_id
                            $db->exec("UPDATE image_files SET image_id = {$flickrImageId} WHERE image_id = {$localImageId}");
                            $db->exec("DELETE FROM images WHERE image_id = {$localImageId}");
                            echo "Merged {$localImageId} with {$flickrImageId}\n";
                        }
                    } else {
                        // This image is not in database
                        Flickr::addImageFileToBaseFromFlickr($photo, $localImageId, $startTime);
                        echo "Added from Flickr\n";
                    }
                } else {
                    // Image with such description is not found in the database
                    Flickr::addImageFileToBaseFromFlickr($photo, null, $startTime);
                    echo "Added file that is only on Flickr\n";
                }
            } else {
                // Image has short description. It was likely not loaded from main storage but from phone.
                echo "Short description\n";
                Flickr::addImageFileToBaseFromFlickr($photo, null, $startTime, 1);
                echo "Added file that is only on Flickr\n";
            }

        }


        $page++;
        $parameters['page'] = $page;
    } while ($photos['page'] < $photos['pages']);
} catch (\Throwable $e) {
    echo $e;
}