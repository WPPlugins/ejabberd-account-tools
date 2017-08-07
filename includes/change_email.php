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
function ejabat_enqueue_change_email_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_change_email')) {
		$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
		wp_enqueue_style('loaders', EJABAT_DIR_URL.'css/loaders'.$min.'.css', array(), '0.1.2', 'all');
		wp_enqueue_style('ejabat', EJABAT_DIR_URL.'css/style'.$min.'.css', array(), EJABAT_VERSION, 'all');
		wp_enqueue_style('fontawesome', EJABAT_DIR_URL.'css/font-awesome'.$min.'.css', array(), '4.7.0', 'all');
		wp_enqueue_script('ejabat-change-email', EJABAT_DIR_URL.'js/jquery.ejabat.change-email'.$min.'.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-change-email', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'timeout' => (get_option('ejabat_rest_timeout', 5)*get_option('ejabat_rest_retry', 3)+5)*1000,
			'checking_email' => sprintf(__('%s Checking email address...', 'ejabberd-account-tools'), '<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>'),
			'invalid_email' => __('Email address seems invalid.', 'ejabberd-account-tools'),
			'recaptcha_verify' => __('Please verify the Captcha.', 'ejabberd-account-tools'),
			'empty_field' => __('Please fill the required field.', 'ejabberd-account-tools'),
			'empty_fields' => __('Verification errors occurred. Please check all fields and submit it again.', 'ejabberd-account-tools'),
			'error' => __('Unexpected error occurred, try again.', 'ejabberd-account-tools'),
			'form_error' => '<div class="ejabat-info ejabat-no-margin ejabat-form-error">'.__('Unexpected error occurred, try again.', 'ejabberd-account-tools').'</div>'
		));
	}
}
add_action('wp_enqueue_scripts', 'ejabat_enqueue_change_email_scripts');

//Change email shortcode
function ejabat_change_email_shortcode() {
	return '<div id="ejabat_change_email_form" class="loader-inner line-scale" title="'.__('Loading', 'ejabberd-account-tools').'..."><div></div><div></div><div></div><div></div><div></div></div>';
}

//Change email form
function ajax_ejabat_change_email_form() {
	//Form is disabled
	if(get_option('ejabat_disable_change_email', false) && !is_user_logged_in()) {
		$html = '<div class="ejabat-info ejabat-no-margin ejabat-form-error">'.__('Form to change private email address is temporarily disabled, please try again later.', 'ejabberd-account-tools').'</div>';
	}
	else {
		//Change request uri to JS referer
		$_SERVER['REQUEST_URI'] = sanitize_text_field($_POST['referer']);
		//Default response
		$response = '<div id="response" class="ejabat-display-none"></div>';
		//Get code parameter
		$code = sanitize_text_field($_POST['code']);
		//Link to change email
		if($code != 'undefined') {
			//Code valid
			if(true == ($data = get_transient('ejabat_email_'.$code))) {
				//Get data
				$login = $data['login'];
				$host = $data['host'];
				$email = $data['email'];
				//Try set private email
				$response = ejabat_get_xmpp_data('private_set', array('user' => $login, 'host' => $host, 'element' => '<private xmlns=\'email\'>'.$email.'</private>'));
				//Server unavailable
				if(is_null($response)) {
					$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools').'</div>';
				}
				//Private email changed
				else if($response['code'] == 0) {
					delete_transient('ejabat_email_'.$code);
					$response = '<div id="response" class="ejabat-display-none ejabat-form-success" style="display: inline-block;">'.sprintf(__('Private email address, for your XMPP account %s, has been successfully changed to %s.', 'ejabberd-account-tools'), $login.'@'.$host, $email).'</div>';
				}
				//Unexpected error
				else {
					$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Unexpected error occurred, try again.', 'ejabberd-account-tools').'</div>';
				}
			}
			//Code expired or not valid
			else {
				delete_transient('ejabat_email_'.$code);
				$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to change private email address has expired or is not valid. Please fill the form and submit it again.', 'ejabberd-account-tools').'</div>';
			}
		}
		//Get recaptcha
		$recaptcha_html = apply_filters('recaptcha_html','');
		//Create form
		$html .= '<form id="ejabat_change_email" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
			<div id="login">
				<input type="text" name="login" placeholder="'.__('Login', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
				<span class="tip"></span>
			</div>
			<div id="password">
				<input type="password" name="password" placeholder="'.__('Password', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
				<span class="tip"></span>
			</div>
			<div id="email">
				<input type="email" name="email" placeholder="'.__('New private email', 'ejabberd-account-tools').'">
				<span class="tip"></span>
			</div>
			'.$recaptcha_html.'
			<span id="recaptcha" class="recaptcha tip"></span>
			<div id="submit">
				<input type="hidden" name="action" value="ejabat_change_email">
				'.wp_nonce_field('ajax_ejabat_change_email', '_ejabat_nonce', true, false).'
				<input type="submit" value="'.__('Change email', 'ejabberd-account-tools').'" id="ejabat_change_email_button">
				<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
			</div>
			'.$response.'
		</form>';
	}
	wp_send_json(array('data' => $html));
}
add_action('wp_ajax_ejabat_change_email_form', 'ajax_ejabat_change_email_form');
add_action('wp_ajax_nopriv_ejabat_change_email_form', 'ajax_ejabat_change_email_form');

