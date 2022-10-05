<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


#	---
#	Author: Gavin Vickery
#	Created: 2007/06/01
#	Last Update: 2007/06/05
#	---
// Generate base directory for source files

function snappy_basedir($typeDir) {
    $newCI = & get_instance();
    $baseDir = $newCI->config->slash_item('base_url');
    $sourceDir = 'assets/';
    return $baseDir . $sourceDir . $typeDir;
}


// Generate Image HTML
function snappy_image($fileName, $imgAlt = '', $imgClass = '', $add = '') {
    $fileDir = snappy_basedir('img/');
    $htmlStr = '';

    if (is_array($fileName)) {
        foreach ($fileName as $imgArr) {
            $imageFile = $fileDir . $imgArr[0];
            $htmlStr .= '<img ';
            if (!empty($imgArr[2])) {
                $htmlStr .= 'class="' . $imgArr[2] . '" ';
            }
            $htmlStr .= 'src="' . $imageFile . '" alt="';
            if (!empty($imgArr[1])) {
                $htmlStr .= $imgArr[1];
            }
            $htmlStr .= '" />' . "\n";
        }
    } else {
        $imageFile = $fileDir . $fileName;
        $htmlStr .= '<img ';
        if (!empty($imgClass)) {
            $htmlStr .= 'class="' . $imgClass . '" ';
        }
        $htmlStr .= 'src="' . $imageFile . '" alt="' . $imgAlt . '" '.$add.' />' . "\n";
    }

    return $htmlStr;
}

// Generate Stylesheet HTML
function snappy_style($fileName, $mediaType = 'screen') {
    $fileDir = snappy_basedir('css/');
    $htmlStr = '';

    if (is_array($fileName)) {
        foreach ($fileName as $styleArr) {
            $styleFile = $fileDir . $styleArr[0];
            $htmlStr .= '<link rel="stylesheet" type="text/css" href="' . $styleFile . '" media="';
            if (!empty($styleArr[1])) {
                $htmlStr .= $styleArr[1];
            } else {
                $htmlStr .= $mediaType;
            }
            $htmlStr .= '" />' . "\n";
        }
    } else {
        $styleFile = $fileDir . $fileName;
        $htmlStr = '<link rel="stylesheet" type="text/css" href="' . $styleFile . '" media="' . $mediaType . '" />' . "\n";
    }

    return $htmlStr;
}

// Generate Javascript HTML
function snappy_script($fileName) {
    $fileDir = snappy_basedir('js/');
    $htmlStr = '';

    if (is_array($fileName)) {
        foreach ($fileName as $jsFile) {
            $scriptFile = $fileDir . $jsFile;
            $htmlStr .= '<script type="text/javascript" src="' . $scriptFile . '"></script>' . "\n";
        }
    } else {
        $scriptFile = $fileDir . $fileName;
        $htmlStr = '<script type="text/javascript" src="' . $scriptFile . '"></script>' . "\n";
    }

    return $htmlStr;
}

?>
