<?php

namespace Fokin\PhotoTags;

/**
 * Class Base
 */
class Base
{
    /**
     * @param $timestamp
     */
    public static function fillFlickrDataForRecentUploads($timestamp)
    {
        $Flickr = Service::Flickr();
        $db = Service::Database();
        $response = $Flickr->call('flickr.photos.recentlyUpdated', [
            'min_date' => $timestamp,
            'extras'   => 'date_taken,o_dims,url_o,url_t,path_alias,original_format,last_update,geo,tags,machine_tags,o_dims,views,media',
        ]);

        $photos = $response['photos'];
        foreach ($photos['photo'] as $photo) {
            if(empty($photo['id'])) {
                print_r($photo);
                throw new \Exception('Empty photo data');
            }
            $smt = $db->prepare("
            UPDATE image_files SET path = ':url_o', width = ':width_o', height = ':height_o',  thumb_url = ':url_t'
             WHERE service_id = ':id'");
            $smt->bindValue(':url_o', $photo['url_o'], SQLITE3_TEXT);
            $smt->bindValue(':width_o', $photo['width_o'], SQLITE3_NUM);
            $smt->bindValue(':height_o', $photo['height_o'], SQLITE3_NUM);
            $smt->bindValue(':url_t', $photo['url_t'], SQLITE3_TEXT);
            $smt->bindValue(':id', $photo['id'], SQLITE3_NUM);
            $smt->execute();
        }
    }

    /**
     * @param $photo
     * @param int $imageId
     * @return int
     */
    public static function addImageFileToBaseFromFlickr($photo, $imageId = null) {
        $headers = get_headers($photo['url_o'], 1);
        $timestamp = strtotime($photo['datetaken']);

        return self::addImageFile(1, $photo['url_o'],$headers['Content-Length'], $photo['width_o'], $photo['height_o'],$imageId, $photo['title'], $timestamp, $photo['id'], $photo['url_t']);

        //$res = $db->exec("INSERT INTO images (`title`, `timestamp`) VALUES ('{$photo['title']}', $timestamp)");

        //$image_id = $db->lastInsertRowid();
        /*$query = $db->exec("
    INSERT INTO image_files (image_id, server, path, filesize, width, height, service_id, thumb_url)
    VALUES ({$image_id}, 1, '{$photo['url_o']}', '{$headers['Content-Length']}', '{$photo['width_o']}', '{$photo['height_o']}', '{$photo['id']}', '{$photo['url_t']}')");*/
    }

    /**
     * @param $server
     * @param $path
     * @param $width
     * @param $height
     * @param null $imageId
     * @param null $serviceId
     * @param null $thumbUrl
     * @return int
     */
    public static function addImageFile($server, $path, $size, $width, $height, $imageId = null, $title = null, $timestamp,  $serviceId = null, $thumbUrl = null) {
        $db = Service::Database();
        if(is_null($imageId)) {
            $db->exec("INSERT INTO images (`title`, `timestamp`) VALUES ('{$title}', $timestamp)");
            $imageId = $db->lastInsertRowid();
        }

        $db->exec("
    INSERT INTO image_files (image_id, server, path, filesize, width, height, service_id, thumb_url)
    VALUES ({$imageId}, {$server}, '{$path}', '{$size}', '{$width}', '{$height}', '{$serviceId}', '{$thumbUrl}')");
        return $db->lastInsertRowid();
    }

}