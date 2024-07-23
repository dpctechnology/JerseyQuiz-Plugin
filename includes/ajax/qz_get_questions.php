<?php

$return = [
    'data' => [],
    'status' => false,
    'msg' => 'No data found.'
];
if (isset($_POST['action']) && $_POST['action'] === PLUGIN_PREFIX . '_get_questions') {
    $posttype = (isset($_POST['posttype']) && $_POST['posttype'] != '') ? $_POST['posttype'] : '';
    $question_id = (isset($_POST['question_id']) && $_POST['question_id'] != '') ? $_POST['question_id'] : '';

    if ($posttype != '' && $question_id != '') {
        $question_type = get_post_meta($question_id, 'question_type', true);
        $saved_post_type = get_option(PLUGIN_PREFIX . '_conditional_post_type');
        /* $question_args = array(
            'post_type' => PLUGIN_PREFIX . '-questions',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        $page_args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        $question_loop = new WP_Query($question_args);
        $question_loop = $question_loop->posts;

        $page_loop = new WP_Query($page_args);
        $page_loop = $page_loop->posts;

        if (!empty($question_loop)) {
            foreach ($question_loop as $key => $value) {
                $return['condition'][] = [
                    'id' => $value->ID,
                    'title' => $value->post_title,
                    'type' => 'Question'
                ];
            }
        }

        if (!empty($page_loop)) {
            foreach ($page_loop as $key => $value) {
                $return['condition'][] = [
                    'id' => $value->ID,
                    'title' => $value->post_title,
                    'type' => 'Page'
                ];
            }
        } */

        $return['condition'] = [
            [
                'title' => 'Question',
                'type' => PLUGIN_PREFIX . '-questions'
            ],
            [
                'title' => 'Redirect',
                'type' => ((isset($saved_post_type) && $saved_post_type != '') ? $saved_post_type : 'page')
            ]
        ];

        if ($question_type === 'single') {
            $return['data'][] = 'true';
            $return['data'][] = 'false';
            $return['status'] = true;
            $return['msg'] = 'Questions data';
        } else if ($question_type === 'multiple') {
            $multiple_options = get_post_meta($question_id, 'multiple_options', true);
            foreach ($multiple_options as $k => $v) {
                $return['data'][] = $v;
            }
            $return['status'] = true;
            $return['msg'] = 'Conditions data';
        }
    }
}

print_r(json_encode($return));
exit;
