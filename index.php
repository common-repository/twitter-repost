<?php
//
/* Do view file) */
//========================
$table_name = PLUGIN_TABLE_NAME;
$stream = new TwitterStream();
if ($_POST['add'])
{
	if ($_POST['username'] && $stream->check_for_int($_POST['amount'])) {
		foreach ($_POST as $key => $value)
		{
			if (!is_array($value)) {
				$value = wp_strip_all_tags($value);
			}
		}
		extract($_POST);
		$json_data = $stream->stream_get($username, $amount);
		foreach ($json_data->results as $result)
		{
			$serialized[] = $result->id_str;
			if (!empty($result->entities->media)) {
				$stream->post_from_stream($result->entities->media, $select_name, $result->text);
			}
			elseif (!empty($result->entities->urls)) {
				$stream->post_from_stream($result->entities->urls, $select_name, $result->text);
			}
			
		}
		$serialized = serialize($serialized);
		$rows = $wpdb->insert ($table_name, array(
			  'user'     => $username,
			  'category'    => serialize($select_name),
			  'amount' => $amount,
			  'twitter_ids' => $serialized
		   )
		);
	}
}
if ($_GET['delete']) {
	$stream->delete_stream($wpdb, $_GET['delete'], $table_name);
}
$streams = $stream->get_streams($wpdb, $table_name);
include 'index.tmpl.php';
