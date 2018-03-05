<?php

namespace Fokin\PhotoTags\Tpl;

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
    public static function endHeader()
    {
        ?><?php
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
                                     src="Photo/<?= $src ?>"/><?php if ($comment) {
                            echo '<br />' .$title.'<br />' . $comment;} ?></td><?php

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

/**
 * @param $src
 * @param $title
 */
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
public static function end()
{
    self::endBody();
}
}

