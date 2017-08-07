<?php
/*
	Copyright (c) 2016 Krzysztof Grochocki

	This file is part of Ejabberd Account Tools.

	Ejabberd Account Tools is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3, or
	(at your option) any later version.

	Ejabberd Account Tools is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with GNU Radio. If not, see <https://www.gnu.org/licenses/>.
*/

//Activation hook
function ejabat_activated() {
	//Add cron job
	wp_schedule_event(time(), 'daily', 'ejabat_cron');
}
register_activation_hook(EJABAT_DIR_PATH.'ejabat.php', 'ejabat_activated');

//Deactivation hook
function ejabat_deactivated() {
	//Remove cron job
	wp_clear_scheduled_hook('ejabat_cron');
}
register_deactivation_hook(EJABAT_DIR_PATH.'ejabat.php', 'ejabat_deactivated' );

//Removing expired transients cron job
function ejabat_cron() {
	//Get current time in UTC
	$time = current_time('timestamp', 1);
	//Find expired transients
	global $wpdb;
	$expired = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ejabat_%' AND option_value < $time");
	foreach($expired as $transient) {
		//Remove '_transient_timeout_' phrase
		$key = str_replace('_transient_timeout_', '', $transient);
		//Delete transient
		delete_transient($key);
	}
}
add_action('ejabat_cron', 'ejabat_cron');
