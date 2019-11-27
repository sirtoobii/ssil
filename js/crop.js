window.addEventListener('DOMContentLoaded', function () {
    var image = document.querySelector('#image');
    let crop_res = document.getElementById('crop_res');
    var options = {
        movable: false,
        zoomable: false,
        rotatable: true,
        autoCrop: false,
        scalable: false,
        crop: function (e) {
            current_image.changed = true;
            let data = e.detail;
            let height = Math.round(data.height);
            let width = Math.round(data.width);
            crop_res.innerHTML = height + 'x' + width;
        }

    };
    var cropper = new Cropper(image,options);
    populateImgInfo();
    // Buttons
    document.getElementById('save').onclick = function () {
        saveImage();
    };
    document.getElementById('reset').onclick = function () {
        resetView();
    };
    document.getElementById('rotate_r').onclick = function () {
        cropper.rotate(1);
    };
    document.getElementById('rotate_l').onclick = function () {
        cropper.rotate(-1);
    };
    document.getElementById('duplicate').onclick = function () {
        duplicateImage(current_image.name);
    };

//Keyboard shortcut
    document.onkeypress = function (e) {
        switch (e.key) {
            case 'a':
                cropper.rotate(2);
                break;
            case 'd':
                cropper.rotate(-2);
                break;
            case 'q':
                resetView();
                break;
            case 's':
                saveImage();
                break;
            case 'x':
                duplicateImage(current_image.name);
                break;
            case '1':
                set_cat(1);
                break;
            case '2':
                set_cat(2);
                break;
            case '3':
                set_cat(3);
                break;
            case '4':
                set_cat(4);
                break;
            case '5':
                set_cat(5);
        }
    };

    /**
     * Resets current cropping and category
     */
    function resetView() {
        cropper.reset();
        clearCatTable();
        current_image.category = 'none';
        current_image.changed = false;
    }

    /**
     * Uploads to cropped image to the server
     */
    function saveImage() {
        if (current_image.category === 'none') {
            showMessage('error', 'Please choose a category first');
            return;
        }
        //Skip create blob when unchanged
        if (!current_image.changed) {
            $.ajax('api.php?c=upload&cat=' + current_image.category + '&filename=' + current_image.name + '', {
                method: "POST",
                processData: false,
                contentType: false,
                success: function () {
                    showMessage('success', 'Upload successful');
                    clearCatTable();
                    retrieve_image();
                },
                error: function (data){
                    console.log(data);
                    showMessage('error', data.responseJSON.error);
                }
            });
            return;
        }
        var imgurl = cropper.getCroppedCanvas().toDataURL();
        var img = document.createElement("img");
        img.src = imgurl;
        cropper.getCroppedCanvas().toBlob(function (blob) {
            var formData = new FormData();
            formData.append('user_file', blob);
            // Use `jQuery.ajax` method
            $.ajax('api.php?c=upload&cat=' + current_image.category + '&filename=' + current_image.name + '', {
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    showMessage('success', 'Upload successful');
                    clearCatTable();
                    retrieve_image();
                },
                error: function (data) {
                    showMessage('error', data.responseJSON.error)
                }
            });
        });
    }

    /**
     * Set the category of the current picture
     * @param cat
     */
    function set_cat(cat) {
        console.log('Set category to: ' + cat_name[cat - 1]);
        clearCatTable();
        document.getElementById('cat_' + (cat - 1)).style.backgroundColor = 'green';
        current_image.category = cat_index[cat - 1];
    }

    /**
     * Get the data for the next image
     */
    function retrieve_image() {
        current_image.clear();
        $.ajax({
            url: 'api.php?c=img_info',
            dataType: "json",
            success: function (data) {
                current_image.name = data.img_name;
                current_image.url = data.img_url;
                current_image.img_x = data.img_x;
                current_image.img_y = data.img_y;
                current_image.is_copy = data.is_copy;
                current_image.changed = false;
                cropper.replace(current_image.url);
                populateImgInfo();
            },
            error: function (data) {
                showMessage('error', data.responseJSON.error)
            }
        });
    }

    /**
     * Clear category table
     */
    function clearCatTable() {
        var rows = document.getElementById("catTable").getElementsByTagName("td");
        for (i = 0; i < rows.length; i++) {
            rows[i].removeAttribute('style');
        }
    }

    function populateImgInfo() {
        set_title(current_image.name);
        set_resolution(current_image.img_x, current_image.img_y);
        set_copy(current_image.is_copy);
    }
});

/**
 * Duplicates image on the server
 * @param $filename Filename to duplicate
 */
function duplicateImage($filename) {
    $.ajax({
        url: 'api.php?c=duplicate&filename=' + $filename,
        success: function (data) {
            showMessage('success', 'Successfully duplicated');
        },
        error: function (data) {
            showMessage('error', data.responseJSON.error)
        }
    });
}

/**
 * Simply sets the title
 */
function set_title($title) {
    document.getElementById("title_img").innerHTML = $title;
}


function set_resolution($x, $y) {
    let cell = document.getElementById('img_res');
    cell.innerText = $x + 'x' + $y;
    if ($x < image_quality.min_x || $y < image_quality.min_y) {
        cell.style.backgroundColor = 'red';
        return;
    }
    let rel_val = Math.min($x / image_quality.max_x, $y / image_quality.max_y);
    if (rel_val > 1.0) {
        cell.style.backgroundColor = 'green';
    } else {
        cell.style.backgroundColor = perc2color(rel_val*100);
    }

}

function set_copy($copy) {
    let cell = document.getElementById('img_copy');
    cell.innerHTML = $copy;
    if ($copy){
        cell.style.backgroundColor = 'red';
    } else {
        cell.style.backgroundColor = 'green';
    }
}

/**
 * Shows a toast message
 * @param $type error or success
 * @param $msg Message to display
 */
function showMessage($type, $msg) {
    // Get the snackbar DIV
    let x = document.getElementById("snackbar");
    // Set messgage
    x.innerHTML = $msg;
    switch ($type) {
        case 'error':
            x.style.backgroundColor = 'red';
            break;
        case 'success':
            x.style.backgroundColor = 'green';
    }

    // Add the "show" class to DIV
    x.className = "show";

    setTimeout(function () {
        x.className = x.className.replace("show", "");
    }, 3000);
}

/**
 * https://gist.github.com/mlocati/7210513
 * @param perc 0-100
 * @returns {string} html color string
 */
function perc2color(perc) {
    var r, g, b = 0;
    if (perc < 50) {
        r = 255;
        g = Math.round(5.1 * perc);
    } else {
        g = 255;
        r = Math.round(510 - 5.10 * perc);
    }
    var h = r * 0x10000 + g * 0x100 + b * 0x1;
    return '#' + ('000000' + h.toString(16)).slice(-6);
}