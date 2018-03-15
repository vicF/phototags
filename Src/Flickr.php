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
    public function addToSet($photoId, $setName) {
        static $photosets;

        if(!isset($photosets)) {
            $flickr = Service::Flickr();
            $res = $flickr->call('flickr.photosets.getList');
            $photosets = [];
            foreach($res['photosets']['photoset'] as $set) {
                $photosets[$set['title']['_content']] = $set['id'];
            }
        }
        if(!array_key_exists($setName, $photosets)) {
            $this->createSet($setName, $photoId);
        } else {
            $parameters = ['photoset_id' => $photosets[$setName],
                           'photo_id' => $photoId];
            $this->call('flickr.photosets.addPhoto', $parameters);
        }

    }

    /**
     * @param $setName
     * @param $photoId
     */
    public function createSet($setName, $photoId) {
        $parameters = ['title' => $setName, 'primary_photo_id' => $photoId];
        $this->call('flickr.photosets.create', $parameters);
    }
}