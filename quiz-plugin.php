<?php

/**
 * Plugin Name: Quiz Plugin
 * Plugin URI: https://github.com/MuhammadSaudFarooq/quiz-plugin
 * Description: Quiz Plugin
 * Author: Muhammad Saud Farooque
 * Author URI: https://github.com/MuhammadSaudFarooq
 * Version: 1.0.0
 * License: MIT
 **/

if (!defined('ABSPATH')) {
    exit;
}

define("PLUGIN_PREFIX", "qz");
define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));
define("PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

$template = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'quiz_plugin_integration_class.php';

$quizPlugin  = new QuizPluginIntegration(__FILE__);
$quizPlugin->registerActions();
