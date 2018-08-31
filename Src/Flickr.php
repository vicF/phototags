<?php
namespace Fokin\PhotoTags;

/**
 * Class Flickr
 *
 * @package Fokin\PhotoTags
 */
class Flickr extends \DPZ\Flickr
{

    /**
     * @param $photoId
     * @param $setName
     */
    public function addToSet($photoId, $setName)
    {
        static $photoSets;

        if (!isset($photoSets)) {
            // Getting full list of existing photosets
            $flickr = Service::Flickr();
            $res = $flickr->call('flickr.photosets.getList');
            $photoSets = [];
            foreach ($res['photosets']['photoset'] as $set) {
                $photoSets[$set['title']['_content']] = $set['id'];
            }
        }
        if (!array_key_exists($setName, $photoSets)) {
            $res = $this->createSet($setName, $photoId);
            if ($res['stat'] == 'ok') {
                // Adding new photoset to the local list
                echo "Created new PhotoSet {$setName}: " . $res['photoset']['id'] . "\n";
                $photoSets[$setName] = $res['photoset']['id'];
            }
        } else {
            $parameters = ['photoset_id' => $photoSets[$setName],
                           'photo_id'    => $photoId];
            $this->call('flickr.photosets.addPhoto', $parameters);
        }

    }

    /**
     * @param $setName
     * @param $photoId
     * @return mixed|null
     */
    public function createSet($setName, $photoId)
    {
        $parameters = ['title' => $setName, 'primary_photo_id' => $photoId];
        return $this->call('flickr.photosets.create', $parameters);
    }

    /**
     * @param $photo
     * @return array
     */
    public static function getAdditionalData($photo)
    {
        return ['secret' => $photo['secret']];
    }

    /**
     * @param $photo
     * @param int $imageId
     * @return int
     */
    public static function addImageFileToBaseFromFlickr($photo, $imageId = null, $revision = 0, $status = 1)
    {
        $headers = get_headers($photo['url_o'], 1);
        $timestamp = strtotime($photo['datetaken']);
        $media = self::getFlickrMediaType($photo);
        $data = Flickr::getAdditionalData($photo);

        return Base::addImageFile(1, $photo['url_o'], $headers['Content-Length'], $photo['width_o'], $photo['height_o'], $imageId, $photo['title'], $timestamp, $photo['id'], $photo['url_t'], $revision, $status, $media, $data);

    }

    /**
     * @param $photo
     * @return int
     * @throws \Exception
     */
    public static function getFlickrMediaType($photo)
    {
        switch ($photo['media']) {
            case 'photo':
                return Base::PHOTO;
            case 'video':
                return Base::VIDEO;
            default:
                throw new \Exception('Unknown media type: ' . $photo['media']);
        }
    }
}