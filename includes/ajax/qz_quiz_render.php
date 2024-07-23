<?php

$return = [
    'data' => [],
    'msg' => 'Something went wrong!',
    'status' => false
];
// Quiz Render
if (isset($_POST['action']) && $_POST['action'] == PLUGIN_PREFIX . '_quiz_render') {

    $quiz_id = $_POST['quiz_id'];
    $quiz_data = get_post_meta($quiz_id, 'quiz_data', true);
    if ($quiz_data == '' || get_post_status($quiz_id) != 'publish') {
        $return['msg'] = 'No quiz available';
    } else {

        $question_id = $quiz_data['qs_1']['id'];
        $question_type = get_post_meta($question_id, 'question_type', true);
        $question_title = get_the_title($question_id);

        if ($question_type === 'single') {
            $single_options = ['true', 'false'];
            $return['data']['title'] = $question_title;

            $count = 1;
            foreach ($single_options as $key => $value) {

                $key = "['qs_1']['condition']['cd_" . ++$key . "']";
                $redirection_id = $quiz_data['qs_1']['condition']['cd_' . $count]['page_id'];

                if ($redirection_id != '') {
                    $return['data']['options'][$key] = [
                        'value' => $value,
                        'redirect' => get_the_guid($redirection_id),
                        'ques_id' => $question_id
                    ];
                } else {
                    $return['data']['options'][$key] = [
                        'value' => $value,
                        'redirect' => 'empty',
                        'ques_id' => $question_id
                    ];
                }

                $count++;
            }
        } else {
            $multiple_options = get_post_meta($question_id, 'multiple_options', true);
            $return['data']['title'] = $question_title;

            $count = 1;
            foreach ($multiple_options as $key => $value) {

                $key = "['qs_1']['condition']['cd_" . ++$key . "']";
                $redirection_id = $quiz_data['qs_1']['condition']['cd_' . $count]['page_id'];

                if ($redirection_id != '') {
                    $return['data']['options'][$key] = [
                        'value' => $value,
                        'redirect' => get_the_guid($redirection_id),
                        'ques_id' => $question_id
                    ];
                } else {
                    $return['data']['options'][$key] = [
                        'value' => $value,
                        'redirect' => 'empty',
                        'ques_id' => $question_id
                    ];
                }

                $count++;
            }
        }

        $return['msg'] = 'Quiz found';
        $return['status'] = true;
    }

}
print_r(json_encode($return));
exit;