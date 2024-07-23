<?php
// Save Quiz
if (isset($_POST['action']) && $_POST['action'] == PLUGIN_PREFIX . '_save_quiz') {

    global $wpdb;
    $tablename = PLUGIN_PREFIX . '_entries';
    $firstname = $_POST['ques_data']['firstname'];
    $lastname = $_POST['ques_data']['lastname'];
    $email = $_POST['ques_data']['email'];
    $phone = $_POST['ques_data']['phone'];
    $body_part = $_POST['ques_data']['body_part'];
    $return = [
        'msg' => 'Something went wrong!',
        'status' => false
    ];

    $res = $wpdb->insert(
        $tablename,
        array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phone' => $phone,
            'body_part' => $body_part,
            'data' => maybe_serialize($_POST['ques_data']['list']),
            'created_at' => date('Y-m-d H:i:s')
        ),
        array('%s', '%s')
    );

    if ($res) {
        $return['msg'] = 'Inserted';
        $return['status'] = true;
    }

    print_r(json_encode($return));
    exit;
}
