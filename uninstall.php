<?php
/*
	Copyright (c) 2017 Krzysztof Grochocki

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

//Die if uninstall.php is not called by WordPress
if(!defined('WP_UNINSTALL_PLUGIN')) {
die;
}
//Remove settings options
delete_option('ejabat_hostname');
delete_option('ejabat_sender_email');
delete_option('ejabat_sender_name');
delete_option('ejabat_show_hints');
delete_option('ejabat_login_hint');
delete_option('ejabat_password_hint');
delete_option('ejabat_email_hint');
delete_option('ejabat_password_strength');
delete_option('ejabat_validator_pizza');
delete_option('ejabat_rest_url');
delete_option('ejabat_login');
delete_option('ejabat_password');
delete_option('ejabat_set_last');
delete_option('ejabat_rest_timeout');
delete_option('ejabat_rest_retry');
delete_option('ejabat_registration_hosts');
delete_option('ejabat_allowed_login_regexp');
delete_option('ejabat_blocked_login_regexp');
delete_option('ejabat_welcome_msg');
delete_option('ejabat_welcome_msg_subject');
delete_option('ejabat_welcome_msg_body');
delete_option('ejabat_watcher');
delete_option('ejabat_registration_timeout');
delete_option('ejabat_two_step_registration');
delete_option('ejabat_activation_timeout');
delete_option('ejabat_disable_registration');
delete_option('ejabat_change_email_timeout');
delete_option('ejabat_disable_change_email');
delete_option('ejabat_reset_pass_timeout');
delete_option('ejabat_reset_pass_limit_count');
delete_option('ejabat_reset_pass_limit_timeout');
delete_option('ejabat_disable_reset_pass');
delete_option('ejabat_delete_account_timeout');
delete_option('ejabat_disable_delete_account');
