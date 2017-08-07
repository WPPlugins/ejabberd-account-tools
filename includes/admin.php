<?php
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

//Admin init
function ejabat_register_settings() {
	//Register settings
	register_setting('ejabat_settings', 'ejabat_hostname', 'trim');
	register_setting('ejabat_settings', 'ejabat_sender_email', 'trim');
	register_setting('ejabat_settings', 'ejabat_sender_name', 'trim');
	register_setting('ejabat_settings', 'ejabat_show_hints', 'boolval');
	register_setting('ejabat_settings', 'ejabat_login_hint', 'trim');
	register_setting('ejabat_settings', 'ejabat_password_hint', 'trim');
	register_setting('ejabat_settings', 'ejabat_email_hint', 'trim');
	register_setting('ejabat_settings', 'ejabat_password_strength', 'intval');
	register_setting('ejabat_settings', 'ejabat_validator_pizza', 'boolval');
	register_setting('ejabat_settings', 'ejabat_rest_url', 'trim');
	register_setting('ejabat_settings', 'ejabat_login', 'trim');
	register_setting('ejabat_settings', 'ejabat_password', 'trim');
	register_setting('ejabat_settings', 'ejabat_set_last', 'boolval');
	register_setting('ejabat_settings', 'ejabat_rest_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_rest_retry', 'intval');
	register_setting('ejabat_settings', 'ejabat_registration_hosts', 'trim');
	register_setting('ejabat_settings', 'ejabat_allowed_login_regexp', 'trim');
	register_setting('ejabat_settings', 'ejabat_blocked_login_regexp', 'trim');
	register_setting('ejabat_settings', 'ejabat_welcome_msg', 'boolval');
	register_setting('ejabat_settings', 'ejabat_welcome_msg_subject', 'trim');
	register_setting('ejabat_settings', 'ejabat_welcome_msg_body', 'trim');
	register_setting('ejabat_settings', 'ejabat_watcher', 'trim');
	register_setting('ejabat_settings', 'ejabat_registration_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_two_step_registration', 'boolval');
	register_setting('ejabat_settings', 'ejabat_activation_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_disable_registration', 'boolval');
	register_setting('ejabat_settings', 'ejabat_change_email_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_disable_change_email', 'boolval');
	register_setting('ejabat_settings', 'ejabat_reset_pass_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_reset_pass_limit_count', 'intval');
	register_setting('ejabat_settings', 'ejabat_reset_pass_limit_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_disable_reset_pass', 'boolval');
	register_setting('ejabat_settings', 'ejabat_delete_account_timeout', 'intval');
	register_setting('ejabat_settings', 'ejabat_disable_delete_account', 'boolval');
	//Add link to the settings on plugins page
	add_filter('plugin_action_links_'.EJABAT_BASENAME, 'ejabat_plugin_action_links', 10, 2);
}
add_action('admin_init', 'ejabat_register_settings');

//Link to the settings on plugins page
function ejabat_plugin_action_links($links) {
	array_unshift($links, '<a href="admin.php?page=ejabat-settings">'.__('Settings', 'ejabberd-account-tools').'</a>');
	return $links;
}

//Create options menu
function ejabat_add_admin_menu() {
	//Add menu page
	add_menu_page(__('Ejabberd Account Tools', 'ejabberd-account-tools'), __('Ejabberd Account Tools', 'ejabberd-account-tools'), 'manage_options', 'ejabat-settings', '', 'dashicons-ejabberd');
	//Add icon CSS style
	add_action('admin_head', 'ejabat_admin_head_icon');
	//Add options page
	if($ejabat_settings_page_hook = add_submenu_page('ejabat-settings', __('Settings', 'ejabberd-account-tools'), __('Settings', 'ejabberd-account-tools'), 'manage_options', 'ejabat-settings', 'ejabat_settings_page')) {
		//Add page CSS style
		add_action('admin_head-'.$ejabat_settings_page_hook, 'ejabat_admin_head_page');
		//Add jQuery script
		add_action('admin_footer-'.$ejabat_settings_page_hook, 'ejabat_admin_footer');
	}
	//Add tools page
	if($ejabat_tools_page_hook = add_submenu_page('ejabat-settings', __('Tools', 'ejabberd-account-tools'), __('Tools', 'ejabberd-account-tools'), 'manage_options', 'ejabat-tools', 'ejabat_tools_page')) {
		//Add page CSS style
		add_action('admin_head-'.$ejabat_tools_page_hook, 'ejabat_admin_head_page');
	}
}
add_action('admin_menu', 'ejabat_add_admin_menu');

