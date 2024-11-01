<?php
/*
Plugin Name: Twitter repost media
Plugin URI: http://cetera.ru
Description: Plugin for posting pictures from twitter messages to the wordpress posts.
Version: 1.0
Author: Cetera Labs
Author URI: http://cetera.ru
License: GPL2

Copyright 2012  Cetera Labs  (email : support@ceteralabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
add_action('admin_menu', 'register_ni_twitter_plugin');
include_once 'class.php';
include_once 'functions.php';
register_activation_hook( __FILE__, 'plugin_activate_ni');
add_action('twitts_check', 'twitter_check_function');
register_deactivation_hook( __FILE__, 'plugin_deactivate_ni');
function register_ni_twitter_plugin() {
   add_menu_page('Twitter photo repost', 'Twitter photo repost', 'add_users', 'twitter_repost/index.php', '', '');
}