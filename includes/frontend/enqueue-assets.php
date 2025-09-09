<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SDG_Chatbot_Enqueue_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
        add_action( 'wp_footer', array( $this, 'inject_container_html' ) );
    }

    public function enqueue_styles_and_scripts() {
        wp_enqueue_style(
            'sdg-chatbot-style',
            SDG_CHATBOT_URL . 'assets/css/style.css',
            array(),
            SDG_CHATBOT_VERSION
        );

        wp_enqueue_script(
            'sdg-chatbot-main',
            SDG_CHATBOT_URL . 'assets/js/main.js',
            array( 'jquery' ),
            SDG_CHATBOT_VERSION,
            true
        );

        $opts = get_option( 'sdg_chatbot_options', array() );
        $defaults = array(
            'chatbot_logo'            => '',
            'chatbot_header_title'    => 'AI Assistant',
            'chatbot_title_font_size' => 16,
            'chatbot_title_color'     => '#ffffff',
            'chatbot_header_bg_color' => '#0073aa',
            'chatbot_welcome_message' => 'Hi there! How can I help you today?',
            'chatbot_enable_logging'  => 1,
        );
        $o = wp_parse_args( $opts, $defaults );

        wp_localize_script( 'sdg-chatbot-main', 'sdgChatbotData', array(
            'rest_url'        => esc_url_raw( rest_url( 'sdg-chatbot/v1/query' ) ),
            'nonce'           => wp_create_nonce( 'wp_rest' ),
            'welcome_message' => $o['chatbot_welcome_message'],
            'logo_url'        => esc_url( $o['chatbot_logo'] ),
            'header_title'    => $o['chatbot_header_title'],
            'title_font_size' => (int) $o['chatbot_title_font_size'],
            'title_color'     => $o['chatbot_title_color'],
            'header_bg_color' => $o['chatbot_header_bg_color'],
        ) );
    }

    public function inject_container_html() {
        echo '<div id="sdg-chatbot-container"></div>';
    }
}
