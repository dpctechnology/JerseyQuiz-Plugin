<?php
// Register Custom Post Type Questions
$labels = array(
    'name' => _x('Quiz', 'Post Type General Name', 'textdomain'),
    'singular_name' => _x('Quiz', 'Post Type Singular Name', 'textdomain'),
    'menu_name' => _x('Quiz', 'Admin Menu text', 'textdomain'),
    'name_admin_bar' => _x('Quiz', 'Add New on Toolbar', 'textdomain'),
    'archives' => __('Quiz Archives', 'textdomain'),
    'attributes' => __('Quiz Attributes', 'textdomain'),
    'parent_item_colon' => __('Parent Quiz:', 'textdomain'),
    'all_items' => __('All Quiz', 'textdomain'),
    'add_new_item' => __('Add New Quiz', 'textdomain'),
    'add_new' => __('Add New', 'textdomain'),
    'new_item' => __('New Quiz', 'textdomain'),
    'edit_item' => __('Edit Quiz', 'textdomain'),
    'update_item' => __('Update Quiz', 'textdomain'),
    'view_item' => __('View Quiz', 'textdomain'),
    'view_items' => __('View Quiz', 'textdomain'),
    'search_items' => __('Search Quiz', 'textdomain'),
    'not_found' => __('Not found', 'textdomain'),
    'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
    'featured_image' => __('Featured Image', 'textdomain'),
    'set_featured_image' => __('Set featured image', 'textdomain'),
    'remove_featured_image' => __('Remove featured image', 'textdomain'),
    'use_featured_image' => __('Use as featured image', 'textdomain'),
    'insert_into_item' => __('Insert into Quiz', 'textdomain'),
    'uploaded_to_this_item' => __('Uploaded to this Quiz', 'textdomain'),
    'items_list' => __('Quiz list', 'textdomain'),
    'items_list_navigation' => __('Quiz list navigation', 'textdomain'),
    'filter_items_list' => __('Filter Quiz list', 'textdomain'),
);
$args = array(
    'label' => __('Quiz', 'textdomain'),
    'description' => __('', 'textdomain'),
    'labels' => $labels,
    'menu_icon' => 'dashicons-book',
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
register_post_type('qz-quiz', $args);

/**
 * Quiz Category functionality
 */
function add_category_to_quiz()
{
    add_meta_box(PLUGIN_PREFIX . '_quiz', __('Quiz Category', 'textdomain'), 'render_quiz_category_meta_box', PLUGIN_PREFIX . '-quiz', 'side', 'default');
}

function render_quiz_category_meta_box($post)
{
    $post_id = $post->ID;
    // Retrieve existing values for the custom fields
    $quizCategories = get_option(PLUGIN_PREFIX . "_quiz_categories");
    $quizCatRelation = get_option(PLUGIN_PREFIX . "_quiz_cat_relation");

    $count = 1;
    $template = '<div id="quiz-category">';
    if ($quizCatRelation) {
        foreach ($quizCategories as $key => $value) {

            $cat_quiz_id = (isset($quizCatRelation['category-' . $count]) ? $quizCatRelation['category-' . $count] : '');

            $template .= '<label for="category-' . $count . '" title="' . (get_the_title($cat_quiz_id)) . '">';
            if (!is_null(get_post($cat_quiz_id))) {
                if ($cat_quiz_id == $post_id) {
                    $template .= '<input type="radio" id="category-' . $count . '" name="quiz_category" value="category-' . $count . '" required checked>';
                } else if ($cat_quiz_id) {
                    $template .= '<input type="radio" id="category-' . $count . '" name="quiz_category" value="category-' . $count . '" required disabled>';
                } else {
                    $template .= '<input type="radio" id="category-' . $count . '" name="quiz_category" value="category-' . $count . '" required>';
                }
            } else {
                $template .= '<input type="radio" id="category-' . $count . '" name="quiz_category" value="category-' . $count . '" required>';
            }

            $template .= '<span>' . $value . '</span>';
            $template .= '</label>';
            $count++;
        }
    } else {
        foreach ($quizCategories as $key => $value) {
            $template .= '<label for="category-' . $count . '">';
            $template .= '<input type="radio" id="category-' . $count . '" name="quiz_category" value="category-' . $count . '" required>';
            $template .= '<span>' . $value . '</span>';
            $template .= '</label>';
            $count++;
        }
    }
    $template .= '</div>';
    echo $template;
}

function save_quiz_category_data($post_id)
{
    // Save custom field data when the post is saved
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['quiz_category']) && $_POST['quiz_category'] != '') {
        $quizCatRelation = get_option(PLUGIN_PREFIX . "_quiz_cat_relation");
        if ($quizCatRelation) {
            $quizCatRelation[sanitize_text_field($_POST['quiz_category'])] = $post_id;
            update_option(PLUGIN_PREFIX . "_quiz_cat_relation", $quizCatRelation);
            update_post_meta($post_id, 'quiz_category', sanitize_text_field($_POST['quiz_category']));
        } else {
            $quizCatRelation = [];
            $quizCatRelation[sanitize_text_field($_POST['quiz_category'])] = $post_id;
            add_option(PLUGIN_PREFIX . "_quiz_cat_relation", $quizCatRelation);
            update_post_meta($post_id, 'quiz_category', sanitize_text_field($_POST['quiz_category']));
        }
    }
}

