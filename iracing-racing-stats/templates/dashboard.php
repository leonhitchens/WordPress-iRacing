<?php
/**
 * Dashboard template.
 *
 * Available variables (from $data array):
 *   $data['profile']         — driver profile (name, irating, safety_rating, license_class, member_since)
 *   $data['career']          — career stats (starts, wins, top5, laps, win_pct, avg_finish)
 *   $data['current_season']  — current season (series_name, division, starts, wins, points)
 *   $data['recent_races']    — array of race results
 *   $data['irating_history'] — array of { date, irating }
 *
 * NOTE: Keys in $data match what the Garage61 API actually returns.
 * Update the esc_html() calls below once endpoint shapes are confirmed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$profile        = $data['profile']         ?? array();
$career         = $data['career']          ?? array();
$current_season = $data['current_season']  ?? array();
$recent_races   = $data['recent_races']    ?? array();
$irating_history = $data['irating_history'] ?? array();

// Helper: safely get a value from the API data.
function iracing_get( array $arr, string $key, string $default = '—' ): string {
	return isset( $arr[ $key ] ) ? esc_html( (string) $arr[ $key ] ) : esc_html( $default );
}

$license_class = iracing_get( $profile, 'license_class', 'R' );
$license_color_map = array(
	'R' => '#c00',
	'D' => '#f90',
	'C' => '#fc0',
	'B' => '#0a0',
	'A' => '#07c',
	'P' => '#639',
	'WC' => '#c8a000',
);
$license_color = $license_color_map[ $license_class ] ?? '#666';

// Pass iRating history to JS via wp_localize_script (already called in main plugin file).
// We output the data inline here so the chart JS can pick it up.
$chart_data = array();
if ( ! empty( $irating_history ) ) {
	foreach ( $irating_history as $point ) {
		$chart_data[] = array(
			'x' => isset( $point['date'] ) ? esc_js( $point['date'] ) : '',
			'y' => isset( $point['irating'] ) ? (int) $point['irating'] : 0,
		);
	}
}
$chart_json = wp_json_encode( $chart_data );
$chart_id   = 'iracing-chart-' . wp_rand( 1000, 9999 );
?>

<div class="iracing-dashboard" data-chart-id="<?php echo esc_attr( $chart_id ); ?>">

	<!-- ── Driver Header ── -->
	<div class="iracing-header">
		<div class="iracing-header__name">
			<?php echo iracing_get( $profile, 'display_name', 'Driver' ); ?>
		</div>
		<div class="iracing-header__badges">
			<span class="iracing-badge iracing-badge--license" style="--license-color: <?php echo esc_attr( $license_color ); ?>">
				<?php echo esc_html( $license_class ); ?>
				<?php echo iracing_get( $profile, 'safety_rating', '' ); ?>
			</span>
			<span class="iracing-badge iracing-badge--irating">
				<?php echo iracing_get( $profile, 'irating', '0' ); ?> iR
			</span>
		</div>
		<?php if ( ! empty( $profile['member_since'] ) ) : ?>
			<div class="iracing-header__since">
				Member since <?php echo iracing_get( $profile, 'member_since' ); ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- ── Career Stat Cards ── -->
	<div class="iracing-stats-grid">
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'starts', '0' ); ?></div>
			<div class="iracing-stat-card__label">Starts</div>
		</div>
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'wins', '0' ); ?></div>
			<div class="iracing-stat-card__label">Wins</div>
		</div>
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'top5', '0' ); ?></div>
			<div class="iracing-stat-card__label">Top 5s</div>
		</div>
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'laps', '0' ); ?></div>
			<div class="iracing-stat-card__label">Laps</div>
		</div>
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'win_pct', '0%' ); ?></div>
			<div class="iracing-stat-card__label">Win %</div>
		</div>
		<div class="iracing-stat-card">
			<div class="iracing-stat-card__value"><?php echo iracing_get( $career, 'avg_finish', '—' ); ?></div>
			<div class="iracing-stat-card__label">Avg Finish</div>
		</div>
	</div>

	<!-- ── iRating History Chart ── -->
	<?php if ( ! empty( $chart_data ) ) : ?>
	<div class="iracing-section">
		<h3 class="iracing-section__title">iRating History</h3>
		<div class="iracing-chart-wrap">
			<canvas id="<?php echo esc_attr( $chart_id ); ?>" aria-label="iRating history chart"></canvas>
		</div>
		<script>
			( function () {
				var data = <?php echo $chart_json; // phpcs:ignore WordPress.Security.EscapeOutput -- json_encode produces safe JSON ?>;
				window.iRacingCharts = window.iRacingCharts || [];
				window.iRacingCharts.push( { id: <?php echo wp_json_encode( $chart_id ); ?>, data: data } );
			} )();
		</script>
	</div>
	<?php endif; ?>

	<!-- ── Current Season ── -->
	<?php if ( ! empty( $current_season ) ) : ?>
	<div class="iracing-section">
		<h3 class="iracing-section__title">Current Season</h3>
		<div class="iracing-season">
			<div class="iracing-season__series"><?php echo iracing_get( $current_season, 'series_name' ); ?></div>
			<div class="iracing-season__stats">
				<span><strong><?php echo iracing_get( $current_season, 'starts', '0' ); ?></strong> starts</span>
				<span><strong><?php echo iracing_get( $current_season, 'wins', '0' ); ?></strong> wins</span>
				<span><strong><?php echo iracing_get( $current_season, 'points', '0' ); ?></strong> pts</span>
				<?php if ( ! empty( $current_season['division'] ) ) : ?>
					<span>Division <?php echo iracing_get( $current_season, 'division' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- ── Recent Races ── -->
	<?php if ( ! empty( $recent_races ) ) : ?>
	<div class="iracing-section">
		<h3 class="iracing-section__title">Recent Races</h3>
		<div class="iracing-table-wrap">
			<table class="iracing-table">
				<thead>
					<tr>
						<th>Date</th>
						<th>Series</th>
						<th>Track</th>
						<th>Start</th>
						<th>Finish</th>
						<th>iRating</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $recent_races as $race ) :
					$irating_change = isset( $race['irating_change'] ) ? (int) $race['irating_change'] : 0;
					$change_class   = $irating_change > 0 ? 'iracing-irating--up' : ( $irating_change < 0 ? 'iracing-irating--down' : '' );
					$change_prefix  = $irating_change > 0 ? '+' : '';
				?>
					<tr>
						<td><?php echo iracing_get( $race, 'date' ); ?></td>
						<td><?php echo iracing_get( $race, 'series_name' ); ?></td>
						<td><?php echo iracing_get( $race, 'track_name' ); ?></td>
						<td><?php echo iracing_get( $race, 'start_position' ); ?></td>
						<td><?php echo iracing_get( $race, 'finish_position' ); ?></td>
						<td>
							<span class="iracing-irating-change <?php echo esc_attr( $change_class ); ?>">
								<?php echo esc_html( $change_prefix . $irating_change ); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php endif; ?>

</div><!-- .iracing-dashboard -->
