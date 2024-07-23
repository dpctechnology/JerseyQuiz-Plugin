<?php

// Register Custom Post Type Questions
$labels = array(
    'name' => _x('Questions', 'Post Type General Name', 'textdomain'),
    'singular_name' => _x('Questions', 'Post Type Singular Name', 'textdomain'),
    'menu_name' => _x('Questions', 'Admin Menu text', 'textdomain'),
    'name_admin_bar' => _x('Questions', 'Add New on Toolbar', 'textdomain'),
    'archives' => __('Questions Archives', 'textdomain'),
    'attributes' => __('Questions Attributes', 'textdomain'),
    'parent_item_colon' => __('Parent Questions:', 'textdomain'),
    'all_items' => __('All Questions', 'textdomain'),
    'add_new_item' => __('Add New Questions', 'textdomain'),
    'add_new' => __('Add New', 'textdomain'),
    'new_item' => __('New Questions', 'textdomain'),
    'edit_item' => __('Edit Questions', 'textdomain'),
    'update_item' => __('Update Questions', 'textdomain'),
    'view_item' => __('View Questions', 'textdomain'),
    'view_items' => __('View Questions', 'textdomain'),
    'search_items' => __('Search Questions', 'textdomain'),
    'not_found' => __('Not found', 'textdomain'),
    'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
    'featured_image' => __('Featured Image', 'textdomain'),
    'set_featured_image' => __('Set featured image', 'textdomain'),
    'remove_featured_image' => __('Remove featured image', 'textdomain'),
    'use_featured_image' => __('Use as featured image', 'textdomain'),
    'insert_into_item' => __('Insert into Questions', 'textdomain'),
    'uploaded_to_this_item' => __('Uploaded to this Questions', 'textdomain'),
    'items_list' => __('Questions list', 'textdomain'),
    'items_list_navigation' => __('Questions list navigation', 'textdomain'),
    'filter_items_list' => __('Filter Questions list', 'textdomain'),
);
$args = array(
    'label' => __('Questions', 'textdomain'),
    'description' => __('', 'textdomain'),
    'labels' => $labels,
    'menu_icon' => 'dashicons-clipboard',
    'supports' => array('title'),
    'taxonomies' => array(),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
);
register_post_type('qz-questions', $args);


// Questions functionality
function add_question_fields_to_questions()
{
    add_meta_box(PLUGIN_PREFIX . '_question_type', __('Question Type', 'textdomain'), 'render_question_type_meta_box', PLUGIN_PREFIX . '-questions', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_question_fields_to_questions');

function render_question_type_meta_box($post)
{
    $post_id = $post->ID;
    // Retrieve existing values for the custom fields
    $question_type = get_post_meta($post_id, 'question_type', true);
?>
    <div id="question-type">
        <label for="question_type_single">
            <span>True/False:</span>
            <input type="radio" id="question_type_single" name="question_type" value="single" <?php echo (isset($question_type) && $question_type == 'single') ? 'checked' : ''; ?> required />
        </label>
        <label for="question_type_multiple">
            <span>Multiple Option:</span>
            <input type="radio" id="question_type_multiple" name="question_type" value="multiple" <?php echo (isset($question_type) && $question_type == 'multiple') ? 'checked' : ''; ?> required />
        </label>

        <?php
        if ($question_type === 'multiple') {
            $multiple_options = get_post_meta($post_id, 'multiple_options', true);
            if (!empty($multiple_options)) {
                $template = "<div class='multiple-options'>";
                foreach ($multiple_options as $key => $value) {
                    $template .= "<label>";
                    $template .= "<input type='text' name='multiple_options[]' value='" . $value . "' required>";
                    if ($key > 1) {
                        $template .= "<a href='javascript:void(0)' class='remove-question'>";
                        $template .= "<i class='fa fa-minus'></i>";
                        $template .= "</a>";
                    }
                    $template .= "</label>";
                }
                if (count($multiple_options) < 6) {
                    $template .= "<div class='add-question'>";
                    $template .= "<a href='javascript:void(0)'>Add New";
                    $template .= "<i class='fa fa-plus'></i>";
                    $template .= "</a>";
                    $template .= "</div>";
                }
                $template .= "</div>"; // .\end
                echo $template;
            }
        }
        ?>
    </div>
<?php
}

function save_question_type_data($post_id)
{
    global $wpdb;
    // Save custom field data when the post is saved
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['question_type']) && $_POST['question_type'] != '') {
        update_post_meta($post_id, 'question_type', sanitize_text_field($_POST['question_type']));

        if ($_POST['question_type'] === 'multiple') {
            update_post_meta($post_id, 'multiple_options', $_POST['multiple_options']);
        }

        /* $existing_question = $wpdb->get_row("SELECT question_id FROM qz_question WHERE question_id = $post_id");
        $existing_options = $wpdb->get_var("SELECT options FROM qz_question WHERE question_id = $post_id");
        $single_options = [
            'true',
            'false'
        ];
        if ($existing_question === null) {
            if ($_POST['question_type'] === 'single') {
                foreach ($single_options as $value) {
                    $wpdb->insert('qz_question', array(
                        'question_id' => $post_id,
                        'options' => $value
                    ));
                }
            } else if ($_POST['question_type'] === 'multiple') {
                foreach ($_POST['multiple_options'] as $value) {
                    $wpdb->insert('qz_question', array(
                        'question_id' => $post_id,
                        'options' => $value
                    ));
                }
            }
        } else {
            if ($_POST['question_type'] === 'single') {
                $existing_options_array = explode(',', $existing_options);

                if (count($single_options) > count($existing_options_array)) {
                    // Add new options
                    $options_to_add = array_diff($single_options, $existing_options_array);
                    $updated_options_array = array_merge($existing_options_array, $options_to_add);
                    $updated_options = implode(',', $updated_options_array);

                    $wpdb->update('qz_question', array('options' => $updated_options), array('question_id' => $post_id));
                } else {
                    // Remove excess options
                    $options_to_remove = array_diff($existing_options_array, $single_options);
                    $updated_options_array = array_diff($existing_options_array, $options_to_remove);
                    $updated_options = implode(',', $updated_options_array);

                    $wpdb->update('qz_question', array('options' => $updated_options), array('question_id' => $post_id));
                }
            } else if ($_POST['question_type'] === 'multiple') {
                $existing_options_array = explode(',', $existing_options);

                if (count($_POST['multiple_options']) > count($existing_options_array)) {
                    // Add new options
                    $options_to_add = array_diff($_POST['multiple_options'], $existing_options_array);
                    $updated_options_array = array_merge($existing_options_array, $options_to_add);
                    $updated_options = implode(',', $updated_options_array);

                    $wpdb->update('qz_question', array('options' => $updated_options), array('question_id' => $post_id));
                } else {
                    // Remove excess options
                    $options_to_remove = array_diff($existing_options_array, $_POST['multiple_options']);
                    $updated_options_array = array_diff($existing_options_array, $options_to_remove);
                    $updated_options = implode(',', $updated_options_array);

                    $wpdb->update('qz_question', array('options' => $updated_options), array('question_id' => $post_id));
                }
            }
        } */
    }
}
add_action('save_post', 'save_question_type_data');
