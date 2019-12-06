<?php
include "config.php";
include "image.php";

function generateCategories()
{
    global $CONFIG;
    $cats_index = "var cat_index=[";
    $cats_name = "var cat_name=[";
    foreach ($CONFIG['CATEGORIES'] as $key => $cat) {
        $cats_index .= "'" . $key . "',";
        $cats_name .= "'" . $cat . "',";
    }
    echo rtrim($cats_index, ",") . '];';
    echo rtrim($cats_name, ",") . '];';
}

function generateCatTableRows()
{
    global $CONFIG;
    $table_rows = "";
    for ($i = 0; $i < count($CONFIG['CATEGORIES']); $i++) {
        $keys = array_keys($CONFIG['CATEGORIES']);
        $cat = $CONFIG['CATEGORIES'][$keys[$i]];
        $table_rows .= '<td id="cat_' . $i . '">' . $cat . '[' . ($i + 1) . ']</td>';
    }
    echo $table_rows;
}

$context = array();

/**
 * Generates the initial context for the index.php page
 */
function generateContext()
{
    global $context;
    global $CONFIG;

    $min = preg_split('/x/', $CONFIG['QUALITY_INDICATOR']['min']);
    $max = preg_split('/x/', $CONFIG['QUALITY_INDICATOR']['max']);
    $context['image_quality'] = array(
        'min_x' => $min[0],
        'min_y' => $min[1],
        'max_x' => $max[0],
        'max_y' => $max[1],
    );

    $next_image = getNextFile();
    $context['current_image'] = array(
        'name' => $next_image['img_name'],
        'url' => $next_image['img_url'],
        'is_copy' => ($next_image['is_copy']) ? 'true' : 'false',
        'img_x' => $next_image['img_x'],
        'img_y' => $next_image['img_y'],
        'file_count' => getFileCount($CONFIG['SOURCE_DIR'])
    );

}
