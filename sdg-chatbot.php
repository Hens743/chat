<?php
/*
Plugin Name: SDG Chatbot
Description: A customizable chatbot with logging.
Version: 1.0.0
Author: You
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ----- Constants -----
define( 'SDG_CHATBOT_FILE', __FILE__ );
define( 'SDG_CHATBOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SDG_CHATBOT_URL', plugin_dir_url( __FILE__ ) );
define( 'SDG_CHATBOT_VERSION', '1.0.0' );

// ----- Includes -----
require_once SDG_CHATBOT_DIR . 'includes/frontend/enqueue-assets.php';
require_once SDG_CHATBOT_DIR . 'includes/admin/settings-page.php';
require_once SDG_CHATBOT_DIR . 'includes/api/rest-endpoint.php';
require_once SDG_CHATBOT_DIR . 'includes/core/logging.php';

// ----- Activation: create logs table -----
register_activation_hook( __FILE__, function() {
    global $wpdb;
    $table = $wpdb->prefix . 'sdg_chatbot_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_message TEXT NOT NULL,
        bot_reply TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
});

// ----- Init frontend -----
new SDG_Chatbot_Enqueue_Assets();

// ----- Admin Menus -----
add_action( 'admin_menu', function() {
    // Settings (top level)
    add_menu_page(
        'SDG Chatbot',
        'SDG Chatbot',
        'manage_options',
        'sdg_chatbot',
        function() { ( new SDG_Chatbot_Settings() )->render_page(); },
        'dashicons-format-chat',
        85
    );

    // Logs submenu
    add_submenu_page(
        'sdg_chatbot',
        'Chat Logs',
        'Logs',
        'manage_options',
        'sdg_chatbot_logs',
        function() { ( new SDG_Chatbot_Logging() )->render_log_page(); }
    );
});

// ----- Admin assets (only on our pages) -----
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Loads on toplevel_page_sdg_chatbot and sdg_chatbot_page_* subpages
    if ( stripos( $hook, 'sdg_chatbot' ) === false ) return;

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_script(
        'sdg-chatbot-admin',
        SDG_CHATBOT_URL . 'assets/js/admin.js',
        array( 'jquery', 'wp-color-picker' ),
        SDG_CHATBOT_VERSION,
        true
    );
});
