<?php
// Get All post types as List
$get_all_post_types = get_post_types('', 'names');
// echo "<pre>";
// print_r($_SERVER['REQUEST_URI']);
// echo "</pre>";

if (isset($_POST) && isset($_POST['submit']) && $_POST['submit'] == 'save') {
    update_option(PLUGIN_PREFIX . '_conditional_post_type', $_POST['qz_post_types']);
}
?>
<div id="quiz-settings">
    <div>
        <h1>Quiz Settings</h1>
    </div>
    <div class="shortcode">
        <h2>Shortcode</h2>
        <p>[quizzes]</p>
    </div>
    <div>
        <h2>Select Post type for redirection</h2>
        <?php
        $saved_post_type = get_option(PLUGIN_PREFIX . '_conditional_post_type');
        $count = 1;
        echo '<form action="" method="post" class="post-type-list">';
        echo '<select name="qz_post_types" required>';
        echo '<option value="" disabled ' . ((isset($saved_post_type) && $saved_post_type == '') ? "selected" : "") . '>Select post type...</option>';
        foreach ($get_all_post_types as $key => $value) {
            $get_all_post_type_obj = get_post_type_object($value);
            echo '<option value="' . $key . '" ' . ((isset($saved_post_type) && $saved_post_type == $value) ? "selected" : "") . '>' . $get_all_post_type_obj->label . '</option>';
            $count++;
        }
        echo '</select>';
        echo '<div>';
        echo '<input type="submit" class="button button-primary button-large" name="submit" value="save"/>';
        echo '</div>';
        echo '</form>';
        ?>
    </div>
</div>