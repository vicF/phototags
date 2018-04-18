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
}