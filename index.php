<!DOCTYPE html>
<head>
    <script src="vendor/jQuery/jquery-3.4.1.min.js"></script>
    <link href="vendor/cropperjs-1.5.6/cropper.min.css" rel="stylesheet">
    <script src="vendor/cropperjs-1.5.6/cropper.min.js"></script>
    <script src="js/crop.js"></script>
    <link href="css/main.css" rel="stylesheet">
    <script>
        <?php
        include "src/js_generator.php";
        generateContext();
        generateCategories();
        ?>
        let current_image = {
            'name': '<?php echo $context['current_image']['name']?>',
            'url': '<?php echo $context['current_image']['url']?>',
            'is_copy': <?php echo $context['current_image']['is_copy']?>,
            'img_x': <?php echo $context['current_image']['img_x']?>,
            'img_y': <?php echo $context['current_image']['img_y']?>,
            'category': 'none',
            'changed': false,
            'clear': function () {
                this.name = 'none';
                this.category = 'none';
                this.copy = false;
                this.changed = false;
                this.url = 'none';
                this.img_x = 0;
                this.img_y = 0;
            }
        };
        let image_quality = {
            'min_x':<?php echo $context['image_quality']['min_x']?>,
            'min_y':<?php echo $context['image_quality']['min_y']?>,
            'max_x':<?php echo $context['image_quality']['max_x']?>,
            'max_y':<?php echo $context['image_quality']['max_y']?>,
        }
    </script>
    <title>Super Simple Image Labeler | SSIL</title>
</head>
<body>


<div class="main_container">
    <div class="container_right">
        <table>
            <tr>
                <th>Attribute</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Name</td>
                <td id="title_img"></td>
            </tr>
            <tr>
                <td>Resolution</td>
                <td id="img_res"></td>
            </tr>
            <tr>
                <td>Copy</td>
                <td id="img_copy"></td>
            </tr>
            <tr>
                <td>Crop resolution</td>
                <td id="crop_res"></td>
            </tr>
        </table>
        <p>
            <button type="button" class="action_btn" id="save">Save [s]</button>
        </p>
        <p>
            <button type="button" class="action_btn" id="rotate_r">Rotate right [a]</button>
        </p>
        <p>
            <button type="button" class="action_btn" id="rotate_l">Rotate left [d]</button>
        </p>
        <button type="button" class="action_btn" id="reset">Reset [q]</button>
        <p>
            <button type="button" class="action_btn" id="duplicate">Duplicate [x]</button>
        </p>
    </div>
    <div class="container_left">
        <table style="width:100%" id="catTable">
            <tr>
                <?php
                generateCatTableRows();
                ?>
            </tr>
        </table>
        <div>
            <img id="image" src="<?php echo $context['current_image']['url'] ?>" alt="No picture left">
        </div>
        <div id="cropped_result"></div>
    </div>
    <div id="snackbar">Some text some message..</div>
</div>
</body>
</html>
