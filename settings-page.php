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
        'OverlordNews\PortalPay_main_page_callback', // Callback function
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
        'OverlordNews\PortalPay_add_api_keys_callback' // Callback function
    );
}

add_action('admin_menu', 'OverlordNews\PortalPay_register_my_menu_page');

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
            <?php wp_nonce_field('fsdapikey_api_options_verify'); ?>

            <input type="password" name="our_api_key" placeholder="Enter API Key" value="<?php echo isset($api_key) ? esc_attr($api_key) : ''; ?>">
            <input type="hidden" name="action" value="fsdapikey_external_api">
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update API Key" />
        </form>
    </div>
<?php
}
?>