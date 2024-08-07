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
?>
    <?php
    $data_encryption = new \OverlordNews\Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');

    if($encrypted_api_key){
        $api_key = $data_encryption->decrypt($encrypted_api_key);
    }
    ?>
    <div class="wrap">
        <h2>API Key Settings</h2>
        <?php
        // Check if status is 1 which means a successful options save just happened
        if (isset($_GET['status']) && $_GET['status'] == 1) : ?>
            <div class="notice notice-success inline">
                <p>Options Saved!</p>
            </div>
        <?php endif; ?>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <h3>Your API Key</h3>

            <!-- The nonce field is a security feature to avoid submissions from outside WP admin -->
            <?php wp_nonce_field('PortalPay_api_options_verify'); ?>

            <input type="password" name="PortalPay_api_key" placeholder="Enter API Key" value="<?php echo esc_attr(get_option('PortalPay_api_key', '')); ?>">
            <input type="hidden" name="action" value="PortalPay_external_api">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update API Key" />
        </form>
    </div>
<?php
}

add_action('admin_post_PortalPay_external_api', 'OverlordNews\\PortalPay_submit_api_key');

function PortalPay_submit_api_key()
{
    // Make sure user actually has the capability to edit the options
    if (!current_user_can('edit_theme_options')) {
        wp_die("You do not have permission to view this page.");
    }

    
    // Pass in the nonce ID from our form's nonce field - if the nonce fails this will kill script
    check_admin_referer('PortalPay_api_options_verify');

    if (isset($_POST['PortalPay_api_key'])) {
        $data_encryption = new \OverlordNews\Data_Encryption();
        $submitted_api_key = sanitize_text_field($_POST['PortalPay_api_key']);
        $api_key = $data_encryption->encrypt($submitted_api_key);

        if (!empty($api_key)) {
            // Update or add the option
            update_option('PortalPay_api_key', $api_key);
        }
    }
    // Redirect to the previous page with status=1 to show the options updated banner
    wp_redirect(add_query_arg('status', '1', wp_get_referer()));
    exit;
}
?>