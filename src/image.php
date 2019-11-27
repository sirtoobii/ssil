<?php
include "constants.php";
include("config.php");

/**
 * Serves a file
 * @param $file string to server
 * @return false|int 404 if not found
 */
function serveImage(string $file)
{
    global $CONFIG;
    $file_name = $CONFIG['SOURCE_DIR'] . $file;
    if (!file_exists($file_name)) {
        header("HTTP/1.1 404 Not Found");
    } else {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($file_name)) {
            header('HTTP/1.0 304 Not Modified');
            exit;
        }
        header('Content-Type:' . mime_content_type($file_name));
        header('Content-Length: ' . filesize($file_name));
        header('Last-Modified: ' . date(DATE_RFC2822, filemtime($file_name)));
        header('Content-Disposition: inline; filename=' . $file);
        return readfile($file_name);
    }
}

/**
 * Duplicated a file in the `SOURCE_DIR`
 * @param $file string file to duplicate
 */
function duplicateImage(string $file)
{
    global $CONFIG;
    $file_name = $CONFIG['SOURCE_DIR'] . $file;
    $rand_nr = mt_rand(100, 999);
    if (!file_exists($file_name)) {
        header("HTTP/1.1 404 Not Found");
    } else {
        if (copy($file_name, $CONFIG['SOURCE_DIR'] . 'copy_' . $rand_nr . '_' . $file)) {
            header("HTTP/1.1 204 NO CONTENT");
        } else {
            header("HTTP/1.1 404 Not Found");
        }
    }
}

/**
 * Returns the filename of the next file
 * @return array
 */
function getNextFile()
{
    global $CONFIG;
    // Choose the first file in SOURCE_DIR
    if ($h = opendir($CONFIG['SOURCE_DIR'])) {
        while (($file = readdir($h)) !== false) {
            if ($file != '.' && $file != '..') {
                break;
            }
        }
        closedir($h);
    }
    //Check if we really have an image and extract meta info
    if (!empty($file) && $img_info = getimagesize($CONFIG['SOURCE_DIR'] . $file)) {
        return array(
            'img_name' => $file,
            'img_y' => $img_info[0],
            'img_x' => $img_info[1],
            'mime' => $img_info['mime'],
            'error' => 'none',
            'is_copy' => (strpos($file, 'copy') !== false) ? true : false,
            'img_url' => $CONFIG['PROTO'] . $_SERVER['HTTP_HOST'] . '/api.php?c=get_image&filename=' . $file
        );
    } else {
        return array(
            'img_name' => '',
            'img_y' => 0,
            'img_x' => 0,
            'mime' => 0,
            'error' => 'Either not an image file or no files left filename: ' . $file,
            'is_copy' => false,
            'img_url' => 'none'
        );
    }
}

/**
 * Stores a labeled and cropped file to the server
 * @param array $post_data POST data
 * @param string $cat category
 * @param string $filename filename
 * @return bool True if stored successfully, false if something went wrong
 */
function storeFile(array $post_data, string $cat, string $filename)
{
    global $CONFIG;
    //Prepare target dirs
    prepareDirs();
    //First move original
    if (copy($CONFIG['SOURCE_DIR'] . $filename, $CONFIG['TARGET_DIR'] . $cat . '/orig/' . $filename)) {
        unlink($CONFIG['SOURCE_DIR'] . $filename);
    } else {
        return false;
    }
    //Get dest dir
    if (!empty($post_data)) {
        //Change file extension to PNG
        $filename = pathinfo($filename, PATHINFO_FILENAME) . '.PNG';
        $dest_file = $CONFIG['TARGET_DIR'] . $cat . '/crop/crop_' . $filename;
        if (move_uploaded_file($post_data['user_file']['tmp_name'], $dest_file)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * Prepares folder structure in `TARGET_DIR`
 */
function prepareDirs()
{
    global $CONFIG;
    foreach ($CONFIG['CATEGORIES'] as $cat) {
        if (!is_dir($CONFIG['TARGET_DIR'] . $cat)) {
            mkdir($CONFIG['TARGET_DIR'] . $cat, 0777, true);
            mkdir($CONFIG['TARGET_DIR'] . $cat . '/orig', 0777, true);
            mkdir($CONFIG['TARGET_DIR'] . $cat . '/crop', 0777, true);
        }
    }
}