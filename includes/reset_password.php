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

//Enqueue styles & scripts
function ejabat_enqueue_reset_password_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_reset_password')) {
		$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
		//Get hints args
		$show_hints = get_option('ejabat_show_hints', true);
		if($show_hints) {
			$hints = apply_filters('ejabat_hints_args', array(
				'password' => get_option('ejabat_password_hint', __('Required at least good password', 'ejabberd-account-tools'))
			));
		}
		//Enqueue styles
		wp_enqueue_style('loaders', EJABAT_DIR_URL.'css/loaders'.$min.'.css', array(), '0.1.2', 'all');
		wp_enqueue_style('ejabat', EJABAT_DIR_URL.'css/style'.$min.'.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', EJABAT_DIR_URL.'css/font-awesome'.$min.'.css', array(), '4.7.0', 'all');
		wp_enqueue_style('hint', EJABAT_DIR_URL.'css/hint'.$min.'.css', array(), '2.4.1', 'all');
		//Enqueue scripts
		if($show_hints) {
			wp_enqueue_script('ejabat-hints', EJABAT_DIR_URL.'js/jquery.ejabat.hints'.$min.'.js', array('jquery'), EJABAT_VERSION, true);
			wp_localize_script('ejabat-hints', 'ejabat_hints', array(
				'password' => $hints['password'],
				'password_strength' => get_option('ejabat_password_strength', 3)
			));
		}
		wp_enqueue_script('ejabat-reset-password', EJABAT_DIR_URL.'js/jquery.ejabat.reset-password'.$min.'.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-reset-password', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'timeout' => (get_option('ejabat_rest_timeout', 5)*get_option('ejabat_rest_retry', 3)+5)*1000,
			'password_strength' => get_option('ejabat_password_strength', 3),
			'password_too_weak' => __('Password is too weak.', 'ejabberd-account-tools'),
			'password_very_weak' => __('Password is very weak.', 'ejabberd-account-tools'),
			'password_weak' => __('Password is weak.', 'ejabberd-account-tools'),
			'password_good' => __('Password is good.', 'ejabberd-account-tools'),
			'password_strong' => __('Password is strong.', 'ejabberd-account-tools'),
			'passwords_mismatch' => __('Password mismatch with the confirmation.', 'ejabberd-account-tools'),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabberd-account-tools'),
			'empty_field' => __('Please fill the required field.', 'ejabberd-account-tools'),
			'empty_fields' => __('Verification errors occurred. Please check all fields and submit it again.', 'ejabberd-account-tools'),
			'error' => __('Unexpected error occurred, try again.', 'ejabberd-account-tools'),
			'form_error' => '<div class="ejabat-info ejabat-no-margin ejabat-form-error">'.__('Unexpected error occurred, try again.', 'ejabberd-account-tools').'</div>'
		));
		wp_enqueue_script('zxcvbn-async');
		wp_enqueue_script('password-strength-meter');
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_reset_password_scripts');

//Reset password shortcode
function ejabat_reset_password_shortcode() {
	return '<div id="ejabat_reset_password_form" class="loader-inner line-scale" title="'.__('Loading', 'ejabberd-account-tools').'..."><div></div><div></div><div></div><div></div><div></div></div>';
}

