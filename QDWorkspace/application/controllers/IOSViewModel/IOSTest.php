<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/27
 * Time: 8:59 PM
 */


function delete_file_path($path, $del_dir) {
    $delete_paths = [];
    if (substr($path, strlen($path) - 1, 1) != DIRECTORY_SEPARATOR) {
        $path .= DIRECTORY_SEPARATOR;
    }
    $files = scandir($path);
    if ($files != -1) {
        foreach ($files as $key => $file_name) {
            if ($file_name == '.' || $file_name == '..') {
                continue;
            }

            $file_name = $path . $file_name;
            if (is_file($file_name)) {
                unlink($file_name);
            } else if (is_dir($file_name)) {
                $this->delete_file_path($file_name, true);
            }
        }

        if ($del_dir) {
            rmdir($path);
        }
    }

}

function zip_archive($archive_name, $archive_folder) {

    $zip = new ZipArchive;
    if ($zip -> open($archive_name, ZipArchive::CREATE) === TRUE)  {
        $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder."/");

        $dirs = array($dir);
        while (count($dirs)) {
            $dir = current($dirs);
            $dir_arr = explode(DIRECTORY_SEPARATOR, $dir);
            while ('' === ($dir_name = array_pop($dir_arr))) {

            }

            $dh = opendir($dir);
            while($file = readdir($dh)) {
                if ($file != '.' && $file != '..') {
                    if (is_file($dir . $file))
                        $zip -> addFile($dir . $file, $file);
                    elseif (is_dir($dir . $file))
                        $dirs[] = $dir . $file . "/";
                }
            }
            closedir($dh);
            array_shift($dirs);
        }

        $zip -> close();
        echo 'Archiving is sucessful!';
    } else {
        echo 'Error, can\'t create a zip file!';
    }
}


$path = __DIR__ . DIRECTORY_SEPARATOR . "../../../download/190107/";
$zip_path = __DIR__ . DIRECTORY_SEPARATOR . "../../../download/190107.zip";

zip_archive($zip_path, $path);
echo $dir;




//echo $str;