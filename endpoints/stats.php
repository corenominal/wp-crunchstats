<?php
/**
 * Return the stats
 */
function iewp_crunchstats_endpoint_stats( $request_data )
{
	global $wpdb;

	$data = $request_data->get_params();

	// Test API key
	if( !isset( $data['apikey'] ) || empty( $data['apikey'] ) )
		return array( 'Error' => 'Please provide a valid API key. e.g. "apikey=YOUR-API-KEY-HERE"' );

	$apikey = get_option( 'iewp_crunchstats_apikey', '' );

	if( $apikey != $data['apikey'] )
		return array( 'Error' => 'Invalid API key' );


	// Test report type
	if( !isset( $data['report'] ) || empty( $data['report'] ) )
		return array( 'Error' => 'Please specify a report type. e.g. "report=today-hour-by-hour"' );

	// Report switch
	switch ( $data['report'] )
	{
		// A hour by hour breakdown of today's hits
		case 'today-hour-by-hour':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%k') AS `hour`, COUNT(*) AS `total`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND FROM_UNIXTIME(`date`,'%Y-%m-%d') = '" . date( 'Y-m-d' ) . "'
					  GROUP BY `hour`
					  ORDER BY date ASC";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );

			break;

		// Today's hits
		case 'today-hits':
			$sql = "SELECT COUNT(*) AS `hits`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND FROM_UNIXTIME(`date`,'%Y-%m-%d') = '" . date( 'Y-m-d' ) . "'";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Today's new sessions
		case 'today-sessions':
			$sql = "SELECT COUNT(*) AS `sessions`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `new_session` = 1 AND FROM_UNIXTIME(`date`,'%Y-%m-%d') = '" . date( 'Y-m-d' ) . "'";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Hits for the last 7 days
		case 'hits-last-7-days':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%a') AS `day`, COUNT(*) AS `total`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `date` > " . strtotime('-1 week') . "
					  GROUP BY `day`
					  ORDER BY date DESC";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// News sessions for the last 7 days
		case 'sessions-last-7-days':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%a') AS `day`, COUNT(*) AS `total`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `new_session` = 1 AND `date` > " . strtotime('-1 week') . "
					  GROUP BY `day`
					  ORDER BY date DESC";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Recently Viewed - All
		case 'recently-viewed-content':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

        // Recently Viewed - Posts
		case 'recently-viewed-posts':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = 'post'
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

        // Recently Viewed - Pages
		case 'recently-viewed-pages':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = 'page'
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

        // Recently Viewed - Other
		case 'recently-viewed-other':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = 'other'
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;


		// Recent bot activity
		case 'recent-bot-activity':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 1
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Recent 404 errors
		case 'recent-404-errors':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = '404'
					  ORDER BY `date` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Recent 404 errors
		case 'common-404-errors':
			$sql = "SELECT COUNT(*) AS `total`, `guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = '404'
					  GROUP BY `guid`
					  ORDER BY `total` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Popular content
		case 'popular-content-all':
			$sql = "SELECT `title`,`name`, COUNT(*) AS `total`, `guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `name` != ''
					  GROUP BY `guid`
					  ORDER BY total DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Popular posts
		case 'popular-content-posts':
			$sql = "SELECT `title`,`name`, COUNT(*) AS `total`, `guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `name` != '' AND `content_type` = 'post'
					  GROUP BY `guid`
					  ORDER BY total DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Popular pages
		case 'popular-content-pages':
			$sql = "SELECT `title`,`name`, COUNT(*) AS `total`, `guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `name` != '' AND `content_type` = 'page'
					  GROUP BY `guid`
					  ORDER BY total DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Most common referers
		case 'referers-common':
			$sql = "SELECT `referer`, COUNT(*) AS `total`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `referer` != '' AND `referer` NOT LIKE '%" . site_url() . "%'
					  GROUP BY `referer`
					  ORDER BY total DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Most recent referers
		case 'referers-recent':
			$sql = "SELECT `date` AS `timestamp`, FROM_UNIXTIME(`date`,'%b %d, %H:%i %p') AS `date`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `referer` != '' AND `referer` NOT LIKE '%" . site_url() . "%'
					  ORDER BY `timestamp` DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Most common searches
		case 'searches-common':
			$sql = "SELECT COUNT(*) AS `total`, REPLACE(`search_string`, '?s=', '') AS `query`, `search_string`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `search_string` != '' AND `content_type` = 'search'
					  GROUP BY `query`
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		// Most recent searches
		case 'searches-recent':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`, `content_type`, REPLACE(`search_string`, '?s=', '') AS `query`, `search_string`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `search_string` != '' AND `content_type` = 'search'
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			break;

		default:
			return array( 'Error' => 'Unknown report type. Please specify a valid report type' );
			break;
	}

	// Remove API key and return report
	unset( $data['apikey'] );
	$data['num_rows'] = $wpdb->num_rows;
	return $data;

}
