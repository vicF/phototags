<?php


namespace Fokin\PhotoTags;

require_once '../common.php';
$df = new DuplicateFinder();
//$df->processDuplicates();
$df->assignMediaIdsToSingle();