//Change email - form callback
function ajax_ejabat_change_email_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['_ejabat_nonce']), 'ajax_ejabat_change_email') || !check_ajax_referer('ajax_ejabat_change_email', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabberd-account-tools');
	}
	else {
		//Verify fields
		if(empty($_POST['login']) || empty($_POST['password']) || empty($_POST['email'])) {
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
				//Verify email
				$email = stripslashes_deep(sanitize_text_field($_POST['email']));
				if(!filter_var($email, FILTER_VALIDATE_EMAIL) || !ejabat_validate_email_mxrecord($email)) {
					$status = 'blocked';
					$message = __('Email address seems invalid, change it and try again.', 'ejabberd-account-tools');
				}
				else if(!ejabat_validate_email_pizza($email)) {
					$status = 'blocked';
					$message = __('Disposable emails addresses are forbidden, please change entered email address and try again.', 'ejabberd-account-tools');
				}
				else {
					//Check login and password
					list($login, $host) = array_pad(explode('@', stripslashes_deep(sanitize_text_field($_POST['login'])), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
					$password = stripslashes_deep(sanitize_text_field($_POST['password']));
					$response = ejabat_get_xmpp_data('check_password', array('user' => $login, 'host' => $host, 'password' => $password));
					//Server unavailable
					if(is_null($response)) {
						$status = 'error';
						$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
					}
					//Invalid login or password
					else if($response['code'] == 1) {
						$status = 'blocked';
						$message = __('Invalid login or password, correct them and try again.', 'ejabberd-account-tools');
					}
					//Login and password valid
					else if($response['code'] == 0) {
						//Get current private email address
						$response = ejabat_get_xmpp_data('private_get', array('user' => $login, 'host' => $host, 'element' => 'private', 'ns' => 'email'));
						//Server unavailable
						if(is_null($response)) {
							$status = 'error';
							$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
						}
						//Check response
						else if($response['code'] == 0) {
							//Get email address
							preg_match("/<private xmlns='email'>(.*)?<\/private>/", $response['body'], $matches);
							//New email address different from current
							if($email != $matches[1]) {
								//Set code transient
								$code = bin2hex(openssl_random_pseudo_bytes(16));
								$data = array('timestamp' => current_time('timestamp', 1), 'ip' => $_SERVER['REMOTE_ADDR'], 'login' => $login, 'host' => $host, 'email' => $email);
								set_transient('ejabat_email_'.$code, $data, get_option('ejabat_change_email_timeout', 900));
								//Email data
								$subject = sprintf(__('Confirm the email address for your %s account', 'ejabberd-account-tools'), $host);
								$body = sprintf(__('Hey %s,<br><br>You have changed the private email address for your XMPP account %s. To complete the change, please click on the confirmation link:<br><br>%s<br><br>If you haven\'t made this change, simply disregard this email.<br><br>Greetings,<br>%s', 'ejabberd-account-tools'), $login, $login.'@'.$host, '<a href="'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'">'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'</a>', get_option('ejabat_sender_name', get_bloginfo()));
								$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', get_option('admin_email')).'>';
								$headers[] = 'Content-Type: text/html; charset=UTF-8';
								//Try send email
								if(wp_mail($login.' <'.$email.'>', $subject, $body, $headers)) {
									$status = 'success';
									$message = __('An email has been sent to you to confirm changes. It contains a confirmation link that you have to click.', 'ejabberd-account-tools');
								}
								//Problem with sending email
								else {
									delete_transient('ejabat_email_'.$code);
									$status = 'error';
									$message = __('Failed to send email, try again.', 'ejabberd-account-tools');
								}
							}
							//New private email address same as current
							else {
								$status = 'blocked';
								$message = __('Selected private email address is already set for this account.', 'ejabberd-account-tools');
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
	}
	//Return response
	wp_send_json(array('status' => $status, 'message' => $message));
}
add_action('wp_ajax_ejabat_change_email', 'ajax_ejabat_change_email_callback');
add_action('wp_ajax_nopriv_ejabat_change_email', 'ajax_ejabat_change_email_callback');

//Change email - admin form callback
function ejabat_change_email_admin_callback() {
	//Verify login
	if($_POST['login']) {
		//Get login and host name
		list($login, $host) = array_pad(explode('@', stripslashes_deep(sanitize_text_field($_POST['login'])), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
		//Check account
		$response = ejabat_get_xmpp_data('check_account', array('user' => $login, 'host' => $host));
		//Server unavailable
		if(is_null($response)) {
			$status = 'error';
			$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
		}
		//User not found
		else if($response['code'] == 1) {
			$status = 'blocked';
			$message = __('Invalid login, correct it and try again.', 'ejabberd-account-tools');
		}
		//User found
		else if($response['code'] == 0) {
			//Verify email
			$email = stripslashes_deep(sanitize_text_field($_POST['email']));
			if((!empty($email)) && (!filter_var($email, FILTER_VALIDATE_EMAIL) || !ejabat_validate_email_mxrecord($email))) {
				$status = 'blocked';
				$message = __('Email address seems invalid, change it and try again.', 'ejabberd-account-tools');
			}
			else if((!empty($email)) && !ejabat_validate_email_pizza($email)) {
				$status = 'blocked';
				$message = __('Disposable emails addresses are forbidden, please change entered email address and try again.', 'ejabberd-account-tools');
			}
			else {
				//Get current private email address
				$response = ejabat_get_xmpp_data('private_get', array('user' => $login, 'host' => $host, 'element' => 'private', 'ns' => 'email'));
				//Server unavailable
				if(is_null($response)) {
					$status = 'error';
					$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
				}
				//Check response
				else if($response['code'] == 0) {
					//Get email address
					preg_match("/<private xmlns='email'>(.*)?<\/private>/", $response['body'], $matches);
					//New email address different from current
					if(empty($email) || ($email != $matches[1])) {
						//Try delete private email
						if(empty($email)) {
							$response = ejabat_get_xmpp_data('private_set', array('user' => $login, 'host' => $host, 'element' => '<private xmlns=\'email\'/>'));
							//Server unavailable
							if(is_null($response)) {
								$status = 'error';
								$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
							}
							//Private email changed
							else if($response['code'] == 0) {
								$status = 'success';
								$message = sprintf(__('Private email address, for XMPP account %s, has been successfully deleted.', 'ejabberd-account-tools'), $login.'@'.$host, $email);
							}
							//Unexpected error
							else {
								$status = 'error';
								$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
							}
						}
						//Try set private email
						else {
							$response = ejabat_get_xmpp_data('private_set', array('user' => $login, 'host' => $host, 'element' => '<private xmlns=\'email\'>'.$email.'</private>'));
							//Server unavailable
							if(is_null($response)) {
								$status = 'error';
								$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
							}
							//Private email changed
							else if($response['code'] == 0) {
								$status = 'success';
								$message = sprintf(__('Private email address, for XMPP account %s, has been successfully changed to %s.', 'ejabberd-account-tools'), $login.'@'.$host, $email);
							}
							//Unexpected error
							else {
								$status = 'error';
								$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
							}
						}
					}
					//New private email address same as current
					else {
						$status = 'blocked';
						$message = __('Selected private email address is already set for this account.', 'ejabberd-account-tools');
					}
				}
				//Unexpected error
				else {
					$status = 'error';
					$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
				}
			}
		}
		//Unexpected error
		else {
			$status = 'error';
			$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
		}
	}
	else {
		$status = 'blocked';
		$message = __('Please enter a login and check again.', 'ejabberd-account-tools');
	}
	//Return response
	return array('status' => $status, 'message' => $message);
}

//Check email - admin form callback
function ejabat_check_email_admin_callback() {
	//Verify login
	if($_POST['login']) {
		//Get login and host name
		list($login, $host) = array_pad(explode('@', stripslashes_deep(sanitize_text_field($_POST['login'])), 2), 2, get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])));
		//Check account
		$response = ejabat_get_xmpp_data('check_account', array('user' => $login, 'host' => $host));
		//Server unavailable
		if(is_null($response)) {
			$_POST['email'] = null;
			$status = 'error';
			$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
		}
		//User not found
		else if($response['code'] == 1) {
			$_POST['email'] = null;
			$status = 'blocked';
			$message = __('Invalid login, correct it and try again.', 'ejabberd-account-tools');
		}
		//User found
		else if($response['code'] == 0) {
			//Get current private email address
			$response = ejabat_get_xmpp_data('private_get', array('user' => $login, 'host' => $host, 'element' => 'private', 'ns' => 'email'));
			//Server unavailable
			if(is_null($response)) {
				$_POST['email'] = null;
				$status = 'error';
				$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
			}
			//Check response
			else if($response['code'] == 0) {
				//Private email set
				if(preg_match("/<private xmlns='email'>(.*)?<\/private>/", $response['body'], $matches)) {
					//Fetch email address
					$_POST['email'] = $matches[1];
					$status = 'success';
					$message = __('Private email address has been properly obtained.', 'ejabberd-account-tools');
				}
				//Private email not set
				else {
					$_POST['email'] = null;
					$message = __('Private email address hasn\'t been set for this account.', 'ejabberd-account-tools');
					$status = 'error';
				}
			}
			//Unexpected error
			else {
				$_POST['email'] = null;
				$status = 'error';
				$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
			}
		}
		//Unexpected error
		else {
			$_POST['email'] = null;
			$status = 'error';
			$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
		}
	}
	else {
		$_POST['email'] = null;
		$status = 'blocked';
		$message = __('Please enter a login and check again.', 'ejabberd-account-tools');
	}
	//Return response
	return array('status' => $status, 'message' => $message);
}

//Add shortcode
add_shortcode('ejabat_change_email', 'ejabat_change_email_shortcode');
