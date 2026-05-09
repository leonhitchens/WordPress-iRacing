<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRacing_Shortcode {

	public static function register(): void {
		add_shortcode( 'iracing_stats', array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the racing dashboard via shortcode.
	 *
	 * Usage: [iracing_stats]
	 * Optional overrides: [iracing_stats driver_id="123456"]
	 */
	public static function render( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'driver_id' => '',
			),
			$atts,
			'iracing_stats'
		);

		$driver_id = ! empty( $atts['driver_id'] )
			? sanitize_text_field( $atts['driver_id'] )
			: get_option( 'iracing_garage61_driver_id', '' );

		$api_key = get_option( 'iracing_garage61_api_key', '' );

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
}
