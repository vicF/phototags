<?PHP

namespace Fokin\PhotoTags\Tpl {use Fokin\PhotoTags\Iterator\Database;

/**
 * Class Tpl
 *
 * @package Fokin\PhotoTags\Tpl
 */
class Tpl
{
const COLUMN_WIDTH = 5;
protected static $_leftContent;
protected static $_rightContent;
protected static $_startedMain = false;
protected static $_startedImages = false;
protected static $_imageColumn = 1;


public static function startBody()
{
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
        body table {
            width: 100%;
        }

        #left {
            width: 200px;
        }

        #right {
            width: 300px;
        }
    </style>
</head>
<body><?php
}

public static function startHeader()
{
?>
<div class="header"><?php
    }

    public static function header($total, $request, $limit = 1000)
    {
        $pages = floor($total / $limit) - 1;
        if ($total % $limit > 0) {
            $pages++;
        }
        $sources = [2 => 'local', 1 => 'Flickr'];
        $sourcesChecked = array_flip($request['source']);
        ?>
        <form id="selectors" method="post">
        <button type="submit" name="page" form="selectors" value="prev">&lt;&lt;</button><?php
        for ($i = 0; $i <= $pages; $i++) { ?>
            <button type="submit"
            <?php if ($request['page'] == $i) { ?>style="background-color:blue"<?php } ?> name="page" form="selectors"
                    value="<?= $i ?>"><?= $i + 1 ?></button><?php
        } ?>
        <button type="submit" form="selectors" name="page" value="next">&gt;&gt;</button>
        Sources: <?php foreach ($sources as $sourceId => $name) { ?><label><?= $name ?></label><input type="checkbox"
                                                                                                      name="source[]"
                                                                                                      value="<?= $sourceId ?>" <?php if (isset($sourcesChecked[$sourceId])) {
                echo 'checked';
            } ?> /><?php }
        $sources = [Database::NONE   => 'None',
                    Database::SIZE   => 'Size',
                    Database::TIME   => 'Time',
                    Database::SOURCE => 'Source',
                    Database::NAME   => 'Name'];
        ?>
        <select name="sort"><?php foreach ($sources as $value => $option) { ?>
                <option value="<?= $value ?>" <?php if ($value == $request['sort']) {
                    echo "selected";
                } ?> ><?= $option ?></option>
            <?php } ?>
        </select><input type="radio" name="sortd" value="desc" checked>↓<input type="radio" name="sortd"
                                                                               value="asc">↑
        </form><?php
    }

    public static function endHeader()
    {
    ?></div><?php
}

public static function startLeft()
{
?>
<table>
    <tr>
        <td id="left"><?php
            }

            public static function endLeft()
            {
            ?></td><?php
        }
        public static function startMain()
        {
        ?>
        <td id="main"><?php
            }

            public static function startImages(){
            ?>
            <table>
                <?php
                self::$_startedImages = true;
                }

                public static function startImageRow() {
                ?>
                <tr><?php
                    }

                    public static function image($src, $title, $comment)
                    { ?>
                        <td><img title="<?= $title ?>" width="150"
                                 src="<?= $src ?>"/><?php if ($comment) {
                        echo '<br />' . $title . '<br />' . $comment;
                    } ?></td><?php

                    }

                    public static function endImageRow() {
                    ?></tr><?php
            }

            public static function endImages()
            {
            ?>
            </table><?php
        }

        public static function endMain()
        {
        ?></td><?php
    }


    public static function startRight()
    {
    ?>
        <td id="right"><?php
            }

            public static function endRight()
            {
            ?></td>
    </tr>
</table><?php
}





public static function startFooter()
{
?>
<div id="footer"><?php
    }

    public static function endFooter()
    {
    ?></div><?php
}

public static function endBody()
{
?>
</body>
</html><?php
}

public static function setLeft($content)
{
    self::$_leftContent = $content;
}

public static function setRight($content)
{
    self::$_rightContent = $content;
}

public static function start()
{
    self::startBody();
    self::startHeader();
    self::endHeader();
    self::startLeft();
    echo self::$_leftContent;
    self::endLeft();
    self::startMain();
    self::$_startedMain = true;
}


public static function showImage($src, $title, $comment = null)
{
    if (!self::$_startedMain) {
        self::start();
    }
    if (!self::$_startedImages) {
        self::startImages();
    }
    if (self::$_imageColumn == 1) {
        self::startImageRow();
    }
    self::image($src, $title, $comment);
    if (self::$_imageColumn == self::COLUMN_WIDTH) {
        self::endImageRow();
        self::$_imageColumn = 1;
        return;
    }
    self::$_imageColumn++;

}



public static function nextImageRow()
{
    while (self::$_imageColumn < self::COLUMN_WIDTH) {
        ?>
        <td></td><?php
        self::$_imageColumn++;
    }
    self::endImageRow();
    self::$_imageColumn = 1;

}


public static function showAnyImage($image)
{
    if (!$image) {
        return;
    }
    switch ($image['server']) {
        case 1:
            $url = $image['path'];
            $comment = "<a href='{$image['path']}' target='_blank'>flickr</a>";
            break;
        case 2:
            $url = 'http://localhost:8001/' . substr($image['path'], 18);
            $comment = "<a href='file:///{$image['path']}' target='_blank'>local</a>";
            break;
        default:
            throw new \Exception('Unknown server type: ' . $image['server']);
    }
    self::showImage($url, $image['title'], $comment);
}


public static function end()
{
    self::endBody();
}
}
}