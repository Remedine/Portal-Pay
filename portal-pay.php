<?php
namespace OverlordNews;
/**
 * @since             1.0.0
 * @package           Portal_Pay
 *
 * @wordpress-plugin
 * Plugin Name:       Portal Pay
 * Plugin URI:        https://overlord.news/portal-pay-plugin
 * Description:       Integrate Woocommerce with Portal Pay. 

*Make the Overlord Proud
 * Version:           1.0.0
 * Author:            Digital Seeds Development
 * Author URI:        https://digitalseeds.dev/
 * License:           
 * License URI:       
 * Text Domain:       overlord-news-portal-pay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * */
define( 'PORTAL_PAY_VERSION', '1.0.0' );

// Test to see if WooCommerce is active (including network activated).
$plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';

if (
	in_array($plugin_path, wp_get_active_and_valid_plugins())
	|| in_array($plugin_path, wp_get_active_network_plugins())
);

?>