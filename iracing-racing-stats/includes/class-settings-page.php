<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRacing_Settings_Page {

	public static function register(): void {
		add_options_page(
			'iRacing Stats Settings',
			'iRacing Stats',
			'manage_options',
			'iracing-stats',
			array( __CLASS__, 'render' )
		);
	}

	public static function register_settings(): void {
		register_setting(
			'iracing_stats_settings',
			'iracing_garage61_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'iracing_stats_settings',
			'iracing_garage61_driver_id',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'iracing_stats_settings',
			'iracing_cache_ttl',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 60,
			)
		);

		add_settings_section(
			'iracing_stats_main',
			'Garage61 API Configuration',
			array( __CLASS__, 'section_description' ),
			'iracing-stats'
		);

		add_settings_field(
			'iracing_garage61_api_key',
			'Personal Access Token',
			array( __CLASS__, 'field_api_key' ),
			'iracing-stats',
			'iracing_stats_main'
		);

		add_settings_field(
			'iracing_garage61_driver_id',
			'Driver ID',
			array( __CLASS__, 'field_driver_id' ),
			'iracing-stats',
			'iracing_stats_main'
		);

		add_settings_field(
			'iracing_cache_ttl',
			'Cache Duration (minutes)',
			array( __CLASS__, 'field_cache_ttl' ),
			'iracing-stats',
			'iracing_stats_main'
		);
	}

	public static function section_description(): void {
		echo '<p>Enter your <a href="https://garage61.net/developer/authentication" target="_blank" rel="noopener">Garage61 Personal Access Token</a> and your iRacing Driver ID. These are stored securely in your WordPress database and never committed to the repository.</p>';
	}

	public static function field_api_key(): void {
		$value = get_option( 'iracing_garage61_api_key', '' );
		$display = ! empty( $value ) ? str_repeat( '•', 20 ) . substr( $value, -4 ) : '';
		?>
		<input
			type="password"
			name="iracing_garage61_api_key"
			id="iracing_garage61_api_key"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			autocomplete="new-password"
		/>
		<p class="description">Your Garage61 Personal Access Token. Generate one at garage61.net → Developer → Tokens.</p>
		<?php
	}

	public static function field_driver_id(): void {
		$value = get_option( 'iracing_garage61_driver_id', '' );
		?>
		<input
			type="text"
			name="iracing_garage61_driver_id"
			id="iracing_garage61_driver_id"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<p class="description">Your Garage61 / iRacing Driver ID (numeric customer ID).</p>
		<?php
	}

	public static function field_cache_ttl(): void {
		$value = (int) get_option( 'iracing_cache_ttl', 60 );
		?>
		<input
			type="number"
			name="iracing_cache_ttl"
			id="iracing_cache_ttl"
			value="<?php echo esc_attr( $value ); ?>"
			min="1"
			max="1440"
			class="small-text"
		/>
		<p class="description">How long to cache API responses (1–1440 minutes). Default: 60.</p>
		<?php
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle cache clear action.
		if (
			isset( $_POST['iracing_clear_cache'], $_POST['iracing_clear_cache_nonce'] ) &&
			wp_verify_nonce( sanitize_key( $_POST['iracing_clear_cache_nonce'] ), 'iracing_clear_cache' )
		) {
			self::clear_all_transients();
			echo '<div class="notice notice-success"><p>iRacing Stats cache cleared.</p></div>';
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			// Clear cache whenever settings are saved so new credentials take effect immediately.
			self::clear_all_transients();
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'iracing_stats_settings' );
				do_settings_sections( 'iracing-stats' );
				submit_button( 'Save Settings' );
				?>
			</form>

			<hr />

			<h2>Cache</h2>
			<p>API responses are cached for <?php echo esc_html( get_option( 'iracing_cache_ttl', 60 ) ); ?> minutes. Clear the cache to fetch fresh data immediately.</p>
			<form method="post">
				<?php wp_nonce_field( 'iracing_clear_cache', 'iracing_clear_cache_nonce' ); ?>
				<input type="hidden" name="iracing_clear_cache" value="1" />
				<?php submit_button( 'Clear Cache', 'secondary' ); ?>
			</form>

			<hr />

			<h2>Usage</h2>
			<p><strong>Gutenberg Block:</strong> Add the <em>iRacing Racing Dashboard</em> block to any page or post.</p>
			<p><strong>Shortcode:</strong> <code>[iracing_stats]</code></p>
		</div>
		<?php
	}

	private static function clear_all_transients(): void {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_iracing_stats_%',
				'_transient_timeout_iracing_stats_%'
			)
		);
	}
}
