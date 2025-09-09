<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SDG_Chatbot_Logging {

    public function render_log_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        global $wpdb;
        $table = $wpdb->prefix . 'sdg_chatbot_logs';

        // Simple bulk delete if requested
        if ( isset($_POST['sdg_chatbot_clear_logs']) && check_admin_referer('sdg_chatbot_clear_logs_action', 'sdg_chatbot_clear_logs_nonce') ) {
            $wpdb->query( "TRUNCATE TABLE $table" );
            echo '<div class="notice notice-success is-dismissible"><p>All logs cleared.</p></div>';
        }

        $logs = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 500" );
        ?>
        <div class="wrap">
            <h1>SDG Chatbot Logs</h1>

            <form method="post" style="margin: 12px 0;">
                <?php wp_nonce_field('sdg_chatbot_clear_logs_action','sdg_chatbot_clear_logs_nonce'); ?>
                <button type="submit" name="sdg_chatbot_clear_logs" class="button">Clear All Logs</button>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th width="110">Date</th>
                        <th>User Message</th>
                        <th>Bot Reply</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( $logs ) : ?>
                    <?php foreach ( $logs as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row->created_at ); ?></td>
                            <td><?php echo esc_html( $row->user_message ); ?></td>
                            <td><?php echo esc_html( $row->bot_reply ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No logs yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
