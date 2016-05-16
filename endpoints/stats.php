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
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Recently Viewed Content
		case 'recently-viewed-content':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Recent bot activity
		case 'recent-bot-activity':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 1 
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Recent 404 errors
		case 'recent-404-errors':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `content_type` = '404'
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
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
			$data['num_rows'] = $wpdb->num_rows;
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
			$data['num_rows'] = $wpdb->num_rows;
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
			$data['num_rows'] = $wpdb->num_rows;
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
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Most recent referers
		case 'referers-recent':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `referer` != '' AND `referer` NOT LIKE '%" . site_url() . "%'
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Most common searches
		case 'searches-common':
			$sql = "SELECT COUNT(*) AS `total`, REPLACE(`search_string`, '?s=', '') AS `query`, `search_string`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `search_string` != '' AND `content_type` = 'search'
					  GROUP BY `query`
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
			break;

		// Most recent searches
		case 'searches-recent':
			$sql = "SELECT FROM_UNIXTIME(`date`,'%Y-%m-%d %H:%i:%s') AS `date`, `content_type`, REPLACE(`search_string`, '?s=', '') AS `query`, `search_string`,`referer`,`title`,`guid`
					  FROM `iewp_crunchstats_log`
					  WHERE `is_bot` = 0 AND `search_string` != '' AND `content_type` = 'search'
					  ORDER BY date DESC
					  LIMIT 20";
			$data['report'] = $wpdb->get_results( $sql, ARRAY_A );
			$data['num_rows'] = $wpdb->num_rows;
			break;
		
		default:
			return array( 'Error' => 'Unknown report type. Please specify a valid report type' );
			break;
	}

	// Remove API key and return report
	unset( $data['apikey'] );
	return $data;

}