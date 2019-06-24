<?php
/**
 * phototags
 * User: fokin
 * Created: 2019-06-20
 */

namespace Fokin\PhotoTags;

echo $out = '/Volumes/SQLMAZE_Projects/facts/data/all_files.xml';
require_once '../common.php';

$total = 0;
$previousFile = null;
$serverTypes = [1 => 'flickr', 2 => 'local', 3 => 'yandex'];
$writer = new \XMLWriter();
if (!$writer->openURI($out)) {
    echo "Failed to create {$out}\n";
    exit(1);
}
$writer->setIndent(true);
$writer->setIndentString(' ');
$writer->startDocument('1.0', 'UTF-8');
$writer->startElement('collection');

foreach (new Iterator\Database('SELECT *, LOWER(CONCAT(
    SUBSTR(HEX(uuid), 1, 8), \'-\',
    SUBSTR(HEX(uuid), 9, 4), \'-\',
    SUBSTR(HEX(uuid), 13, 4), \'-\',
    SUBSTR(HEX(uuid), 17, 4), \'-\',
    SUBSTR(HEX(uuid), 21)
)) as uuid1 FROM `media_files` LEFT JOIN media on media.media_id = media_files.media_id  
ORDER BY filesize, `media_files`.`media_id` ASC') as $file) {

    if (@$previousFile['media_id'] !== $file['media_id']) {
        if ($previousFile !== null) {
            // Closing media
            $writer->endElement();
        }
        $writer->startElement('media');
        $writer->writeAttribute('name', $file['filename']);
        $writer->writeAttribute('size', $file['filesize']);
        $writer->writeAttribute('type', $file['media_type'] ? 'photo' : 'video');
        $writer->writeAttribute('uuid', $file['uuid1']);
        $writer->writeAttribute('created', $file['created']);
    }
    $writer->startElement('file');
    $writer->writeAttribute('path', $file['path']);
    $writer->writeAttribute('storage', $serverTypes[$file['server_type']]);
    $writer->endElement();
    echo $file['path'] . "\n";
    $previousFile = $file;
    $total++;

}
$writer->endElement();
$writer->endElement();
$writer->endDocument();
$writer->flush();
echo "\nProcessed $total files\n";