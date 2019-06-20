<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Exception\ExpectedException;

try {

    require_once '../common.php';

    $flickr = Service::Flickr();
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
            try {
                if (empty($photo['url_o'])) {
                    if ($photo['media_status'] == 'failed') {
                        echo 'Image has failed on Flickr';
                        continue;
                    }
                    print_r($photo);
                    die('empty url');
                }

                $description = html_entity_decode($photo['description']['_content']);
                $path = "{$description}/{$photo['title']}";
                echo "{$photo['datetaken']} {$path} \n";
                $localImageId = $pdo->run("SELECT media_file_id FROM media_files WHERE server_type=? and service_id = ?", [Base::FLICKR, $photo['id']])->fetchColumn();

                $mediaType = Flickr::getFlickrMediaType($photo);
                $data = json_encode(Flickr::getAdditionalData($photo));
                if (!empty($localImageId)) {
                    // that's OK! Already in database
                    /*$pdo->do("UPDATE media_files SET revision = ?, status = ?, data = ?, media_type = ?  WHERE media_file_id = ?", [$startTime, 1, $data, $mediaType, $localImageId]);*/

                    //$pdo->do("UPDATE media SET media_type = ? WHERE media_id = ?", [$media, $localImageId]);
                    echo "Already exists\n";
                    continue;
                } else {
                    Flickr::addImageFileToBaseFromFlickr($photo, null, $startTime);
                    echo "Added new file\n";
                    continue;
                }
                /*$localImageId = null;
                if (strlen($description) > 14) {
                    // description may look like 2012/2012-01-01

                    $localImageId = $pdo->do("SELECT media_id FROM media_files WHERE server_type=2 and path like concat('%', ?)", [$path])->fetchColumn();
                    if (!empty($localImageId)) {
                        // There is an image for this in database. Now let's check if it is already linked to this flickr
                        $flickrImageId = $pdo->do("SELECT media_id FROM media_files WHERE service_id = '{$photo['id']}'")->fetchColumn();
                        if (!empty($flickrImageId)) {
                            // There's already record for flickr
                            if ($flickrImageId == $localImageId) {
                                // that's OK! Nothing to do!
                                $pdo->do("UPDATE media_files SET revision = ?, status = ?, data = ?  WHERE media_id = ?", [$startTime, 1, $localImageId, $data]);

                                $pdo->do("UPDATE media SET media_type = ? WHERE media_id = ?", [$mediaType, $localImageId]);

                                echo "OK!\n";
                                continue;
                            } else {
                                // Files are the same but linked to different media_id
                                $pdo->do("UPDATE media_files SET media_id = ? WHERE media_id = ?", [$flickrImageId, $localImageId]);
                                $pdo->do("DELETE FROM media WHERE media_id = ?", [$localImageId]);
                                echo "Merged {$localImageId} with {$flickrImageId}\n";
                            }
                        } else {
                            // This image is not in database
                            Flickr::addImageFileToBaseFromFlickr($photo, $localImageId, $startTime);
                            echo "Added from Flickr\n";
                        }
                    }  else {
                        // Image with such description is not found in the database
                        Flickr::addImageFileToBaseFromFlickr($photo, null, $startTime);
                        echo "Added new file from Flickr\n";
                    }
                }  else {
                    // Image has short description. It was likely not loaded from main storage but from phone.
                    echo "Short description\n";
                    Flickr::addImageFileToBaseFromFlickr($photo, null, $startTime, 1);
                    echo "Added file that is only on Flickr\n";
                }*/
            } catch (ExpectedException $e) {
                echo("Problem processing photo: " . print_r($photo, 1) . "\n" . $e);
            }

        }


        $page++;
        $parameters['page'] = $page;
    } while ($photos['page'] < $photos['pages']);
    //Base::assignMediaFiles($startTime);
} catch (\Throwable $e) {
    echo $e;
}