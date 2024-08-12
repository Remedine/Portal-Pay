<?php

namespace OverlordNews;

function PortalPay_register_my_menu_page()
{
    // Add top-level menu page
    add_menu_page(
        'Portal Pay', // Page title
        'Portal Pay', // Menu title
        'manage_options', // Capability
        'portal-pay', // Menu slug
        'OverlordNews\\PortalPay_main_page_callback', // Callback function
        'dashicons-money-alt', // Icon URL
        2 // Position
    );

    // Add submenu page for settings
    add_submenu_page(
        'portal-pay', // Parent slug
        'Portal Pay Settings', // Page title
        'Settings', // Menu title
        'manage_options', // Capability
        'portal-pay-settings', // Menu slug
        'OverlordNews\\PortalPay_add_api_keys_callback' // Callback function
    );
}

add_action('admin_menu', 'OverlordNews\\PortalPay_register_my_menu_page');

// Callback function for the main menu page
function PortalPay_main_page_callback()
{
    echo '<div class="wrap">';
    echo '<h2>Portal Pay Main Page</h2>';
    echo '<p>Welcome to the Portal Pay main page.</p>';
    echo '</div>';
}

// Callback function for the settings page
function PortalPay_add_api_keys_callback()
{
    $data_encryption = new \OverlordNews\Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');

    if ($encrypted_api_key) {
        $api_key = $data_encryption->decrypt($encrypted_api_key);
    }

    $webhook_url = get_option('PortalPay_webhook_url', '');
?>
    <div class="wrap">
        <h2>API Key & Webhook Settings</h2>
        <?php
        if (isset($_GET['status']) && $_GET['status'] == 1) : ?>
            <div class="notice notice-success inline">
                <p>Options Saved and Webhook Set!</p>
            </div>
        <?php endif; ?>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <h3>Your API Key</h3>

            <?php wp_nonce_field('PortalPay_api_options_verify'); ?>

            <input type="password" name="PortalPay_api_key" placeholder="Enter API Key" value="<?php echo esc_attr($api_key); ?>">

            <h3>Webhook URL</h3>
            <input type="text" name="PortalPay_webhook_url" placeholder="Enter Webhook URL" value="<?php echo esc_url($webhook_url); ?>">

            <input type="hidden" name="action" value="PortalPay_external_api">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Settings" />
        </form>

        <?php
        // Retrieve and display the list of webhooks
        PortalPay_display_webhooks_list();
        ?>
    </div>
<?php
}

add_action('admin_post_PortalPay_external_api', 'OverlordNews\\PortalPay_submit_api_key');

function PortalPay_submit_api_key()
{
    if (!current_user_can('edit_theme_options')) {
        wp_die("You do not have permission to view this page.");
    }

    check_admin_referer('PortalPay_api_options_verify');

    if (isset($_POST['PortalPay_api_key'])) {
        $data_encryption = new \OverlordNews\Data_Encryption();
        $submitted_api_key = sanitize_text_field($_POST['PortalPay_api_key']);
        $api_key = $data_encryption->encrypt($submitted_api_key);

        if (!empty($api_key)) {
            update_option('PortalPay_api_key', $api_key);
        }
    }

    if (isset($_POST['PortalPay_webhook_url'])) {
        $webhook_url = esc_url_raw($_POST['PortalPay_webhook_url']);
        if (!empty($webhook_url)) {
            update_option('PortalPay_webhook_url', $webhook_url);
        }
    }

    // After saving, set the webhook endpoint
    PortalPay_send_webhook();

    // Redirect to the previous page with status=1 to show the options updated banner
    wp_redirect(add_query_arg('status', '1', wp_get_referer()));
    exit;
}

// Function to send the webhook request
function PortalPay_send_webhook()
{
    $data_encryption = new \OverlordNews\Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');
    $api_key = $data_encryption->decrypt($encrypted_api_key);

    $webhook_url = get_option('PortalPay_webhook_url', '');

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.portal-pay.xyz/webhook/endpoints",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'url' => $webhook_url
        ]),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer $api_key",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        echo $response;
    }
}

// Function to retrieve and display the list of webhooks
function PortalPay_display_webhooks_list()
{
    $data_encryption = new \OverlordNews\Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');
    $api_key = $data_encryption->decrypt($encrypted_api_key);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.portal-pay.xyz/webhook/endpoints",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $api_key",
            "accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "<p>cURL Error #: $err</p>";
    } else {
        $response_data = json_decode($response, true);
        $webhooks = $response_data['results'] ?? [];

        if (!empty($webhooks) && is_array($webhooks)) {
            echo '<h3>Existing Webhooks</h3>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>ID</th><th>URL</th><th>Delete Endpoint</th></tr></thead>';
            echo '<tbody>';

            foreach ($webhooks as $webhook) {
                if (isset($webhook['id']) && isset($webhook['url'])) {
                    $delete_url = esc_url(add_query_arg([
                        'action' => 'PortalPay_delete_webhook',
                        'webhook_id' => $webhook['id'],
                    ], admin_url('admin-post.php')));

                    echo '<tr>';
                    echo '<td>' . esc_html($webhook['id']) . '</td>';
                    echo '<td>' . esc_url($webhook['url']) . '</td>';
                    echo '<td><a href="' . $delete_url . '" class="delete-webhook" data-webhook-id="' . esc_attr($webhook['id']) . '">Delete</a></td>';
                    echo '</tr>';
                } else {
                    echo '<tr><td colspan="3">Invalid webhook data.</td></tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No webhooks found or invalid response from the API.</p>';
        }
    }
}

// Action to handle webhook deletion
add_action('admin_post_PortalPay_delete_webhook', 'OverlordNews\\PortalPay_delete_webhook');

function PortalPay_delete_webhook()
{
    if (!current_user_can('manage_options')) {
        wp_die("You do not have permission to delete this webhook.");
    }

    if (!isset($_GET['webhook_id'])) {
        wp_die("Invalid webhook ID.");
    }

    $webhook_id = sanitize_text_field($_GET['webhook_id']);
    $data_encryption = new \OverlordNews\Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');
    $api_key = $data_encryption->decrypt($encrypted_api_key);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.portal-pay.xyz/webhook/endpoints/" . urlencode($webhook_id),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer $api_key"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "<p>cURL Error #:" . $err . "</p>";
    } else {
        echo "<p>Webhook deleted successfully.</p>";
    }

    // Redirect back to the settings page
    wp_redirect(wp_get_referer());
    exit;
}

// JavaScript to confirm deletion
add_action('admin_footer', function () {
?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('.delete-webhook');

            deleteLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const confirmation = confirm("Are you sure? Deleting this webhook will remove the endpoint from Portal Pay and updates will no longer be sent to this endpoint.");
                    if (!confirmation) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
<?php
});
?>