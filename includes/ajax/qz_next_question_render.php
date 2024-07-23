<?php
// Next Question Render
$return = [
    'data' => [],
    'msg' => 'Something went wrong!',
    'status' => false
];

if (isset($_POST['action']) && $_POST['action'] == PLUGIN_PREFIX . '_next_question') {
    $quiz_id = $_POST['quiz_id'];
    $key = str_replace("\\", "", $_POST['key']);
    $quiz_data = get_post_meta($quiz_id, 'quiz_data', true);
    // $upd_quiz_data = getValueFromArray($quiz_data, $key);

    // Update array key start
    $keys = explode("']['", trim($key, "['']"));
    $temp = $quiz_data;
    foreach ($keys as $k) {
        if (isset($temp[$k])) {
            $temp = $temp[$k];
        } else {
            return null; // Key doesn't exist, return null or handle as needed
        }
    }
    $upd_quiz_data = $temp;
    // Update array key end


    $question_id = $upd_quiz_data['qs_id'];
    $question_type = get_post_meta($question_id, 'question_type', true);
    $question_title = get_the_title($question_id);

    if ($question_type === 'single') {
        $return['data']['title'] = $question_title;
        // $return['data']['options'] = ['true', 'false'];
        $count = 1;
        $single_options = ['true', 'false'];
        foreach ($upd_quiz_data['condition'] as $k => $v) {

            $k = $key . "['condition']['cd_" . $count . "']";
            $redirection_id = $upd_quiz_data['condition']['cd_' . $count]['page_id'];

            foreach ($single_options as $single_key => $single_val) {
                ++$single_key;
                if ($single_key === $count) {
                    if ($redirection_id != '') {
                        $return['data']['options'][$k] = [
                            'value' => $single_val,
                            'redirect' => get_the_guid($redirection_id),
                            'ques_id' => $question_id
                        ];
                    } else {
                        $return['data']['options'][$k] = [
                            'value' => $single_val,
                            'redirect' => 'empty',
                            'ques_id' => $question_id
                        ];
                    }
                }
            }

            $count++;
        }

        $return['msg'] = 'Quiz found';
        $return['status'] = true;
    } else if ($question_type === 'multiple') {
        $multiple_options = get_post_meta($question_id, 'multiple_options', true);
        $return['data']['title'] = $question_title;

        $count = 1;
        foreach ($upd_quiz_data['condition'] as $k => $v) {

            $k = $key . "['condition']['cd_" . $count . "']";
            $redirection_id = $upd_quiz_data['condition']['cd_' . $count]['page_id'];

            foreach ($multiple_options as $multiple_key => $multiple_val) {
                ++$multiple_key;
                if ($multiple_key === $count) {
                    if ($redirection_id != '') {
                        $return['data']['options'][$k] = [
                            'value' => $multiple_val,
                            'redirect' => get_the_guid($redirection_id),
                            'ques_id' => $question_id
                        ];
                    } else {
                        $return['data']['options'][$k] = [
                            'value' => $multiple_val,
                            'redirect' => 'empty',
                            'ques_id' => $question_id
                        ];
                    }
                }
            }

            $count++;
        }

        $return['msg'] = 'Quiz found';
        $return['status'] = true;
    }
}

print_r(json_encode($return));
exit;

/* function getValueFromArray($array, $keys)
{
    $keys = explode("']['", trim($keys, "['']"));
    $temp = $array;

    foreach ($keys as $key) {
        if (isset($temp[$key])) {
            $temp = $temp[$key];
        } else {
            return null; // Key doesn't exist, return null or handle as needed
        }
    }

    return $temp;
} */
