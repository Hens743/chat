<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SDG_Chatbot_Settings {

    private function get_defaults() {
        return array(
            'chatbot_logo'            => '',
            'chatbot_header_title'    => 'AI Assistant',
            'chatbot_title_font_size' => 16,
            'chatbot_title_color'     => '#ffffff',
            'chatbot_header_bg_color' => '#0073aa',
            'chatbot_welcome_message' => 'Hi there! How can I help you today?',
            'chatbot_enable_logging'  => 1, // default logging ON; change to 0 if you prefer off
        );
    }

    private function get_options() {
        $saved = get_option( 'sdg_chatbot_options', array() );
        return wp_parse_args( $saved, $this->get_defaults() );
    }

    private function sanitize( $data ) {
        $clean = array();
        $clean['chatbot_logo']            = isset($data['chatbot_logo']) ? esc_url_raw($data['chatbot_logo']) : '';
        $clean['chatbot_header_title']    = isset($data['chatbot_header_title']) ? sanitize_text_field($data['chatbot_header_title']) : '';
        $clean['chatbot_title_font_size'] = isset($data['chatbot_title_font_size']) ? absint($data['chatbot_title_font_size']) : 16;
        $clean['chatbot_title_color']     = isset($data['chatbot_title_color']) ? sanitize_hex_color($data['chatbot_title_color']) : '#ffffff';
        $clean['chatbot_header_bg_color'] = isset($data['chatbot_header_bg_color']) ? sanitize_hex_color($data['chatbot_header_bg_color']) : '#0073aa';
        $clean['chatbot_welcome_message'] = isset($data['chatbot_welcome_message']) ? sanitize_textarea_field($data['chatbot_welcome_message']) : '';
        $clean['chatbot_enable_logging']  = ! empty($data['chatbot_enable_logging']) ? 1 : 0;
        return $clean;
    }

    public function handle_post() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        // Reset
        if ( isset($_POST['sdg_chatbot_reset']) ) {
            check_admin_referer( 'sdg_chatbot_reset_action', 'sdg_chatbot_reset_nonce' );
            delete_option( 'sdg_chatbot_options' );
            wp_redirect( admin_url( 'admin.php?page=sdg_chatbot&reset=1' ) );
            exit;
        }

        // Save
        if ( isset($_POST['sdg_chatbot_save']) ) {
            check_admin_referer( 'sdg_chatbot_save_action', 'sdg_chatbot_save_nonce' );

            $incoming = array(
                'chatbot_logo'            => $_POST['chatbot_logo']            ?? '',
                'chatbot_header_title'    => $_POST['chatbot_header_title']    ?? '',
                'chatbot_title_font_size' => $_POST['chatbot_title_font_size'] ?? '',
                'chatbot_title_color'     => $_POST['chatbot_title_color']     ?? '',
                'chatbot_header_bg_color' => $_POST['chatbot_header_bg_color'] ?? '',
                'chatbot_welcome_message' => $_POST['chatbot_welcome_message'] ?? '',
                'chatbot_enable_logging'  => $_POST['chatbot_enable_logging']  ?? '',
            );

            $clean = $this->sanitize( $incoming );
            update_option( 'sdg_chatbot_options', $clean );

            wp_redirect( admin_url( 'admin.php?page=sdg_chatbot&saved=1' ) );
            exit;
        }
    }

    public function render_page() {
        $this->handle_post();
        $o = $this->get_options();
        ?>
        <div class="wrap">
            <h1>SDG Chatbot Settings</h1>

            <?php if ( isset($_GET['saved']) ): ?>
                <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
            <?php endif; ?>
            <?php if ( isset($_GET['reset']) ): ?>
                <div class="notice notice-success is-dismissible"><p>Settings reset to defaults.</p></div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field( 'sdg_chatbot_save_action', 'sdg_chatbot_save_nonce' ); ?>

                <h2>Header Customization</h2>
                <table class="form-table" role="presentation"><tbody>
                    <tr>
                        <th scope="row"><label>Chatbot Logo</label></th>
                        <td>
                            <div class="chatbot-logo-preview" style="margin-bottom:8px;">
                                <?php if ( ! empty($o['chatbot_logo']) ): ?>
                                    <img src="<?php echo esc_url($o['chatbot_logo']); ?>" style="max-height:60px; width:auto;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" id="chatbot_logo" name="chatbot_logo" value="<?php echo esc_attr($o['chatbot_logo']); ?>">
                            <button type="button" class="button upload-logo-button">Upload/Select Logo</button>
                            <button type="button" class="button remove-logo-button" style="<?php echo empty($o['chatbot_logo']) ? 'display:none' : ''; ?>">Remove Logo</button>
                            <p class="description">Recommended logo height: 30–40px.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="chatbot_header_title">Header Title</label></th>
                        <td><input type="text" id="chatbot_header_title" name="chatbot_header_title" class="regular-text" value="<?php echo esc_attr($o['chatbot_header_title']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="chatbot_title_font_size">Title Font Size</label></th>
                        <td><input type="number" id="chatbot_title_font_size" name="chatbot_title_font_size" class="small-text" min="10" step="1" value="<?php echo esc_attr($o['chatbot_title_font_size']); ?>"> px</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="chatbot_title_color">Title Color</label></th>
                        <td><input type="text" id="chatbot_title_color" name="chatbot_title_color" class="chatbot-color-picker" value="<?php echo esc_attr($o['chatbot_title_color']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="chatbot_header_bg_color">Header Background Color</label></th>
                        <td><input type="text" id="chatbot_header_bg_color" name="chatbot_header_bg_color" class="chatbot-color-picker" value="<?php echo esc_attr($o['chatbot_header_bg_color']); ?>"></td>
                    </tr>
                </tbody></table>

                <h2>General</h2>
                <table class="form-table" role="presentation"><tbody>
                    <tr>
                        <th scope="row"><label for="chatbot_welcome_message">Welcome Message</label></th>
                        <td><textarea id="chatbot_welcome_message" name="chatbot_welcome_message" class="large-text" rows="3"><?php echo esc_textarea($o['chatbot_welcome_message']); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Chat Logging</th>
                        <td>
                            <label>
                                <input type="checkbox" name="chatbot_enable_logging" value="1" <?php checked(1, $o['chatbot_enable_logging']); ?>>
                                Log user questions and bot replies
                            </label>
                            <p class="description">Disable this to stop saving conversations to the database.</p>
                        </td>
                    </tr>
                </tbody></table>

                <p>
                    <button type="submit" name="sdg_chatbot_save" class="button button-primary">Save Settings</button>
                </p>
            </form>

            <hr>

            <form method="post" onsubmit="return confirm('Reset all chatbot settings to defaults? This cannot be undone.');">
                <?php wp_nonce_field( 'sdg_chatbot_reset_action', 'sdg_chatbot_reset_nonce' ); ?>
                <button type="submit" name="sdg_chatbot_reset" class="button button-secondary">Reset to Default</button>
            </form>
        </div>
        <?php
    }
}
