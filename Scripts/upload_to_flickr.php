<?php

namespace Fokin\PhotoTags;

use Fokin\PhotoTags\Iterator\Database;

require_once '../common.php';

$query = new Database('select i.image_id, i.title, o.path from images i left join image_files f on f.image_id = i.image_id and f.server = 1 left join image_files o on o.image_id = i.image_id where f.image_file_id is null');

foreach ($query as $row) {
    echo $row['path'] . "\n";
}