//Add jQuery script
function ejabat_admin_head_icon() { ?>
	<style>
		.dashicons-ejabberd:before{
		content: "\f339";
		transform:rotate(25deg);
		-webkit-transform:rotate(25deg);
		-moz-transform:rotate(25deg);
		-o-transform:rotate(25deg);
		}
	</style>
<?php }
function ejabat_admin_head_page() { ?>
	<style>
		.metabox-holder .postbox .hndle{
		cursor:default;
		}
		.postbox.opened .hndle, .postbox.closed .hndle{
		cursor:pointer;
		}
		#ejabat_review{
		background-color:#E39124;
		border:1px solid transparent;
		cursor:pointer;
		}
		#ejabat_review a{
		text-decoration:none;
		color:#444;
		}
		#ejabat_review h2{
		border-bottom:none;
		color:#FAEBD7;
		cursor:pointer;
		}
		#ejabat_review .inside{
		margin:0;
		}
	</style>
<?php }

//Add jQuery script
function ejabat_admin_footer() { ?>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			//Add toggles to postboxes
			$('.postbox.closed .handlediv').click(function() {
				$(this).parent().toggleClass('closed').toggleClass('opened');
			});
			$('.postbox.closed .hndle').click(function() {
				$(this).parent().toggleClass('closed').toggleClass('opened');
			});
		});
	</script>
<?php }

//Display error notices
function ejabat_admin_notices() {
	//Re-enable registration
	if(($_GET['page']=='ejabat-settings') && ($_GET['settings-updated']=='enable-registration')) {
		update_option('ejabat_disable_registration', false);
	}
	//Re-enable form to change private email address
	if(($_GET['page']=='ejabat-settings') && ($_GET['settings-updated']=='enable-change-email')) {
		update_option('ejabat_disable_change_email', false);
	}
	//Re-enable form to reset password
	if(($_GET['page']=='ejabat-settings') && ($_GET['settings-updated']=='enable-reset-pass')) {
		update_option('ejabat_disable_reset_pass', false);
	}
	//Re-enable form to delete account
	if(($_GET['page']=='ejabat-settings') && ($_GET['settings-updated']=='enable-delete-account')) {
		update_option('ejabat_disable_delete_account', false);
	}
	//Display error notice - registration
	if(get_option('ejabat_disable_registration', false)) {
		echo '<div id="setting-error-ejabat-registration" class="error settings-error notice"><p><strong>' . sprintf(__('Registration is temporarily disabled, click <a href="%s">here</a> to turn it on again.', 'ejabberd-account-tools'), admin_url('admin.php?page=ejabat-settings&settings-updated=enable-registration')) . '</strong></p></div>';
	}
	//Display error notice - form to change private email address
	if(get_option('ejabat_disable_change_email', false)) {
		echo '<div id="setting-error-ejabat-changing-email" class="error settings-error notice"><p><strong>' . sprintf(__('Form to change private email address is temporarily disabled, click <a href="%s">here</a> to turn it on again.', 'ejabberd-account-tools'), admin_url('admin.php?page=ejabat-settings&settings-updated=enable-change-email')) . '</strong></p></div>';
	}
	//Display error notice - form to reset password
	if(get_option('ejabat_disable_reset_pass', false)) {
		echo '<div id="setting-error-ejabat-resetting-password" class="error settings-error notice"><p><strong>' . sprintf(__('Form to reset password is temporarily disabled, click <a href="%s">here</a> to turn it on again.', 'ejabberd-account-tools'), admin_url('admin.php?page=ejabat-settings&settings-updated=enable-reset-pass')) . '</strong></p></div>';
	}
	//Display error notice - form to delete account
	if(get_option('ejabat_disable_delete_account', false)) {
		echo '<div id="setting-error-ejabat-deleting-account" class="error settings-error notice"><p><strong>' . sprintf(__('Form to delete account is temporarily disabled, click <a href="%s">here</a> to turn it on again.', 'ejabberd-account-tools'), admin_url('admin.php?page=ejabat-settings&settings-updated=enable-delete-account')) . '</strong></p></div>';
	}
}
add_action('admin_notices', 'ejabat_admin_notices');