//Reset password form
function ajax_ejabat_reset_password_form() {
	//Form is disabled
	if(get_option('ejabat_disable_reset_pass', false) && !is_user_logged_in()) {
		$html = '<div class="ejabat-info ejabat-no-margin ejabat-form-error">'.__('Form to reset password is temporarily disabled, please try again later.', 'ejabberd-account-tools').'</div>';
	}
	else {
		//Change request uri to JS referer
		$_SERVER['REQUEST_URI'] = sanitize_text_field($_POST['referer']);
		//Default response
		$response = '<div id="response" class="ejabat-display-none"></div>';
		//Get recaptcha
		$recaptcha_html = apply_filters('recaptcha_html','');
		//Get code parameter
		$code = sanitize_text_field($_POST['code']);
		//Link to reset password
		if($code != 'undefined') {
			//Code valid
			if(true == ($data = get_transient('ejabat_pass_'.$code))) {
				//Get data
				$login = $data['login'];
				$host = $data['host'];
				//Create form
				$html = '<form id="ejabat_change_password" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
					<div id="login">
						<input type="text" name="login" value="'.$login.'@'.$host.'" disabled>
						<span class="tip"></span>
					</div>
					<div id="password" class="hints">
						<input type="password" name="password" placeholder="'.__('Password', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
						<span class="tip"></span>
					</div>
					<div id="password_retyped">
						<input type="password" name="password_retyped" placeholder="'.__('Confirm password', 'ejabberd-account-tools').'">
						<span class="tip"></span>
					</div>
					'.$recaptcha_html.'
					<span id="recaptcha" class="recaptcha tip"></span>
					<div id="submit">
						<input type="hidden" name="action" value="ejabat_change_password">
						<input type="hidden" name="code" value="'.$code.'">
						'.wp_nonce_field('ajax_ejabat_reset_password', '_ejabat_nonce', true, false).'
						<input type="submit" value="'.__('Set new password', 'ejabberd-account-tools').'" id="ejabat_change_password_button">
						<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
					</div>
					'.$response.'
				</form>';
				wp_send_json(array('data' => $html));
			}
			//Code expired or not valid
			else {
				//Delete transient
				delete_transient('ejabat_pass_'.$code);
				//Response with error
				$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to reset password has expired or is not valid. Please fill the form and submit it again.', 'ejabberd-account-tools').'</div>';
			}
		}
		//Create form
		$html = '<form id="ejabat_reset_password" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
			<div id="login">
				<input type="text" name="login" placeholder="'.__('Login', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
				<span class="tip"></span>
			</div>
			'.$recaptcha_html.'
			<span id="recaptcha" class="recaptcha tip"></span>
			<div id="submit">
				<input type="hidden" name="action" value="ejabat_reset_password">
				'.wp_nonce_field('ajax_ejabat_reset_password', '_ejabat_nonce', true, false).'
				<input type="submit" value="'.__('Reset password', 'ejabberd-account-tools').'" id="ejabat_reset_password_button">
				<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
			</div>
			'.$response.'
		</form>';
	}
	wp_send_json(array('data' => $html));
}
add_action('wp_ajax_ejabat_reset_password_form', 'ajax_ejabat_reset_password_form');
add_action('wp_ajax_nopriv_ejabat_reset_password_form', 'ajax_ejabat_reset_password_form');

