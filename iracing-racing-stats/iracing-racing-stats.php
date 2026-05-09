<?php
/**
 * Plugin Name: iRacing Racing Stats
 * Plugin URI:  https://github.com/leonhitchens/wordpress-iracing
 * Description: Display iRacing statistics powered by the Garage61 API as a Gutenberg block or shortcode.
 * Version:     1.0.0
 * Author:      Leon Hitchens
 * Author URI:  https://leonhitchens.com
 * License:     GPL-2.0-or-later
 * Text Domain: iracing-racing-stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IRACING_STATS_VERSION', '1.0.0' );
define( 'IRACING_STATS_DIR', plugin_dir_path( __FILE__ ) );
define( 'IRACING_STATS_URL', plugin_dir_url( __FILE__ ) );

require_once IRACING_STATS_DIR . 'includes/class-garage61-api.php';
require_once IRACING_STATS_DIR . 'includes/class-settings-page.php';
require_once IRACING_STATS_DIR . 'includes/class-shortcode.php';

add_action( 'admin_menu', array( 'IRacing_Settings_Page', 'register' ) );
add_action( 'admin_init', array( 'IRacing_Settings_Page', 'register_settings' ) );
add_action( 'init', 'iracing_stats_register_block' );
add_action( 'init', array( 'IRacing_Shortcode', 'register' ) );
add_action( 'wp_enqueue_scripts', 'iracing_stats_enqueue_assets' );

function iracing_stats_register_block() {
	register_block_type(
		IRACING_STATS_DIR . 'blocks/racing-dashboard/block.json',
		array(
			'render_callback' => 'iracing_stats_render_block',
		)
	);
}

function iracing_stats_render_block( $attributes ) {
	$driver_id = get_option( 'iracing_garage61_driver_id', '' );
	$api_key   = get_option( 'iracing_garage61_api_key', '' );

	if ( ! $driver_id || ! $api_key ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<div class="iracing-stats-notice">iRacing Stats: Please configure your API key and Driver ID in <a href="' . esc_url( admin_url( 'options-general.php?page=iracing-stats' ) ) . '">Settings → iRacing Stats</a>.</div>';
		}
		return '';
	}

	$api  = new Garage61_API( $api_key, $driver_id );
	$data = $api->get_dashboard_data();

	if ( is_wp_error( $data ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<div class="iracing-stats-notice">iRacing Stats error: ' . esc_html( $data->get_error_message() ) . '</div>';
		}
		return '';
	}

	ob_start();
	include IRACING_STATS_DIR . 'templates/dashboard.php';
	return ob_get_clean();
}

function iracing_stats_enqueue_assets() {
	if ( ! has_block( 'iracing-racing-stats/racing-dashboard' ) && ! iracing_stats_page_has_shortcode() ) {
		return;
	}

	wp_enqueue_style(
		'iracing-racing-stats',
		IRACING_STATS_URL . 'assets/css/racing-stats.css',
		array(),
		IRACING_STATS_VERSION
	);

	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js',
		array(),
		'4',
		true
	);

	wp_enqueue_script(
		'iracing-racing-chart',
		IRACING_STATS_URL . 'assets/js/racing-chart.js',
		array( 'chartjs' ),
		IRACING_STATS_VERSION,
		true
	);
}

function iracing_stats_page_has_shortcode() {
	global $post;
	return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'iracing_stats' );
}
