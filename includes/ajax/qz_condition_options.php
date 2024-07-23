<?php

$return = [
    'data' => [],
    'status' => false,
    'msg' => 'No data found.'
];

if (isset($_POST['action']) && $_POST['action'] === PLUGIN_PREFIX . '_condition_options') {
    $value = $_POST['value'];

    if ($value !== '') {
        $page_args = array(
            'post_type' => $value,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        $page_loop = new WP_Query($page_args);
        $page_loop = $page_loop->posts;
        if (!empty($page_loop)) {
            foreach ($page_loop as $key => $value) {
                $return['data'][] = [
                    'id' => $value->ID,
                    'title' => $value->post_title
                ];
            }
            $return['status'] = true;
            $return['msg'] = 'true';
        }
    }
}
print_r(json_encode($return));
exit;
