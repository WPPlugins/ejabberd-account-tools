<?php
/*
Plugin Name: Ejabberd Account Tools
Plugin URI: https://beherit.pl/en/wordpress/plugins/ejabberd-account-tools/
Description: Provide ejabberd account tools such as the registration form, deleting the account, resetting the account password.
Version: 1.9
Author: Krzysztof Grochocki
Author URI: https://beherit.pl/
Text Domain: ejabberd-account-tools
Domain Path: /languages
License: GPLv3
*/

/*
	Copyright (c) 2015-2017 Krzysztof Grochocki

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

//Define plugin basename, dir path and dir url
define('EJABAT_BASENAME', plugin_basename(__FILE__));
define('EJABAT_DIR_PATH', plugin_dir_path(__FILE__));
define('EJABAT_DIR_URL', plugin_dir_url(__FILE__));

//Define plugin version variable
define('EJABAT_VERSION', '1.9');

//Load plugin translations
function ejabat_textdomain() {
	load_plugin_textdomain('ejabberd-account-tools', false, dirname(EJABAT_BASENAME).'/languages');
}
add_action('init', 'ejabat_textdomain');

//Include admin settings
include_once(EJABAT_DIR_PATH.'includes/admin.php');

//Include functions
include_once(EJABAT_DIR_PATH.'includes/functions.php');

//Include cron
include_once(EJABAT_DIR_PATH.'includes/cron.php');

//Include register shortcode
include_once(EJABAT_DIR_PATH.'includes/register.php');

//Include change email shortcode
include_once(EJABAT_DIR_PATH.'includes/change_email.php');

//Include delete account shortcode
include_once(EJABAT_DIR_PATH.'includes/delete_account.php');

//Include reset password shortcode
include_once(EJABAT_DIR_PATH.'includes/reset_password.php');