add_action('admin_menu', 'add_category_to_quiz');
add_action('save_post', 'save_quiz_category_data');


/**
 * Quiz question functionality
 */

function add_questions_to_quiz()
{
    add_meta_box(PLUGIN_PREFIX . '_quiz_question', __('Questions', 'textdomain'), 'render_quiz_question_meta_box', PLUGIN_PREFIX . '-quiz', 'normal', 'default');
}

function render_quiz_question_meta_box($post)
{
    $post_id = $post->ID;
    $post_type = PLUGIN_PREFIX . '-questions';
    $saved_conditional_post_type = get_option(PLUGIN_PREFIX . '_conditional_post_type');
    $name_key = '[qs_1]';

    if ($saved_conditional_post_type == '')
        $saved_conditional_post_type = 'page';

    $question_args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    $question_loop = new WP_Query($question_args);
    $question_loop = $question_loop->posts;

    $pages_args = array(
        'post_type' => $saved_conditional_post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    $pages_loop = new WP_Query($pages_args);
    $pages_loop = $pages_loop->posts;

    $quiz_data = get_post_meta($post_id, 'quiz_data', true);

    if ($quiz_data) {

        if (!empty($question_loop)) {

            $single_ques_opt = [
                'true',
                'false'
            ];

            $conditions = [
                'qz-questions' => 'Question',
                $saved_conditional_post_type => 'Redirect'
            ];

            $key = '[qs_1][id]';
            $template = '<div id="quiz-questions">';
            $template .= '<div>';
            $question_values = questions_rending($question_loop, $post_type, $key, $quiz_data);
            $template .= $question_values['temp'];
            $new_question_list = $question_values['new_ques'];
            $template .= '<div class="conditions">';
            if (get_post_meta(getValueFromArray($quiz_data, $key), 'question_type', true) === 'single') {
                $cd_option = $single_ques_opt;
            } else {
                $multiple_ques_opt = get_post_meta(getValueFromArray($quiz_data, $key), 'multiple_options', true);
                $cd_option = $multiple_ques_opt;
            }
            foreach ($quiz_data['qs_1']['condition'] as $key => $question) {
                $index = str_replace('cd_', '', $key);
                $question_arr = array(
                    'cd_index' => '[' . $key . ']',
                    'option_name' => $cd_option[--$index],
                    'child_data' => $question
                );
                $template .= question_type_fn($single_ques_opt, $quiz_data, $question_arr, $conditions, $name_key, $pages_loop, $question_loop, $key, $saved_conditional_post_type);
            }
            $template .= '</div>';
            $template .= '</div>';
            $template .= '</div>';
            echo $template;
        }

        // echo "<pre>";
        // print_r($quiz_data);
        // echo "</pre>";

    } else {
        if (!empty($question_loop)) {
            $template = '<div id="quiz-questions">';
            $template = '<div>';
            $template .= '<select class="question-select" name="data[qs_1][id]" data-index="1" data-posttype="' . $post_type . '" required>';
            $template .= '<option value="">Select question...</option>';
            foreach ($question_loop as $key => $value) {
                $template .= '<option value="' . $value->ID . '">' . $value->post_title . '</option>';
            }
            $template .= '</select>';
            $template .= '</div>';
            $template .= '</div>';
            echo $template;
        }
    }
}

function questions_rending($question_loop, $post_type, $question_key, $quiz_data)
{
    $question_template = '<select class="question-select" name="data' . $question_key . '" data-index="1" data-posttype="' . $post_type . '" required>';
    $question_template .= '<option value="">Select question...</option>';
    foreach ($question_loop as $key => $value) {

        $question_template .= '<option value="' . $value->ID . '" ' . ((getValueFromArray($quiz_data, $question_key) == $value->ID) ? "selected" : "") . '>' . $value->post_title . '</option>';
    }
    $question_template .= '</select>';
    $index = search_question($question_loop, 'ID', getValueFromArray($quiz_data, $question_key));


    $new_question_list = array_splice($question_loop, $index, $index);

    return array(
        'temp' => $question_template,
        'new_ques' => $new_question_list
    );
}

function search_question($question_list, $field, $value)
{
    foreach ($question_list as $key => $question) {
        if ($question->$field == $value) {
            return $key;
        }
    }
    return false;
}
function question_type_fn($single_ques_opt, $quiz_data, $ques_opt, $conditions, $name_key, $pages_loop, $question_loop, $firstKey, $saved_conditional_post_type, $template = '')
{
    $CombinationOfKey = $name_key . '[condition]' . $ques_opt['cd_index'];
    $template .= '<div>';
    $template .= '<span>' . $ques_opt['option_name'] . '</span>';
    $template .= '<select class="conditions-select" data-name="data' . $CombinationOfKey . '" data-cd_index="" required>';

    $template .= '<option value="">Select condition...</option>';
    $upd_quiz_data = getValueFromArray($quiz_data, $CombinationOfKey);

    if (isset($upd_quiz_data['qs_id']) && $upd_quiz_data['qs_id'] != '') {
        $option_c_key = 'qz-questions';
    } else {
        $option_c_key = $saved_conditional_post_type;
    }
    foreach ($conditions as $c_key => $c_value) {
        if (count($question_loop) == 0 && $c_key == 'qz-questions') {
        } else {
            $template .= '<option value="' . $c_key . '"' . (($c_key == $option_c_key) ? "selected" : "") . '>' . $c_value . '</option>';
        }
    }

    $template .= '</select>';


    if (isset($upd_quiz_data['page_id']) && $upd_quiz_data['page_id'] != '') {
        $template .= '<div style="margin-left: 20px;">';
        $template .= '<select class="question-select" name="data' . $CombinationOfKey . '[page_id]" data-posttype="' . PLUGIN_PREFIX . '-questions">';
        $template .= '<option value="">Select page...</option>';
        foreach ($pages_loop as $page_key => $page_value) {
            $template .= '<option value="' . $page_value->ID . '" ' . (($page_value->ID == $upd_quiz_data['page_id']) ? "selected" : "") . '>' . $page_value->post_title . '</option>';
        }

        $template .= '</select>';
        $template .= '</div>';
    } else {
        $NewCombinationOfKey = $CombinationOfKey . '[qs_id]';
        $template .= '<div style="margin-left: 20px;">';
        $template .= '<select class="question-select" name="data' . $CombinationOfKey . '[qs_id]" data-posttype="' . PLUGIN_PREFIX . '-questions">';
        $template .= '<option value="">Select question...</option>';

        // Remove repeated questions from $question_loop (Remove first question)
        $question_loop = remove_repeated_question($question_loop, $quiz_data['qs_1']['id']);

        foreach ($question_loop as $ques_key => $ques_value) {
            if ($upd_quiz_data != NULL) {
                $template .= '<option value="' . $ques_value->ID . '" ' . (($ques_value->ID == $upd_quiz_data['qs_id']) ? "selected" : "") . '>' . $ques_value->post_title . '</option>';

                if (get_post_meta(getValueFromArray($quiz_data, $NewCombinationOfKey), 'question_type', true) === 'single') {
                    $cd_option = $single_ques_opt;
                } else {
                    $multiple_ques_opt = get_post_meta(getValueFromArray($quiz_data, $NewCombinationOfKey), 'multiple_options', true);
                    $cd_option = $multiple_ques_opt;
                }
            }
        }

        // Remove repeated questions from $question_loop (Remove nested question)
        $question_loop = remove_repeated_question($question_loop, $upd_quiz_data['qs_id']);

        $template .= '</select>';
        $template .= '<div class="conditions">';
        foreach ($upd_quiz_data['condition'] as $key => $question) {
            $index = str_replace('cd_', '', $key);
            $question_arr = array(
                'cd_index' => '[' . $key . ']',
                'option_name' => $cd_option[--$index],
                'child_data' => $question
            );

            $template .= question_type_fn($single_ques_opt, $quiz_data, $question_arr, $conditions, $CombinationOfKey, $pages_loop, $question_loop, $key, $saved_conditional_post_type);
        }
        $template .= '</div>';
        $template .= '</div>';
    }

    $template .= '</div>';
    $CombinationOfKey = '';
    return $template;
}

function getValueFromArray($array, $keys)
{
    $keys = [$keys];
    $value = '';
    foreach ($keys as $key) {
        // Remove square brackets and split the key into individual keys
        $key = trim($key, '[]');
        $keysArr = explode('][', $key);

        // Initialize value as the main array
        $value = $array;

        // Traverse through each key and get the value
        foreach ($keysArr as $subKey) {
            if (isset($value[$subKey])) {
                $value = $value[$subKey];
            } else {
                // If key is not found, set value to null and break the loop
                $value = null;
                break;
            }
        }
    }
    return $value;
}

function remove_repeated_question($question_loop, $id)
{
    $indexToRemove = null;
    // Find the index of the object with ID $id
    foreach ($question_loop as $qk => $qv) {
        if ($qv->ID == $id) {
            $indexToRemove = $qk;
            break;
        }
    }
    // Remove the element at the found index
    if ($indexToRemove !== null) {
        unset($question_loop[$indexToRemove]);
    }

    return $question_loop;
}

function save_quiz_question_data($post_id)
{
    // Save custom field data when the post is saved
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['data'])) {
        session_start();
        update_post_meta($post_id, 'quiz_data', $_POST['data']);
        update_post_meta($post_id, 'quiz_html', $_SESSION['html']);
        unset($_SESSION['html']);
    }
}

add_action('add_meta_boxes', 'add_questions_to_quiz');
add_action('save_post', 'save_quiz_question_data');