//Display options page
function ejabat_settings_page() { ?>
	<div class="wrap">
		<h2><?php _e('Ejabberd Account Tools - Settings', 'ejabberd-account-tools'); ?></h2>
		<?php settings_errors(); ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables">
						<form id="ejabat-form" method="post" action="options.php">
							<?php settings_fields('ejabat_settings'); ?>
							<div class="postbox">
								<h2 class="hndle"><?php _e('General', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_hostname"><?php _e('Default hostname', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_hostname" id="ejabat_hostname" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])); ?>" /></label>
											</br><small><?php _e('Determines XMPP vhost name which will be default used in all forms.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_sender_email"><?php _e('Sender email address', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_sender_email" id="ejabat_sender_email" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_sender_email', get_option('admin_email')); ?>" /></label>
											</br><label for="ejabat_sender_name"><?php _e('Sender name', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_sender_name" id="ejabat_sender_name" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_sender_name', get_bloginfo()); ?>" /></label>
											</br><small><?php _e('It will be used in all email notification, eg. when resetting password or confirming new private email address.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_show_hints"><input name="ejabat_show_hints" id="ejabat_show_hints" type="checkbox" value="1" <?php checked(1, get_option('ejabat_show_hints', true)); ?> /><?php _e('Show information hints on forms', 'ejabberd-account-tools'); ?></label>
											</br><small><?php printf(__('To support multi-language use %s filter.', 'ejabberd-account-tools'), '<kbd style="font-size:smaller;">ejabat_hints_args</kbd>'); ?></small>
										</li>
										<li>
											<label for="ejabat_login_hint"><?php _e('Login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_login_hint" id="ejabat_login_hint" type="text" size="50" style="max-width:100%;" value="<?php echo get_option('ejabat_login_hint', __('At least 3 and up to 32 characters, only letters and numbers', 'ejabberd-account-tools')); ?>" /></label>
										</li>
										<li>
											<label for="ejabat_password_hint"><?php _e('Password', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_password_hint" id="ejabat_password_hint" type="text" size="50" style="max-width:100%;" value="<?php echo get_option('ejabat_password_hint', __('Required at least good password', 'ejabberd-account-tools')); ?>" /></label>
										</li>
										<li>
											<label for="ejabat_email_hint"><?php _e('Email', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_email_hint" id="ejabat_email_hint" type="text" size="50" style="max-width:100%;" value="<?php echo get_option('ejabat_email_hint', __('Required only for password recovery', 'ejabberd-account-tools')); ?>" /></label>
										</li>
										<li>
											<?php $ejabat_password_strength = get_option('ejabat_password_strength', 3); ?>
											<label for="ejabat_password_strength"><?php _e('Minimum required password strength', 'ejabberd-account-tools'); ?>:&nbsp;<select name="ejabat_password_strength" id="ejabat_password_strength"><option value="0" <?php selected($ejabat_password_strength, 0); ?>><?php _e('Disabled', 'ejabberd-account-tools'); ?></option><option value="1" <?php selected($ejabat_password_strength, 1); ?>><?php _e('Very weak', 'ejabberd-account-tools'); ?></option><option value="2" <?php selected($ejabat_password_strength, 2); ?>><?php _e('Weak', 'ejabberd-account-tools'); ?></option><option value="3" <?php selected($ejabat_password_strength, 3); ?>><?php _e('Good', 'ejabberd-account-tools'); ?></option><option value="4" <?php selected($ejabat_password_strength, 4); ?>><?php _e('Strong', 'ejabberd-account-tools'); ?></option></select></label>
										</li>
										<li>
											<label for="ejabat_validator_pizza"><input name="ejabat_validator_pizza" id="ejabat_validator_pizza" type="checkbox" value="1" <?php checked(1, get_option('ejabat_validator_pizza', true)); ?> /><?php _e('Block disposable email addresses in forms', 'ejabberd-account-tools'); ?></label>
											</br><small><?php printf(__('Use %s to validate email addresses.', 'ejabberd-account-tools'), '<a href="https://www.validator.pizza/" target="_blank">VALIDATOR.pizza</a>'); ?></small>
										</li>
									</ul>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><?php _e('ReST API', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_rest_url"><?php _e('ReST API url', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_rest_url" id="ejabat_rest_url" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_rest_url'); ?>" /></label>
											</br><small><?php _e('Enter URL defined for module mod_http_api in ejabberd settings.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<?php _e('Authorization', 'xmpp-statistics'); ?>
											</br><label for="ejabat_login"><?php _e('Login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_login" id="ejabat_login" type="text" size="25" style="max-width:100%;" value="<?php echo get_option('ejabat_login'); ?>" /></label>
											</br><label for="ejabat_password"><?php _e('Password', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_password" id="ejabat_password" type="password" size="25" style="max-width:100%;" value="<?php echo get_option('ejabat_password'); ?>" /></label>
											</br><small><?php _e('Authorization is required to connect with the ReST API.', 'xmpp-statistics'); ?></small>
										</li>
										<li>
											<label for="ejabat_set_last"><input name="ejabat_set_last" id="ejabat_set_last" type="checkbox" value="1" <?php checked(1, get_option('ejabat_set_last', false)); ?> /><?php _e('Set last activity information', 'ejabberd-account-tools'); ?></label>
										</li>
										<li>
											<label for="ejabat_rest_timeout"><?php _e('Connection timeout with ReST API', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_rest_timeout" id="ejabat_rest_timeout" type="number" min="5" size="4" value="<?php echo get_option('ejabat_rest_timeout', 5); ?>" />&nbsp;ms</label>
										</li>
										<li>
											<label for="ejabat_rest_retry"><?php _e('Connection retry limit with ReST API', 'ejabberd-account-tools'); ?>:&nbsp;<input type="number" min="3" size="4" name="ejabat_rest_retry" id="ejabat_rest_retry" value="<?php echo get_option('ejabat_rest_retry', 3); ?>" /></label>
										</li>
									</ul>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><?php _e('Registration', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_registration_hosts"><?php _e('Additional host names available in registration', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_registration_hosts" id="ejabat_registration_hosts" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_registration_hosts'); ?>" /></label>
											</br><small><?php _e('Fill only if you want to allow registration on other hosts than the default. Hosts must be separated by a space.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_allowed_login_regexp"><?php _e('Regexp for allowed login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_allowed_login_regexp" id="ejabat_allowed_login_regexp" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$'); ?>" /></label>
										</li>
										<li>
											<label for="ejabat_blocked_login_regexp"><?php _e('Regexp for blocked login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_blocked_login_regexp" id="ejabat_blocked_login_regexp" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_blocked_login_regexp', '^(.*(admin|blog|bot|contact|e-mail|ejabberd|email|ftp|hostmaster|http|https|imap|info|jabber|login|mail|office|owner|pop3|postmaster|root|smtp|ssh|support|team|webmaster|xmpp).*)$'); ?>" /></label>
										</li>
										<li>
											<label for="ejabat_welcome_msg"><input name="ejabat_welcome_msg" id="ejabat_welcome_msg" type="checkbox" value="1" <?php checked(1, get_option('ejabat_welcome_msg', false)); ?> /><?php _e('Send welcome message', 'ejabberd-account-tools'); ?></label>
											<ol>
												<label for="ejabat_welcome_msg_subject"><?php _e('Subject', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_welcome_msg_subject" id="ejabat_welcome_msg_subject" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_welcome_msg_subject'); ?>" /></label>
												</br><label for="ejabat_welcome_msg_body"><?php _e('Body', 'ejabberd-account-tools'); ?>:</br><textarea name="ejabat_welcome_msg_body" id="ejabat_welcome_msg_body" cols="65" rows="5" /><?php echo get_option('ejabat_welcome_msg_body'); ?></textarea></label>
												</br><small><?php printf(__('Plain text only, shortcodes allowed. To support multi-language use %s filter.', 'ejabberd-account-tools'), '<kbd style="font-size:smaller;">ejabat_welcome_msg_args</kbd>'); ?></small>
											</ol>
										</li>
										<li>
											<label for="ejabat_watcher"><?php _e('Registration watcher', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_watcher" id="ejabat_watcher" type="text" size="40" style="max-width:100%;" value="<?php echo get_option('ejabat_watcher'); ?>" /></label>
											</br><small><?php _e('Sends information about new registration to specified JID. Leave field empty if disabled.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_registration_timeout"><?php _e('Registration timeout', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_registration_timeout" id="ejabat_registration_timeout" type="number" min="0" max="86400" style="width: 5em;" value="<?php echo get_option('ejabat_registration_timeout', 3600); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Limits the frequency of registration from a given IP address. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_two_step_registration"><input name="ejabat_two_step_registration" id="ejabat_two_step_registration" type="checkbox" value="1" <?php checked(1, get_option('ejabat_two_step_registration', false)); ?> /><?php _e('Turn on the two-step registration', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Registration must be confirmed by activation link sent on private email address.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_activation_timeout"><?php _e('Validity period of the activation link', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_activation_timeout" id="ejabat_activation_timeout" type="number" min="0" max="86400" style="width: 5em;" value="<?php echo get_option('ejabat_activation_timeout', 3600); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Determines validity period of the activation link. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<label for="ejabat_disable_registration"><input name="ejabat_disable_registration" id="ejabat_disable_registration" type="checkbox" value="1" <?php checked(1, get_option('ejabat_disable_registration', false)); ?> /><?php _e('Temporarily disable the registration for not logged-in users', 'ejabberd-account-tools'); ?></label>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><?php _e('Changing email', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_change_email_timeout"><?php _e('Validity period of the confirmation link', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_change_email_timeout" id="ejabat_change_email_timeout" type="number" min="0" max="86400" style="width: 5em;" value="<?php echo get_option('ejabat_change_email_timeout', 900); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Determines validity period of the confirmation link. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<label for="ejabat_disable_change_email"><input name="ejabat_disable_change_email" id="ejabat_disable_change_email" type="checkbox" value="1" <?php checked(1, get_option('ejabat_disable_change_email', false)); ?> /><?php _e('Temporarily disable the form to change private email address for not logged-in users', 'ejabberd-account-tools'); ?></label>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><?php _e('Resetting password', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_reset_pass_timeout"><?php _e('Validity period of the confirmation link', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_reset_pass_timeout" id="ejabat_reset_pass_timeout" type="number" min="0" max="86400" style="width: 5em;" value="<?php echo get_option('ejabat_reset_pass_timeout', 900); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Determines validity period of the confirmation link. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
										<li>
											<label for="ejabat_reset_pass_limit_count"><?php _e('Limit verification to', 'ejabberd-account-tools'); ?>&nbsp;<input name="ejabat_reset_pass_limit_count" id="ejabat_reset_pass_limit_count" type="number" min="3" max="9" style="width: 3em;" value="<?php echo get_option('ejabat_reset_pass_limit_count', 4); ?>" />&nbsp;<?php _e('within', 'ejabberd-account-tools'); ?>&nbsp;<input type="number" min="0" max="86400" style="width: 5em;" name="ejabat_reset_pass_limit_timeout" value="<?php echo get_option('ejabat_reset_pass_limit_timeout', 43200); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Determines the maximum number of verification within the specified time. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<label for="ejabat_disable_reset_pass"><input name="ejabat_disable_reset_pass" id="ejabat_disable_reset_pass" type="checkbox" value="1" <?php checked(1, get_option('ejabat_disable_reset_pass', false)); ?> /><?php _e('Temporarily disable the form to reset password for not logged-in users', 'ejabberd-account-tools'); ?></label>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><?php _e('Deleting account', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="ejabat_delete_account_timeout"><?php _e('Validity period of the confirmation link', 'ejabberd-account-tools'); ?>:&nbsp;<input name="ejabat_delete_account_timeout" id="ejabat_delete_account_timeout" type="number" min="0" max="86400" style="width: 5em;" value="<?php echo get_option('ejabat_delete_account_timeout', 900); ?>" />&nbsp;<?php _e('seconds', 'ejabberd-account-tools'); ?></label>
											</br><small><?php _e('Determines validity period of the confirmation link. To disable this limitation enter 0.', 'ejabberd-account-tools'); ?></small>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<label for="ejabat_disable_delete_account"><input name="ejabat_disable_delete_account" id="ejabat_disable_delete_account" type="checkbox" value="1" <?php checked(1, get_option('ejabat_disable_delete_account', false)); ?> /><?php _e('Temporarily disable the form to delete account for not logged-in users', 'ejabberd-account-tools'); ?></label>
								</div>
							</div>
							<?php submit_button(__('Save settings', 'ejabberd-account-tools'), 'primary', 'submit', false); ?>
						</form>
					</div>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables">
						<div class="postbox" id="ejabat_review">
							<a href="https://wordpress.org/support/plugin/ejabberd-account-tools/reviews/?rate=5#new-post" target="_blank">
								<h2 class="hndle"><span class="dashicons dashicons-star-empty"></span>&nbsp;<?php _e('Rate plugin', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<?php _e('If you like this plugin, please give it a nice review', 'ejabberd-account-tools'); ?>
								</div>
							</a>
						</div>
						<div class="postbox">
							<h2 class="hndle"><?php _e('Donations', 'ejabberd-account-tools'); ?></h2>
							<div class="inside">
								<p><?php _e('If you like this plugin, please send a donation to support its development and maintenance', 'ejabberd-account-tools'); ?></p>
								<p style="text-align:center; height:50px;"><a href="https://beherit.pl/donate/ejabberd-account-tools/" style="display: inline-block;"><img src="<?php echo EJABAT_DIR_URL; ?>img/paypal.png" style="height:50px;"></a></p>
							</div>
						</div>
						<div class="postbox closed">
							<button type="button" class="handlediv button-link" aria-expanded="true"><?php /*<span class="screen-reader-text">Przełącz panel: Informacje o użytkowaniu</span>*/ ?><span class="toggle-indicator" aria-hidden="true"></span></button>
							<h2 class="hndle"><?php _e('Usage information', 'ejabberd-account-tools'); ?></h2>
							<div class="inside">
								<p><?php printf(__('Make sure that you have the latest version of ejabberd - plugin requires at least ejabberd %s.', 'ejabberd-account-tools'), '16.04'); ?></p>
								<p><?php _e('Check that module mod_http_api in ejabberd is properly configured. Example configuration:', 'ejabberd-account-tools'); ?></p>
<pre style="overflow-x:auto;">listen:
  - ip: "::"
    port: 5285
    module: ejabberd_http
    request_handlers:
      "/api": mod_http_api

acl:
  rest:
    user:
      - "bot": "<?php echo preg_replace('/^www\./','',$_SERVER['SERVER_NAME']); ?>"

access:
  rest:
    rest: allow

commands_admin_access: rest
commands:
  - add_commands:
    - change_password
    - check_account
    - check_password
    - private_get
    - private_set
    - register
    - send_message
    - set_last
    - unregister</pre>
								<p><?php _e('Then configure ReST API url and authorization data, finally put shortcodes on some page.', 'ejabberd-account-tools'); ?></p>
								<ul>
									<li><b>[ejabat_register]</b></br><?php _e('Form to register a new account.', 'ejabberd-account-tools'); ?></br></li>
									<li><b>[ejabat_change_email]</b></br><?php _e('Form to change / add private email address.', 'ejabberd-account-tools'); ?></br></li>
									<li><b>[ejabat_reset_password]</b></br><?php _e('Form to reset account password.', 'ejabberd-account-tools'); ?></br></li>
									<li><b>[ejabat_delete_account]</b></br><?php _e('Form to delete account.', 'ejabberd-account-tools'); ?></br></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }

//Display tools page
function ejabat_tools_page() { ?>
	<div class="wrap">
		<h2><?php _e('Ejabberd Account Tools - Tools', 'ejabberd-account-tools'); ?></h2>
		<?php //Verify POST and nonce
		if(isset($_POST['change_email']) && wp_verify_nonce($_POST['_ejabat_nonce'], 'ejabat_tools_private_email')) {
			//Try change email
			$callback = ejabat_change_email_admin_callback();
			//Show message
			if($callback['status']=='success') {
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
			else {
				echo '<div class="error settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
		}
		else if(isset($_POST['check_email']) && wp_verify_nonce($_POST['_ejabat_nonce'], 'ejabat_tools_private_email')) {
			//Try change email
			$callback = ejabat_check_email_admin_callback();
			//Show message
			if($callback['status']=='success') {
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
			else {
				echo '<div class="error settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
		}
		else if(isset($_POST['send_message']) && wp_verify_nonce($_POST['_ejabat_nonce'], 'ejabat_tools_send_message')) {
			//Try change email
			$callback = ejabat_send_message_admin_callback();
			//Show message
			if($callback['status']=='success') {
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
			else {
				echo '<div class="error settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong>'.$callback['message'].'</strong></p></div>';
			}
		} ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-2" class="postbox-container meta-box-sortables">
					<div id="normal-sortables" class="meta-box-sortables">
						<form id="ejabat_change_email" method="post">
							<div class="postbox">
								<h2 class="hndle"><?php _e('Private email address', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<p><?php _e('Here you can change or delete the private email address for the specified XMPP account.', 'ejabberd-account-tools'); ?></p>
									<ul>
										<li>
											<label for="login"><?php _e('Login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="login" id="login" type="text" size="25" style="max-width:100%;"  value="<?php echo stripslashes_deep($_POST['login']); ?>" /></label>
										</li>
										<li>
											<label for="email"><?php _e('Email', 'ejabberd-account-tools'); ?>:&nbsp;<input name="email" id="email" type="text" size="25" style="max-width:100%;" value="<?php echo stripslashes_deep($_POST['email']); ?>" /></label>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<?php wp_nonce_field('ejabat_tools_private_email', '_ejabat_nonce', false); ?>
									<input name="change_email" id="change_email" type="submit" class="button button-primary" value="<?php _e('Change email', 'ejabberd-account-tools'); ?>" />
									<input name="check_email" id="check_email" type="submit" class="button button-secondary" value="<?php _e('Check email', 'ejabberd-account-tools'); ?>" />
								</div>
							</div>
						</form>
						<form id="ejabat_send_message" method="post">
							<div class="postbox">
								<h2 class="hndle"><?php _e('Send message', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<ul>
										<li>
											<label for="msg_login"><?php _e('Login', 'ejabberd-account-tools'); ?>:&nbsp;<input name="login" id="msg_login" type="text" size="25" style="max-width:100%;" value="<?php echo stripslashes_deep($_POST['login']); ?>" /></label>
										</li>
										<li>
											<label for="subject"><?php _e('Subject', 'ejabberd-account-tools'); ?>:&nbsp;<input name="subject" id="subject" type="text" size="25" style="max-width:100%;" value="<?php echo stripslashes_deep($_POST['subject']); ?>" /></label>
											</br><label for="body"><?php _e('Body', 'ejabberd-account-tools'); ?>:</br><textarea name="body" id="body" style="vertical-align: baseline;" cols="50" rows="5" /><?php echo stripslashes_deep($_POST['body']); ?></textarea>
										</li>
									</ul>
								</div>
								<div id="major-publishing-actions">
									<?php wp_nonce_field('ejabat_tools_send_message', '_ejabat_nonce', false); ?>
									<input id="send_message" name="send_message" type="submit" class="button button-primary" value="<?php _e('Send', 'ejabberd-account-tools'); ?>" />
								</div>
							</div>
						</form>
					</div>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables">
						<div class="postbox" id="ejabat_review">
							<a href="https://wordpress.org/support/plugin/ejabberd-account-tools/reviews/?rate=5#new-post" target="_blank">
								<h2 class="hndle"><span class="dashicons dashicons-star-empty"></span>&nbsp;<?php _e('Rate plugin', 'ejabberd-account-tools'); ?></h2>
								<div class="inside">
									<?php _e('If you like this plugin, please give it a nice review', 'ejabberd-account-tools'); ?>
								</div>
							</a>
						</div>
						<div class="postbox">
							<h2 class="hndle"><?php _e('Donations', 'ejabberd-account-tools'); ?></h2>
							<div class="inside">
								<p><?php _e('If you like this plugin, please send a donation to support its development and maintenance', 'ejabberd-account-tools'); ?></p>
								<p style="text-align:center; height:50px;"><a href="https://beherit.pl/donate/ejabberd-account-tools/" style="display: inline-block;"><img src="<?php echo EJABAT_DIR_URL; ?>img/paypal.png" style="height:50px;"></a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }
