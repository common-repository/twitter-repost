<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
include_once 'class.php';
$stream_tw = new TwitterStream();
$streams = $stream_tw->wp_cron_function($wpdb, PLUGIN_TABLE_NAME);
foreach ($streams as $stream) {
	$new_stream_data = $stream_tw->stream_get($stream->user, $stream->amount);
	if ($data_arr = $stream_tw->find_diff_btw_streams($new_stream_data, $stream, $wpdb)) {
		$stream_tw->post_from_cron_function($data_arr, unserialize($stream->category));
	}

}



