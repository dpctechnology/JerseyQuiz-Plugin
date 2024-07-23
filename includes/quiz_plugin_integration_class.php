<?php
class QuizPluginIntegration
{
    private $wpdb;
    private $pluginFileName = null;
    private $categoryKeyName = null;

    public function __construct($pluginFileName)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->pluginFileName = $pluginFileName;
        $this->categoryKeyName = PLUGIN_PREFIX . "_quiz_categories";
    }

    public function registerActions()
    {
        register_uninstall_hook($this->pluginFileName, [$this, 'uninstallAction']);
        add_action('init', [$this, 'registerPostTypes']);
        add_action('admin_menu', [$this, 'optionPages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStyles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontStyles']);
        add_action('wp_ajax_' . PLUGIN_PREFIX . '_get_questions', [$this, 'ajaxGetQuestions']);
        add_action('wp_ajax_' . PLUGIN_PREFIX . '_condition_options', [$this, 'ajaxConditionOptions']);
        add_action('wp_ajax_' . PLUGIN_PREFIX . '_quiz_render', [$this, 'ajaxQuizRender']);
        add_action('wp_ajax_nopriv_' . PLUGIN_PREFIX . '_quiz_render', [$this, 'ajaxQuizRender']);
        add_action('wp_ajax_' . PLUGIN_PREFIX . '_next_question', [$this, 'ajaxNextQuestionRender']);
        add_action('wp_ajax_nopriv_' . PLUGIN_PREFIX . '_next_question', [$this, 'ajaxNextQuestionRender']);
        add_action('wp_ajax_' . PLUGIN_PREFIX . '_save_quiz', [$this, 'ajaxSaveQuiz']);
        add_action('wp_ajax_nopriv_' . PLUGIN_PREFIX . '_save_quiz', [$this, 'ajaxSaveQuiz']);
        add_shortcode('quizzes', [$this, 'quizzesShortcode']);

        // Insert Categories in database
        $quizCategories = [
            'category-1'    => 'Shoulder',
            'category-2'    => 'Neck',
            'category-3'    => 'Arm',
            'category-4'    => 'Head',
            'category-5'    => 'Legs',
            'category-6'    => 'Chest',
            'category-7'    => 'Calves',
            'category-8'    => 'Backbone',
            'category-9'    => 'Lower Back',
            'category-10'   => 'Traps',
            'category-11'   => 'Abs',
            'category-12'   => 'Forearms'
        ];
        update_option($this->categoryKeyName, $quizCategories);

        // Create Sync Table
        $entry_charset_collate = $this->wpdb->get_charset_collate();
        $entry_table = PLUGIN_PREFIX . '_entries';
        $entry_check_query = $this->wpdb->prepare('SHOW TABLES LIKE %s', $this->wpdb->esc_like($entry_table));

        if (!$this->wpdb->get_var($entry_check_query) == $entry_table) {
            $entry_sql = "CREATE TABLE " . $entry_table . " (
                id int(11) NOT NULL AUTO_INCREMENT,
                firstname VARCHAR(255) DEFAULT NULL,
                lastname VARCHAR(255) DEFAULT NULL,
                email VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                body_part VARCHAR(255) DEFAULT NULL,
                data LONGTEXT DEFAULT NULL,
                created_at TIMESTAMP,
                PRIMARY KEY  (id)
                ) $entry_charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($entry_sql);
        }
    }

    public function registerPostTypes()
    {
        require_once $this->getPostTypeUrl(PLUGIN_PREFIX . '_questions.php');
        require_once $this->getPostTypeUrl(PLUGIN_PREFIX . '_quiz.php');
    }

    public function optionPages()
    {

        add_menu_page(
            'Quiz Settings',
            'Quiz Settings',
            'manage_options',
            'quiz_settings',
            array($this, 'quizSettingsPageCallback'),
            'dashicons-admin-generic',
            10
        );

        add_submenu_page(
            'edit.php?post_type=' . PLUGIN_PREFIX . '-quiz',
            'Entries',
            'Entries',
            'manage_options',
            PLUGIN_PREFIX . '-entries',
            array($this, 'quizEntriesCallback')
        );
    }

    public function quizSettingsPageCallback()
    {
        require_once PLUGIN_DIR_PATH . "/includes/templates/" . PLUGIN_PREFIX . "_settings.php";
    }

    public function quizEntriesCallback()
    {
        require_once PLUGIN_DIR_PATH . "/includes/templates/" . PLUGIN_PREFIX . "_submission.php";
    }

    public function enqueueAdminScripts()
    {
        $screen = get_current_screen();

        wp_enqueue_script('font_awesome_js', $this->getScriptUrl('all.min.js'), array(), null, false);
        if ($screen && $screen->id === PLUGIN_PREFIX . '-quiz_page_' . PLUGIN_PREFIX . '-entries') {
            wp_enqueue_script('twitter_bootstrap_js', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js', array(), null, false);
            wp_enqueue_script('data_table_js', 'https://cdn.datatables.net/2.0.1/js/dataTables.js', array(), null, false);
            wp_enqueue_script('data_table_bootstrap_js', 'https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js', array(), null, false);
        }
        wp_enqueue_script(PLUGIN_PREFIX . '_admin_js', $this->getScriptUrl(PLUGIN_PREFIX . '_admin_js.js'), array(), null, false);
        wp_localize_script(PLUGIN_PREFIX . '_admin_js', 'URLs', array('AJAX_URL' => admin_url('admin-ajax.php'), 'SITE_URL' => site_url(), 'PLUGIN_PREFIX' => PLUGIN_PREFIX));
    }

    public function enqueueAdminStyles()
    {
        $screen = get_current_screen();
        wp_enqueue_style('font_awesome_css', $this->getStyleUrl('all.min.css'), array(), null, 'all');
        if ($screen && $screen->id === PLUGIN_PREFIX . '-quiz_page_' . PLUGIN_PREFIX . '-entries') {
            wp_enqueue_style('twitter_bootstrap_css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css', array(), null, 'all');
            wp_enqueue_style('data_table_bootstrap_css', 'https://cdn.datatables.net/2.0.1/css/dataTables.bootstrap5.css', array(), null, 'all');
        }
        wp_enqueue_style(PLUGIN_PREFIX . '_admin_css', $this->getStyleUrl(PLUGIN_PREFIX . '_admin_css.css'), array(), null, 'all');
    }

    public function enqueueFrontScripts()
    {
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0', false);
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, false);
        wp_enqueue_script('font_awesome_js', $this->getScriptUrl('all.min.js'), array(), null, false);
        wp_enqueue_script(PLUGIN_PREFIX . '_front_js', $this->getScriptUrl(PLUGIN_PREFIX . '_front_js.js'), array(), null, false);
        wp_localize_script(PLUGIN_PREFIX . '_front_js', 'URLs', array('AJAX_URL' => admin_url('admin-ajax.php'), 'SITE_URL' => site_url(), 'PLUGIN_PREFIX' => PLUGIN_PREFIX));
    }

    public function enqueueFrontStyles()
    {
        wp_enqueue_style('font_awesome_css', $this->getStyleUrl('all.min.css'), array(), null, 'all');
        wp_enqueue_style(PLUGIN_PREFIX . '_front_css', $this->getStyleUrl(PLUGIN_PREFIX . '_front_css.css'), array(), null, 'all');
    }

    public function uninstallAction()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
    }

    public function ajaxGetQuestions()
    {
        require_once PLUGIN_DIR_PATH . "/includes/ajax/" . PLUGIN_PREFIX . "_get_questions.php";
    }

    public function ajaxConditionOptions()
    {
        require_once PLUGIN_DIR_PATH . "/includes/ajax/" . PLUGIN_PREFIX . "_condition_options.php";
    }

    public function ajaxQuizRender()
    {
        require_once PLUGIN_DIR_PATH . "/includes/ajax/" . PLUGIN_PREFIX . "_quiz_render.php";
    }

    public function ajaxNextQuestionRender()
    {
        require_once PLUGIN_DIR_PATH . "/includes/ajax/" . PLUGIN_PREFIX . "_next_question_render.php";
    }

    public function ajaxSaveQuiz()
    {
        require_once PLUGIN_DIR_PATH . "/includes/ajax/" . PLUGIN_PREFIX . "_save_quiz.php";
    }

    public function quizzesShortcode()
    {
        require_once PLUGIN_DIR_PATH . "/includes/templates/" . PLUGIN_PREFIX . "_view.php";
    }

    private function getPostTypeUrl($postTypeName)
    {
        return PLUGIN_DIR_PATH . '/includes/post-types/' . $postTypeName;
    }

    private function getScriptUrl($scriptName)
    {
        return PLUGIN_DIR_URL . 'public/js/' . $scriptName;
    }

    private function getStyleUrl($styleName)
    {
        return PLUGIN_DIR_URL . 'public/css/' . $styleName;
    }
}
