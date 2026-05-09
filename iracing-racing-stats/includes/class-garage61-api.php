<?php
/**
 * Garage61 API client.
 *
 * Authenticates with a Personal Access Token via:
 *   Authorization: Bearer <token>
 *
 * All responses are cached using WordPress transients.
 *
 * TODO: Confirm exact endpoint paths from https://garage61.net/developer/endpoints
 * and update the BASE_URL + endpoint strings in each method below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Garage61_API {

	// TODO: confirm the correct base URL from https://garage61.net/developer/endpoints
	const BASE_URL = 'https://garage61.net/api/v1';

	private string $api_key;
	private string $driver_id;
	private int    $cache_ttl;

	public function __construct( string $api_key, string $driver_id ) {
		$this->api_key   = $api_key;
		$this->driver_id = $driver_id;
		$this->cache_ttl = (int) get_option( 'iracing_cache_ttl', 60 ) * MINUTE_IN_SECONDS;
	}

	/**
	 * Fetch all data needed for the dashboard in a single call.
	 * Returns a structured array or WP_Error.
	 */
	public function get_dashboard_data(): array|WP_Error {
		$cache_key = 'iracing_stats_dashboard_' . md5( $this->driver_id );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$profile       = $this->get_driver_profile();
		$career        = $this->get_career_stats();
		$current_season = $this->get_current_season();
		$recent_races  = $this->get_recent_races();
		$irating_history = $this->get_irating_history();

		foreach ( array( $profile, $career, $current_season, $recent_races, $irating_history ) as $result ) {
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		$data = array(
			'profile'         => $profile,
			'career'          => $career,
			'current_season'  => $current_season,
			'recent_races'    => $recent_races,
			'irating_history' => $irating_history,
		);

		set_transient( $cache_key, $data, $this->cache_ttl );

		return $data;
	}

	/**
	 * Driver profile: name, member since, license class, iRating, safety rating.
	 *
	 * TODO: confirm endpoint path — likely /drivers/{id} or /members/{id}
	 */
	public function get_driver_profile(): array|WP_Error {
		return $this->request( '/drivers/' . rawurlencode( $this->driver_id ) );
	}

	/**
	 * Career stats: starts, wins, top 5s, laps, win %, avg finish.
	 *
	 * TODO: confirm endpoint path — likely /drivers/{id}/career or /stats/career
	 */
	public function get_career_stats(): array|WP_Error {
		return $this->request( '/drivers/' . rawurlencode( $this->driver_id ) . '/career' );
	}

	/**
	 * Current active season stats: series, division, starts, wins, points.
	 *
	 * TODO: confirm endpoint path — likely /drivers/{id}/season or /drivers/{id}/seasons/current
	 */
	public function get_current_season(): array|WP_Error {
		return $this->request( '/drivers/' . rawurlencode( $this->driver_id ) . '/seasons/current' );
	}

	/**
	 * Recent race results: date, series, track, start pos, finish pos, iRating delta.
	 *
	 * TODO: confirm endpoint path — likely /drivers/{id}/races or /drivers/{id}/results
	 */
	public function get_recent_races( int $limit = 10 ): array|WP_Error {
		return $this->request( '/drivers/' . rawurlencode( $this->driver_id ) . '/races', array( 'limit' => $limit ) );
	}

	/**
	 * iRating history for chart: array of { date, irating }.
	 *
	 * TODO: confirm endpoint path — likely /drivers/{id}/irating or /drivers/{id}/history
	 */
	public function get_irating_history(): array|WP_Error {
		return $this->request( '/drivers/' . rawurlencode( $this->driver_id ) . '/irating' );
	}

	/**
	 * Clear all cached data for this driver.
	 */
	public function clear_cache(): void {
		delete_transient( 'iracing_stats_dashboard_' . md5( $this->driver_id ) );
	}

	/**
	 * Make an authenticated GET request to the Garage61 API.
	 *
	 * Auth: Personal Access Token sent as Authorization: Bearer <token>
	 * Ref: https://garage61.net/developer/authentication
	 */
	private function request( string $endpoint, array $params = array() ): array|WP_Error {
		$url = self::BASE_URL . $endpoint;

		if ( ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Accept'        => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 401 === $code ) {
			return new WP_Error( 'garage61_unauthorized', 'Invalid Garage61 API key. Check Settings → iRacing Stats.' );
		}

		if ( 404 === $code ) {
			return new WP_Error( 'garage61_not_found', 'Driver not found. Check your Driver ID in Settings → iRacing Stats.' );
		}

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'garage61_api_error', "Garage61 API returned HTTP {$code}." );
		}

		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'garage61_json_error', 'Could not parse Garage61 API response.' );
		}

		return $data;
	}
}
