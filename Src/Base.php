<?php

namespace Fokin\PhotoTags;

/**
 * Class Base
 */
class Base
{
    const PHOTO = 0;
    const VIDEO = 1;

    const EXISTS = 1;
    const MISSING = 0;
    const FLICKR = 1;
    const LOCAL = 2;
    const YANDEX = 3;


    /**
     * @param $timestamp
     * @throws \Exception
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
            if (empty($photo['id'])) {
                print_r($photo);
                throw new \Exception('Empty photo data');
            }
            $smt = $db->prepare("
            UPDATE media_files SET path = ':url_o', width = ':width_o', height = ':height_o',  thumb_url = ':url_t'
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
     * @param $server
     * @param $path
     * @param $size
     * @param $width
     * @param $height
     * @param null $imageId
     * @param null $title
     * @param $timestamp
     * @param null $serviceId
     * @param null $thumbUrl
     * @param int $revision
     * @param int $status
     * @param int $media
     * @param null $data
     * @return int
     * @deprecated
     */
    public static function addImageFile($server, $path, $size, $width, $height, $imageId = null, $title = null, $timestamp, $serviceId = null, $thumbUrl = null, $revision = 0, $status = 1, $media = Base::PHOTO, $data = null)
    {
        if (is_null($data)) {
            $data = '';
        } else {
            $data = json_encode($data);
        }
        $db = Service::Database();
        if (is_null($imageId)) {
            $db->exec("INSERT INTO images (`title`, `timestamp`, media) VALUES ('{$title}', $timestamp, $media)");
            $imageId = $db->lastInsertRowid();
        }

        $db->exec("
    INSERT INTO media_files (media_id, server, path, filesize, width, height, service_id, thumb_url, revision, status, data)
    VALUES ({$imageId}, {$server}, '{$path}', '{$size}', '{$width}', '{$height}', '{$serviceId}', '{$thumbUrl}', {$revision}, {$status}, {$data})");
        return $db->lastInsertRowid();
    }

    /**
     * Adding new media file that may be not yet associated with image object
     *
     * @param $server
     * @param $path
     * @param $size
     * @param $width
     * @param $height
     * @param null $imageId
     * @param null $title
     * @param $timestamp
     * @param null $serviceId
     * @param null $thumbUrl
     * @param int $revision
     * @param int $status
     * @param int $media
     * @param null $data
     * @return int
     */
    public static function addMediaFile($server, $path, $size, $width, $height, $imageId = null, $title = null, $timestamp, $serviceId = null, $thumbUrl = null, $revision = 0, $status = 1, $media = Base::PHOTO, $data = null)
    {
        if (is_null($data)) {
            $data = '';
        } else {
            $data = json_encode($data);
        }
        $db = Service::PDO();

        $db->do("
    INSERT INTO media_files (media_id, server_type, path, filename, filesize, width, height, service_id, thumb_url, revision, status, media_type, created, data)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, ?)", [$imageId, $server, $path, $title, $size, $width, $height, $serviceId, $thumbUrl, $revision, $status, $media, $timestamp, $data]);
        return $db->lastInsertId();
    }

    /**
     * @param int $revision
     */
    public static function assignMediaFiles($revision = null)
    {
        $db = Service::PDO();
        $stmt = $db->do('SELECT `filename`, `created`, `media_type` FROM media_files WHERE media_id IS NULL');

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $db->do("INSERT INTO media (`filename`, `created`, `media_type`) VALUES (?, ?, ?)", [$row['filename'], $row['created'], $row['media_type']]);
        }


        $imageId = $db->lastInsertId();

    }

}