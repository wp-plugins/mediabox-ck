<?php
/**
 * Plugin Name: Mediabox CK
 * Plugin URI: http://www.wp-pluginsck.com/en/wordpress-plugins/mediabox-ck
 * Description: Mediabox CK shows your medias in a nice responsive Lightbox. You can also use the lightbox on touch device to navigate through the medias, zoom and pan with your fingers.
 * Version: 1.0.0
 * Author: Cédric KEIFLIN
 * Author URI: http://www.wp-pluginsck.com/
 * Text Domain: mediabox-ck
 * Domain Path: /language
 * License: GPL2
 */

defined('ABSPATH') or die;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}

if (is_admin()) {
	require(plugin_dir_path(__FILE__) . 'mediabox-ck-admin.php');
	$plugin = new MediaboxckAdmin();
	// $plugin->init();
} else {
	require(plugin_dir_path(__FILE__) . 'mediabox-ck-front.php');
	$plugin = new MediaboxckFront();
	// $plugin->init();
}