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
            $db->exec("
            UPDATE image_files SET path = '{$photo['url_o']}', width = '{$photo['width_o']}', height = '{$photo['height_o']}',  thumb_url = '{$photo['url_t']}')
             WHERE service_id = '{$photo['id']}'");
        }
    }

}