//Reset password - form callback
function ajax_ejabat_reset_password_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['_ejabat_nonce']), 'ajax_ejabat_reset_password') || !check_ajax_referer('ajax_ejabat_reset_password', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabberd-account-tools');
	}
	else {
		//Verify fields
		if(empty($_POST['login'])) {
			$status = 'blocked';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabberd-account-tools');
		}
		else {
			//Verify recaptcha
			$recaptcha_valid = apply_filters('recaptcha_valid', null);
			if(!$recaptcha_valid) {
				$status = 'blocked';
				$message = __('Captcha validation error, try again.', 'ejabberd-account-tools');
			}
			else {
				//Check login
				list($login, $host) = array_pad(explode('@', stripslashes_deep(sanitize_text_field($_POST['login'])), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
				$response = ejabat_get_xmpp_data('check_account', array('user' => $login, 'host' => $host));
				//Server unavailable
				if(is_null($response)) {
					$status = 'error';
					$message = __('Server is temporarily unavailable.', 'ejabberd-account-tools');
				}
				//User not found
				else if($response['code'] == 1) {
					$status = 'blocked';
					$message = __('Invalid login, correct it and try again.', 'ejabberd-account-tools');
				}
				//User found
				else if($response['code'] == 0) {
					//Get private email address
					$response = ejabat_get_xmpp_data('private_get', array('user' => $login, 'host' => $host, 'element' => 'private', 'ns' => 'email'));
					//Server unavailable
					if(is_null($response)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable.', 'ejabberd-account-tools');
					}
					//Check response
					else if($response['code'] == 0) {
						//Private email set
						if(preg_match("/<private xmlns='email'>(.*)?<\/private>/", $response['body'], $matches)) {
							//Check verification limit transient
							if(true == ($data = get_transient('ejabat_pass_'.$login.'@'.$host))) {
								$count = $data['count'];
							}
							//Verification limit is not exceeded
							if($count < get_option('ejabat_reset_pass_limit_count', 4)) {
								//Get current timestamp
								$now = current_time('timestamp', 1);
								//Set verification limit transient
								$data = array('timestamp' => $now, 'ip' => $_SERVER['REMOTE_ADDR'], 'count' => $count + 1);
								set_transient('ejabat_pass_'.$login.'@'.$host, $data, get_option('ejabat_reset_pass_limit_timeout', 43200));
								//Set code transient
								$code = bin2hex(openssl_random_pseudo_bytes(16));
								$data = array('timestamp' => $now, 'ip' => $_SERVER['REMOTE_ADDR'], 'login' => $login, 'host' => $host, 'email' => $matches[1]);
								set_transient('ejabat_pass_'.$code, $data, get_option('ejabat_reset_pass_timeout', 900));
								//Email data
								$subject = sprintf(__('Password reset for your %s account', 'ejabberd-account-tools'), $host);
								$body = sprintf(__('Hey %s,<br><br>Someone requested to change the password for your XMPP account %s. To complete the change, please click the following link:<br><br>%s<br><br>If you haven\'t made this change, simply disregard this email.<br><br>Greetings,<br>%s', 'ejabberd-account-tools'), $login, $login.'@'.$host, '<a href="'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'">'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'</a>', get_option('ejabat_sender_name', get_bloginfo()));
								$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', get_option('admin_email')).'>';
								$headers[] = 'Content-Type: text/html; charset=UTF-8';
								//Try send email
								if(wp_mail($login.' <'.$matches[1].'>', $subject, $body, $headers)) {
									$status = 'success';
									$message = sprintf(__('An email has been sent to you at address %s. It contains a link to a page where you can reset your password.', 'ejabberd-account-tools'), mask_email($matches[1]));
								}
								//Problem with sending email
								else {
									//Delete code transient
									delete_transient('ejabat_pass_'.$code);
									//Error message
									$status = 'error';
									$message = __('Failed to send email, try again.', 'ejabberd-account-tools');
								}
							}
							//Verification limit exceeded
							else {
								$status = 'blocked';
								$message = __('Verification limit has been exceeded, please try again later.', 'ejabberd-account-tools');
							}
						}
						//Private email not set
						else {
							$status = 'error';
							$message = __('Private email address hasn\'t been set. To reset your password please contact with the administrator.', 'ejabberd-account-tools');
						}
					}
					//Unexpected error
					else {
						$status = 'error';
						$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
					}
				}
				//Unexpected error
				else {
					$status = 'error';
					$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
				}
			}
		}
	}
	//Return response
	wp_send_json(array('status' => $status, 'message' => $message));
}
add_action('wp_ajax_ejabat_reset_password', 'ajax_ejabat_reset_password_callback');
add_action('wp_ajax_nopriv_ejabat_reset_password', 'ajax_ejabat_reset_password_callback');

//Change password - form callback
function ajax_ejabat_change_password_callback() {
	//Verify nonce
	if(!isset($_POST['code']) || !isset($_POST['_ejabat_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['_ejabat_nonce']), 'ajax_ejabat_reset_password') || !check_ajax_referer('ajax_ejabat_reset_password', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabberd-account-tools');
	}
	else {
		//Verify fields
		if(empty($_POST['password']) || empty($_POST['password_retyped'])) {
			$status = 'blocked';
			$message = __('All fields are required. Please check the form and submit it again.', 'ejabberd-account-tools');
		}
		else {
			//Verify recaptcha
			$recaptcha_valid = apply_filters('recaptcha_valid', null);
			if(!$recaptcha_valid) {
				$status = 'blocked';
				$message = __('Captcha validation error, try again.', 'ejabberd-account-tools');
			}
			else {
				//Verify passwords
				$password = stripslashes_deep(sanitize_text_field($_POST['password']));
				$password_retyped = stripslashes_deep(sanitize_text_field($_POST['password_retyped']));
				if($password != $password_retyped) {
					$status = 'blocked';
					$message = __('Passwords don\'t match, correct them and try again.', 'ejabberd-account-tools');
				}
				else {
					//Get code transient
					$code = sanitize_text_field($_POST['code']);
					//Code valid
					if(true == ($data = get_transient('ejabat_pass_'.$code))) {
						//Get data
						$login = $data['login'];
						$host = $data['host'];
						//Try set new password
						$response = ejabat_get_xmpp_data('change_password', array('user' => $login, 'host' => $host, 'newpass' => $password));
						//Server unavailable
						if(is_null($response)) {
							$status = 'error';
							$message = __('Server is temporarily unavailable.', 'ejabberd-account-tools');
						}
						//Password changed
						else if($response['code'] == 0) {
							//Delete all transients
							delete_transient('ejabat_pass_'.$login.'@'.$host);
							delete_transient('ejabat_pass_'.$code);
							//Success message
							$status = 'success';
							$message = __('The password for your account was successfully changed.', 'ejabberd-account-tools');
						}
						//Unexpected error
						else {
							$status = 'error';
							$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
						}
					}
					//Code expired or not valid
					else {
						//Delete transient
						delete_transient('ejabat_pass_'.$code);
						//Error message
						$status = 'blocked';
						$message = __('The link to reset password has expired or is not valid.', 'ejabberd-account-tools');
					}
				}
			}
		}
	}
	//Return response
	wp_send_json(array('status' => $status, 'message' => $message));
}
add_action('wp_ajax_ejabat_change_password', 'ajax_ejabat_change_password_callback');
add_action('wp_ajax_nopriv_ejabat_change_password', 'ajax_ejabat_change_password_callback');

//Add shortcode
add_shortcode('ejabat_reset_password', 'ejabat_reset_password_shortcode');
