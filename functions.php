<?php
//
/* Activation and deactivation functions */
//================================
function plugin_activate_ni () // Activation hook
{
	global $wpdb;
	$table_name = PLUGIN_TABLE_NAME;
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'")!=$table_name)
	{
		$sql = "CREATE TABLE $table_name
		  	(
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  user text DEFAULT '',
			  category text DEFAULT '',
			  amount int (10),
			  twitter_ids text DEFAULT '',
			  UNIQUE KEY id (id)
		    );";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	$plugin_upload_dir = wp_upload_dir();
	$plugin_upload_dir_full = $plugin_upload_dir["basedir"] . '/' . PLUGIN_UPLOAD_DIR;
	if ($plugin_upload_dir["basedir"] && !is_dir($plugin_upload_dir_full))
	{
		wp_mkdir_p($plugin_upload_dir_full);
	}
	wp_schedule_event( current_time( 'timestamp' ) + 60 , 'hourly', 'twitts_check');
}

function twitter_check_function() {
	$stream_tw = new TwitterStream();
	$streams = $stream_tw->wp_cron_function($wpdb, PLUGIN_TABLE_NAME);
	foreach ($streams as $stream) {
		$new_stream_data = $stream_tw->stream_get($stream->user, $stream->amount);
		if ($data_arr = $stream_tw->find_diff_btw_streams($new_stream_data, $stream, $wpdb)) {
			$stream_tw->post_from_cron_function($data_arr, unserialize($stream->category));
		}

	}
}


function plugin_deactivate_ni ()
{
	global $wpdb;
    $table_name = PLUGIN_TABLE_NAME;
    wp_clear_scheduled_hook('twitts_check');
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'")==$table_name)
    {
      $wpdb->query(
       	"DROP TABLE $table_name;"
      );
    }
}