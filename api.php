<?php
include "src/constants.php";
include "src/image.php";
include "src/config.php";

if (!empty($_GET)) {
    global $CONFIG;
    switch ($_GET['c']) {
        //Serve an image
        case 'get_image':
            if (array_key_exists('filename', $_GET)) {
                $sanitized_filename = htmlspecialchars($_GET['filename'], ENT_QUOTES);
                return serveImage($sanitized_filename);
            } else {
                retFailure(400, 'Parameter filename not specified');
            }
            break;

        //Upload an image
        case 'upload':
            //Check if category exist
            if (array_key_exists('cat', $_GET) && array_key_exists($_GET['cat'], $CONFIG['CATEGORIES'])) {
                $cat = $CONFIG['CATEGORIES'][$_GET['cat']];
                //Check if filename exist
                if (array_key_exists('filename', $_GET) && file_exists($CONFIG['SOURCE_DIR'] . $_GET['filename'])) {
                    $filename = $_GET['filename'];
                    if (!storeFile($_FILES, $cat, $filename)) {
                        retFailure(500, 'Could not store/move files. Is SOURCE_DIR and TARGET_DIR writeable?');
                    }
                } else {
                    retFailure(400, 'Invalid file or not specified');
                }
            } else {
                retFailure(400, 'Invalid category or not specified');
            }
            break;

        //Get next image info
        case 'img_info':
            getNextImageData();
            break;

        //Duplicate image on disk
        case 'duplicate':
            if (array_key_exists('filename', $_GET)) {
                duplicateImage(htmlspecialchars($_GET['filename']));
            } else {
                retFailure(400, 'No filename specified');
            }
    }
}
/**
 * @param int $status_code HTTP Status code
 * @param string $msg Error message which is returned as JSON array
 */
function retFailure(int $status_code, string $msg)
{
    header('Content-type:application/json;charset=utf-8');
    http_response_code($status_code);
    $data = array(
        'error' => $msg
    );
    echo json_encode($data);
}

function getNextImageData()
{
    $next_image = getNextFile();
    header('Content-type:application/json;charset=utf-8');
    echo json_encode($next_image);

}