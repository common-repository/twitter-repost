<?php
//
/* Class and const file */
//
//========================================================
global $wpdb;

define(PLUGIN_UPLOAD_DIR, 'twitter_repost_ni'); // Plugin dir constant
define(PLUGIN_TABLE_NAME, $wpdb->prefix.'twitter_repost_ni'); // Table name postfix

/**
 * STREAM class
 */
class TwitterStream
{
    private $plugin_upload_dir_full;
    private $plugin_upload_dir;

    /**
     * [__construct function]
     */
    public function __construct () {
        $this->plugin_upload_dir = wp_upload_dir();
        $this->plugin_upload_dir_full = $this->plugin_upload_dir["basedir"] . '/' . PLUGIN_UPLOAD_DIR;
    }

    /**
     * [curl description]
     * @param  string $url json url
     * @return json-object with twitter datas       
     */
	private function curl ($url) // using curl
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($c, CURLOPT_TIMEOUT, 5);
        return json_decode(curl_exec($c));
    }

    /**
     * [stream_get description]
     * @param  string $user   twitter username
     * @param  stinrg $amount amount of tweets
     * @return json-object    json object form twitter Search API
     */
    public function stream_get ($user, $amount) //get stream with posts data from twitter using Search API
    {
	  return $this->curl('http://search.twitter.com/search.json?q=from%3A'.$user.'&rpp='.$amount.'&result_type=recent&include_entities=1');
    }

    /**
     * [check_for_int description]
     * @param  string $amount amount of tweets
     * @return bool       
     */
    public function check_for_int ($amount) // Check for integer
    {
        if (!empty($amount))
        {
            $amount = intval($amount);
            if ($amount > 1)
            {
                return true;
            }
        }
    }

    /**
     * [get_categories_multi description]
     * @param  string $category name of category
     * @return string           select with categories
     */
    public function get_categories_multi ($category) //Get multicategories
    {
     $select_cats = wp_dropdown_categories( array( 'echo' => 0, 'hide_empty' => 0, 'name' => 'select_name', 'selected' => $category , 'hierarchical' => true ) );
     $select_cats = str_replace( 'name=\'select_name\'', 'name=\'select_name[]\' multiple="multiple"', $select_cats);
     if (!empty($category)) {
         foreach ($category as $cat) {
            $select_cats = str_replace( 'value="'.$cat.'"', 'value="'.$cat.'" selected', $select_cats);
         }
     }
     return $select_cats;
    }

    /**
     * [get_categories description]
     * @param  array $categories array with categories
     * @return string            string of categories
     */
    public function get_categories ($categories) //get categoies array with names
    {
	    if (!empty($categories)) {
		    $categories = unserialize($categories);
            foreach ($categories as $key => $category) {
                $categories_new[$category] = get_the_category_by_ID($category);
            }

		    return implode(", ", $categories_new);
	    }
	    else
	    	return null;
    }

    /**
     * [get_title_offlink description]
     * @param  string $title link title
     * @return string        title
     */
    private function get_title_offlink ($title) {
        return preg_replace('(((f|ht){1}(tp|tps)://)[\w\d\S]+)', '', $title);
    }

    /**
     * [post_from_stream description]
     * @param  json-object $data  data from Twitter's Search API engine
     * @param  array $categories categories
     * @param  string $title      title of the post message
     */
    public function post_from_stream ($data, $categories, $title) {

        if (!empty($data))
        {
            foreach ($data as $med) 
            {
                if (!empty($med->media_url)) {
                    $media_data = $med->media_url;
                }
                elseif (!empty($med->expanded_url)) {
                    $media_data = $med->expanded_url;
                }
                 if (preg_match('!http://[^?#]+\.(?:jpe?g|png|gif)!Ui', $media_data))
                 {
                    // Create post object
                    $twitter_post = array(
                       'post_title' => $this->get_title_offlink($title),
                       'post_content' => '<img src='.$this->get_the_image($media_data).' />',
                       'post_status' => 'publish',
                       'post_author' => 1,
                       'post_category' => $categories
                    );
                    // Insert the post into the database
                     wp_insert_post( $twitter_post );
                 }
            }
        }
    }

    /**
     * [post_from_cron_function description]
     * @param  array $data array of json objects
     * @param  array $categories array of categories
     */
    public function post_from_cron_function ($data, $categories) {

        if (!empty($data)) {
            foreach ($data as $stream_hs) {

                foreach ($stream_hs->entities->media as $media) {
                    if (!empty($media->media_url) ) {
                        $media_data = $media->media_url;
                    }
                    
                    elseif (!empty($media->expanded_url)) {
                        $media_data = $media->expanded_url;
                    }

                    $twitter_post = array(
                       'post_title' => $this->get_title_offlink($stream_hs->text),
                       'post_content' => '<img src='.$this->get_the_image($media_data).' />',
                       'post_status' => 'publish',
                       'post_author' => 1,
                       'post_category' => $categories
                    );
                    // Insert the post into the database
                     wp_insert_post( $twitter_post );
                }
            }
        }
    }

    /**
     * [get_the_image description]
     * @param  string $image_url image url
     * @return string            image url on server
     */
    private function get_the_image ($image_url) 
    {
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        $file = $this->plugin_upload_dir_full . '/' . $filename;
        file_put_contents($file, $image_data);
        return $this->plugin_upload_dir['baseurl'] . '/' . PLUGIN_UPLOAD_DIR . '/' . $filename;
    }

    /**
     * [get_streams description]
     * @param  database object $wpdb  object form databases
     * @param  string $table_name     table name constant
     */
    public function get_streams ($wpdb, $table_name) {
        return $wpdb->get_results("SELECT * FROM $table_name");
    }

    /**
     * [delete_stream description]
     * @param  database object $wpdb       object form database
     * @param  string $del_id     id of deleted post
     * @param  string $table_name constant of table name
     */
    public function delete_stream ($wpdb, $del_id, $table_name) {
        $del_id = wp_strip_all_tags(mysql_escape_string($del_id));
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name
                WHERE id = $del_id"
            )
        );
    }

    /**
     * [wp_cron_function description]
     * @param  database obj $wpdb  
     * @param  string $table_name 
     * @return array  array of rows form database
     */
    public function wp_cron_function ($wpdb, $table_name) {
        return $this->get_streams($wpdb, $table_name);    
    }

    /**
     * [find_diff_btw_streams description]
     * @param  json-obj $new_stream_data object from twitter Search API
     * @param  obj $old_stream_data      data serialized array from database
     * @return array                     difference btw new and old arrays with stream data
     */
    public function find_diff_btw_streams ($new_stream_data, $old_stream_data, $wpdb) {
        foreach ($new_stream_data->results as $new_data) {
            $raw_arr[] = $new_data->id_str; 
        }
        $old_arr = unserialize($old_stream_data->twitter_ids);
        $diff = array_diff($raw_arr, $old_arr);
        if ($diff !== false) {
            $this->update_id_column($wpdb, $old_stream_data->id, serialize($raw_arr));
            return $this->diff_data($new_stream_data->results, $diff);
        }
        
    }

    /**
     * [update_id_column description]
     * @param  obj $wpdb        database object
     * @param  string $row_id      row id
     * @param  string $twitter_ids serialized array of new ids
     */
    public function update_id_column ($wpdb, $row_id, $twitter_ids) {
        $wpdb->update( PLUGIN_TABLE_NAME, array('twitter_ids' => $twitter_ids), array("ID" => $row_id), $format = null, $where_format = null );
    }

    /**
     * [diff_data description]
     * @param  array $new_stream_data array with new data
     * @param  array $diff_arr        array with different ids
     * @return array                  with data from different ids
     */
    private function diff_data ($new_stream_data, $diff_arr) {
        foreach ($new_stream_data as $stream) {
            if (array_search($stream->id_str, $diff_arr) !== false) {
                $array_new_streams[] = $stream;
            }
        }
        return $array_new_streams;
    }
}