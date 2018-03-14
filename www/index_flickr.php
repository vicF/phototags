<?php

use \DPZ\Flickr;

require_once '../common.php';

stream_context_set_default(
    [
        'http' => [
            'method' => 'HEAD'
        ]
    ]
);


$db = new SQLite3('db/phototags1.db');
if (!$db) {
    $error = (file_exists('../db/phototags1.db')) ? "Impossible to open, check permissions" : "Impossible to create, check permissions";
    die($error);
}
$db->enableExceptions (true);

set_time_limit(0);
$db->busyTimeout(10000);


$flickr = new Flickr($flickrApiKey, $flickrApiSecret);

$parameters = [
    'user_id'  => $flickrUser,
    'per_page' => 100,
    'extras'   => 'date_taken,o_dims,url_o,url_t,path_alias,original_format,last_update,geo,tags,machine_tags,o_dims,views,media',
    'sort'     => 'date-taken-asc',
    'page'     => 1
];


?>
<!DOCTYPE html>
<html>
<head>
    <title>DPZFlickr Example</title>
    <link rel="stylesheet" href="example.css"/>
</head>
<body>
<h1>Photos from Victor</h1>
<?php
try {
    $page = 1;
    do {
        $response = $flickr->call('flickr.photos.search', $parameters);
        $photos = $response['photos'];
        foreach ($photos['photo'] as $photo) {
            ?><a href="<?php echo sprintf("http://flickr.com/photos/%s/%s/", $photo['pathalias'], $photo['id']) ?>">
            <img src="<?php echo $photo['url_t'] ?>"/>
            </a><br/>
            Title:<?= $photo['title'] ?><br/>
            Date: <?= $photo['datetaken'] ?><br/>
            Format: <?= $photo['media'] ?> - <?= $photo['originalformat'] ?><br/>
            <?php
            $results = $db->query("SELECT image_id FROM image_files WHERE service_id = '{$photo['id']}'");
            if ($results !== false) {
                while ($row = $results->fetchArray()) {
                    ?><span style="color:darkgoldenrod">Already in database, id: <?=$row['image_id']?>!!!</span><br/><?php
                    continue 2;
                }
            }
            $headers = get_headers($photo['url_o'], 1);
            //print_r($photo);
            ?>Size: <?= $headers['Content-Length'] ?><br/><?php
            flush();
            $timestamp = strtotime($photo['datetaken']);

            $res = $db->exec("INSERT INTO images (`title`, `timestamp`) VALUES ('{$photo['title']}', $timestamp)");

            $image_id = $db->lastInsertRowid();
            $query = $db->exec("
            INSERT INTO image_files (image_id, server, path, filesize, width, height, service_id, thumb_url) 
            VALUES ({$image_id}, 1, '{$photo['url_o']}', '{$headers['Content-Length']}', '{$photo['width_o']}', '{$photo['height_o']}', '{$photo['id']}', '{$photo['url_t']}')");

            ?><span style="color:green">Added to database, id: <?= $image_id ?></span><br/><?php
        }


        $page++;
        $parameters['page'] = $page;
    } while (true);  // Add condition to stop !!!!!!
} catch(\Throwable $e) {
    print_r($e);
}
?>


// $response = $flickr->call('flickr.photos.getExif', ['photo_id' => $photo['id']]);

</body>
</html>

