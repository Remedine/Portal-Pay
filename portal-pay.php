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
 * Tags: Crypto payments, crypto, Overlord, Super Duper, Portalcoin, crypto for woocommerce, Portal Pay, Pay, Portal
 * Version:           1.0.0
 * Author:            Digital Seeds Development
 * Author URI:        https://digitalseeds.dev/
 * Developer: 	      Digital Seeds Development
 * Developer URI:     https://digitalseeds.dev
 * License:           
 * License URI:       
 * Text Domain:       overlord-news-portal-pay
 * Domain Path:       /languages
 * Requires at least: 6.0.0
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Currently plugin version.
 * */
define( 'PORTAL_PAY_VERSION', '1.0.0' );

//initialize files
require_once plugin_dir_path(__FILE__) . 'class-data-encryption.php';
require_once plugin_dir_path(__FILE__) . 'settings-page.php';
require_once plugin_dir_path(__FILE__) . 'create-payment.php';




?>