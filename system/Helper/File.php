<?php
namespace Sys\Helper;

class File {

    public static function getExtension($file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return $ext;
    }

    public static function getType($file) {
        $info = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file);
        $info = NULL;
        return $mime;
    }

}