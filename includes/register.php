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
function ejabat_enqueue_register_scripts() {
	global $post;
	if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ejabat_register')) {
		$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
		//Get hints args
		$show_hints = get_option('ejabat_show_hints', true);
		if($show_hints) {
			$hints = apply_filters('ejabat_hints_args', array(
				'login' => get_option('ejabat_login_hint', __('At least 3 and up to 32 characters, only letters and numbers', 'ejabberd-account-tools')),
				'password' => get_option('ejabat_password_hint', __('Required at least good password', 'ejabberd-account-tools')),
				'email' => get_option('ejabat_email_hint', __('Required only for password recovery', 'ejabberd-account-tools'))
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
				'login' => $hints['login'],
				'password' => $hints['password'],
				'password_strength' => get_option('ejabat_password_strength', 3),
				'email' => $hints['email']
			));
		}
		wp_enqueue_script('ejabat-register', EJABAT_DIR_URL.'js/jquery.ejabat.register'.$min.'.js', array('jquery'), EJABAT_VERSION, true);
		wp_localize_script('ejabat-register', 'ejabat', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'timeout' => (get_option('ejabat_rest_timeout', 5)*get_option('ejabat_rest_retry', 3)+5)*1000,
			'login_regexp' => get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$'),
			'checking_login' => sprintf(__('%s Checking login...', 'ejabberd-account-tools'), '<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>'),
			'invalid_login' => __('Login contains illegal characters or it\'s too short.', 'ejabberd-account-tools'),
			'password_strength' => get_option('ejabat_password_strength', 3),
			'password_too_weak' => __('Password is too weak.', 'ejabberd-account-tools'),
			'password_very_weak' => __('Password is very weak.', 'ejabberd-account-tools'),
			'password_weak' => __('Password is weak.', 'ejabberd-account-tools'),
			'password_good' => __('Password is good.', 'ejabberd-account-tools'),
			'password_strong' => __('Password is strong.', 'ejabberd-account-tools'),
			'passwords_mismatch' => __('Password mismatch with the confirmation.', 'ejabberd-account-tools'),
			'checking_email' => sprintf(__('%s Checking email address...', 'ejabberd-account-tools'), '<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>'),
			'invalid_email' => __('Email address seems invalid.', 'ejabberd-account-tools'),
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
add_action('wp_enqueue_scripts', 'ejabat_enqueue_register_scripts');

//Registration form shortcode
function ejabat_register_shortcode() {
	return '<div id="ejabat_register_form" class="loader-inner line-scale" title="'.__('Loading', 'ejabberd-account-tools').'..."><div></div><div></div><div></div><div></div><div></div></div>';
}

//Registration form
function ajax_ejabat_register_form() {
	//Registration is disabled
	if(get_option('ejabat_disable_registration', false) && !is_user_logged_in()) {
		$html = '<div class="ejabat-info ejabat-no-margin ejabat-form-error">'.__('Registration is temporarily disabled, please try again later.', 'ejabberd-account-tools').'</div>';
	}
	else {
		//Change request uri to JS referer
		$_SERVER['REQUEST_URI'] = sanitize_text_field($_POST['referer']);
		//Default response
		$response = '<div id="response" class="ejabat-display-none"></div>';
		//Get available host names
		$hosts = get_option('ejabat_registration_hosts');
		if(!empty($hosts)) {
			//Get default host name
			$default_host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
			//Get host name from URL parameters
			$url_host = stripslashes_deep(sanitize_text_field($_POST['host'])) ?: $default_host;
			//Get hosts list from settings
			$hosts = array();
			$hosts[] = $default_host;
			$hosts = array_merge($hosts, explode(' ', get_option('ejabat_registration_hosts')));
			//Compare host name from URL with hosts list
			if(!in_array($url_host, $hosts)) {
				$url_host = $default_host;
			}
			//Foreach hosts
			foreach($hosts as $host) {
				$host_select .= '<option value="' . $host . '" ' . selected($url_host, $host, false) . '>@' . $host . '</option>';
			}
			$host_select = '<div id="host" class="hints">
				<select name="host">
					'.$host_select.'
				</select>
			</div>';
		}
		//Verify registration timeout
		if(get_transient('ejabat_register_'.$_SERVER['REMOTE_ADDR'])) {
			$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('You can\'t register another account so quickly. Please try again later.', 'ejabberd-account-tools').'</div>';
		}
		//Get code parameter
		$code = sanitize_text_field($_POST['code']);
		//Link to confirm registration
		if($code != 'undefined') {
			//Code valid
			if(true == ($data = get_transient('ejabat_register_'.$code))) {
				//Try set correct password
				$response = ejabat_get_xmpp_data('change_password', array('user' => $data['login'], 'host' => $data['host'], 'newpass' => $data['password']));
				//Server unavailable
				if(is_null($response)) {
					$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools').'</div>';
				}
				//Password changed
				else if($response['code'] == 0) {
					//Send welcome message
					if(get_option('ejabat_welcome_msg', false)) {
						//Get subject and body
						$welcome_msg = apply_filters('ejabat_welcome_msg_args', array(
							'subject' => get_option('ejabat_welcome_msg_subject'),
							'body' => get_option('ejabat_welcome_msg_body')
						));
						$welcome_msg['subject'] = htmlspecialchars(wp_strip_all_tags(do_shortcode($welcome_msg['subject'])));
						$welcome_msg['subject'] = str_replace(array('\r\n', '\\r\\n', '\n', '\\n', '\r', '\\r'), ' ', $welcome_msg['subject']);
						$welcome_msg['body'] = htmlspecialchars(wp_strip_all_tags(do_shortcode($welcome_msg['body'])));
						$welcome_msg['body'] = str_replace(array('\r\n', '\\r\\n', '\n', '\\n', '\r', '\\r'), "\n", $welcome_msg['body']);
						//Send message
						if($welcome_msg['subject']) {
							ejabat_get_xmpp_data('send_message', array('type' => 'normal', 'from' => $data['host'], 'to' => $data['login'].'@'.$data['host'], 'subject' => $welcome_msg['subject'], 'body' => $welcome_msg['body']));
						} else {
							ejabat_get_xmpp_data('send_message', array('type' => 'chat', 'from' => $data['host'], 'to' => $data['login'].'@'.$data['host'], 'body' => $welcome_msg['body']));
						}
					}
					//Delete transient
					delete_transient('ejabat_register_'.$code);
					//Success message
					$response = '<div id="response" class="ejabat-display-none ejabat-form-success" style="display: inline-block;">'.sprintf(__('Account %s has been successfully activated.', 'ejabberd-account-tools'),$data['login'].'@'.$data['host']).'</div>';
				}
				//Unexpected error
				else {
					$response = '<div id="response" class="ejabat-display-none ejabat-form-error" style="display: inline-block;">'.__('Unexpected error occurred, try again.', 'ejabberd-account-tools').'</div>';
				}
			}
			//Code expired or not valid
			else {
				//Delete transient
				delete_transient('ejabat_register_'.$code);
				//Response with error
				$response = '<div id="response" class="ejabat-display-none ejabat-form-blocked" style="display: inline-block;">'.__('The link to activate account has expired or is not valid. Please fill the form and submit it again.', 'ejabberd-account-tools').'</div>';
			}
		}
		//Get recaptcha
		$recaptcha_html = apply_filters('recaptcha_html','');
		//Create form
		$html = '<form id="ejabat_register" class="ejabat" method="post" novalidate="novalidate" autocomplete="off" onsubmit="return false">
			<div id="login" class="hints">
				<input type="text" name="login" placeholder="'.__('Login', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
				<span class="tip"></span>
			</div>
			'.$host_select.'
			<div id="password" class="hints">
				<input type="password" name="password" placeholder="'.__('Password', 'ejabberd-account-tools').'" readonly onfocus="this.removeAttribute(\'readonly\');">
				<span class="tip"></span>
			</div>
			<div id="password_retyped">
				<input type="password" name="password_retyped" placeholder="'.__('Confirm password', 'ejabberd-account-tools').'">
				<span class="tip"></span>
			</div>
			<div id="email" class="hints">
				<input type="email" name="email" placeholder="'.__('Private email', 'ejabberd-account-tools').'">
				<span class="tip"></span>
			</div>
			'.$recaptcha_html.'
			<span id="recaptcha" class="recaptcha tip"></span>
			<div id="submit">
				<input type="hidden" name="action" value="ejabat_register">
				'.wp_nonce_field('ajax_ejabat_register', '_ejabat_nonce', true, false).'
				<input type="submit" value="'.__('Register', 'ejabberd-account-tools').'" id="ejabat_register_button">
				<i id="spinner" style="visibility: hidden;" class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
			</div>
			'.$response.'
		</form>';
	}
	wp_send_json(array('data' => $html));
}
add_action('wp_ajax_ejabat_register_form', 'ajax_ejabat_register_form');
add_action('wp_ajax_nopriv_ejabat_register_form', 'ajax_ejabat_register_form');

//Registration form callback
function ajax_ejabat_register_form_callback() {
	//Verify nonce
	if(!isset($_POST['_ejabat_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['_ejabat_nonce']), 'ajax_ejabat_register') || !check_ajax_referer('ajax_ejabat_register', '_ejabat_nonce', false)) {
		$status = 'blocked';
		$message = __('Verification error, try again.', 'ejabberd-account-tools');
	}
	else {
		//Verify fields
		if(empty($_POST['login']) || empty($_POST['password']) || empty($_POST['password_retyped']) || empty($_POST['email'])) {
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
				//Verify login
				$login = stripslashes_deep(sanitize_text_field($_POST['login']));
				if(!preg_match('/'.get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$').'/i', $login)) {
					$status = 'blocked';
					$message = __('Selected login contains illegal characters or it\'s too short, change it and try again.', 'ejabberd-account-tools');
				}
				else if(preg_match('/'.get_option('ejabat_blocked_login_regexp', '^(.*(404|abort|about|abuse|access|account|activat|address|adium|admin|adult|advertisin|affiliat|agile|ajax|allegro|analytics|android|anonymous|api|app|aqq|archiv|atom|auth|backup|billing|blog|board|bombus|bot|bug|business|cache|calendar|campaign|cancel|careers|cart|ceo|cgi|changelog|chat|check|chrome|client|cms|comercial|comment|compare|config|connect|contact|contest|contract|convers|cpp|creat|css|custome|dashboard|delete|demo|design|detail|develop|digsby|direct|disc|docs|document|domain|dot|drive|dropbox|ebay|ecommerce|edit|employment|enquiries|enterprise|error|event|facebook|faq|favorite|feed|file|firefox|flock|follow|form|forum|ftp|gadget|gajim|gist|github|google|group|guest|guide|help|homepage|host|htm|http|ijab|imap|index|info|instagram|instantbird|internal|intranet|invit|invoic|ipad|iphone|irc|irssi|issue|jabbear|jabber|jabbim|jabiru|jappix|java|jitsi|job|joomla|json|kadu|kopete|language|load|login|logout|logs|mail|manager|manual|market|media|member|message|messenger|microblog|microsoft|miranda|mobile|mozilla|mp3|msg|msn|mysql|name|network|news|nick|noreply|ns1|ns2|ns3|ns4|oauth|offers|office|olx|online|openid|operator|oracle|order|organizat|owner|page|pandion|panel|password|perl|php|pidgin|plugin|pop3|popular|porn|post|press|print|privacy|profil|promo|psi|pub|python|query|random|recruit|register|registrat|remove|replies|root|rss|ruby|sales|sample|save|script|search|secure|security|send|seo|service|session|setting|setup|shop|signin|signup|site|smtp|sql|ssh|ssl|staff|start|static|stats|status|store|subscrib|support|sysop|system|tablet|talk|task|team|tech|telnet|terms|test|theme|tigase|tkabber|tlen|tmp|todo|tool|translat|trillian|troll|tube|twitt|update|url|usage|user|vendas|video|visitor|voice|weather|web|widget|windows|work|wtw|www|xabber|xml|xmpp|yaml|yaxim|yml).*)$').'/i', $login)) {
					$status = 'blocked';
					$message = __('Selected login contains illegal words, change it and try again.', 'ejabberd-account-tools');
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
						//Verify registration timeout
						$ip = $_SERVER['REMOTE_ADDR'];
						if(get_transient('ejabat_register_'.$ip)) {
							$status = 'blocked';
							$message = __('You can\'t register another account so quickly, please try again later.', 'ejabberd-account-tools');
						}
						else {
							//Verify passwords
							$password = stripslashes_deep(sanitize_text_field($_POST['password']));
							$password_retyped = stripslashes_deep(sanitize_text_field($_POST['password_retyped']));
							if($password != $password_retyped) {
								$status = 'blocked';
								$message = __('Passwords don\'t match, correct them and try again.', 'ejabberd-account-tools');
							}
							//Try register account
							else {
								//Verify host
								$host = stripslashes_deep(sanitize_text_field($_POST['host'])) ?: get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
								$hosts = explode(' ', get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])).' '.get_option('ejabat_registration_hosts'));
								if(in_array($host, $hosts)) {
									//Two-step registration
									if(get_option('ejabat_two_step_registration', false)) {
										$response = ejabat_get_xmpp_data('register', array('user' => $login, 'host' => $host, 'password' => bin2hex(openssl_random_pseudo_bytes(8))));
									}
									//Normal registration
									else {
										$response = ejabat_get_xmpp_data('register', array('user' => $login, 'host' => $host, 'password' => $password));
									}
									//Server unavailable
									if(is_null($response)) {
										$status = 'error';
										$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
									}
									//Successfully registered
									else if($response['code'] == 0) {
										//Get current timestamp
										$now = current_time('timestamp', 1);
										//Set private email
										ejabat_get_xmpp_data('private_set', array('user' => $login, 'host' => $host, 'element' => '<private xmlns=\'email\'>'.$email.'</private>'));
										//Two-step registration
										if(get_option('ejabat_two_step_registration', false)) {
											//Set code transient
											$code = bin2hex(openssl_random_pseudo_bytes(16));
											$data = array('timestamp' => $now, 'ip' => $_SERVER['REMOTE_ADDR'], 'login' => $login, 'password' => $password, 'host' => $host, 'email' => $email);
											set_transient('ejabat_register_'.$code, $data, get_option('ejabat_activation_timeout', 3600));
											//Email data
											$subject = sprintf(__('Confirm your new account on %s', 'ejabberd-account-tools'), $host);
											$body = sprintf(__('Hey %s,<br><br>You have registered the account %s with this email address. To complete your registration, please click on the activation link:<br><br>%s<br><br>If you haven\'t registered an account, simply disregard this email.<br><br>Greetings,<br>%s', 'ejabberd-account-tools'), $login, $login.'@'.$host, '<a href="'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'">'.explode('?', get_bloginfo('wpurl').sanitize_text_field($_POST['_wp_http_referer']))[0].'?code='.$code.'</a>', get_option('ejabat_sender_name', get_bloginfo()));
											$headers[] = 'From: '.get_option('ejabat_sender_name', get_bloginfo()).' <'.get_option('ejabat_sender_email', get_option('admin_email')).'>';
											$headers[] = 'Content-Type: text/html; charset=UTF-8';
											//Try send email
											if(wp_mail($login.' <'.$email.'>', $subject, $body, $headers)) {
												$status = 'success';
												$message = __('An email has been sent to you to confirm registration. It contains an activation link that you have to click.', 'ejabberd-account-tools');
											}
											//Problem with sending email
											else {
												$status = 'error';
												$message = __('Failed to complete registration, please contact with the administrator.', 'ejabberd-account-tools');
											}
										}
										//Normal registration
										else {
											//Form status
											$status = 'success';
											$message = sprintf(__('Account %s has been successfully registered.', 'ejabberd-account-tools'), $login.'@'.$host);
											//Send welcome message
											if(get_option('ejabat_welcome_msg', false)) {
												//Get subject and body
												$welcome_msg = apply_filters('ejabat_welcome_msg_args', array(
													'subject' => get_option('ejabat_welcome_msg_subject'),
													'body' => get_option('ejabat_welcome_msg_body')
												));
												$welcome_msg['subject'] = htmlspecialchars(wp_strip_all_tags(do_shortcode($welcome_msg['subject'])));
												$welcome_msg['subject'] = str_replace(array('\r\n', '\\r\\n', '\n', '\\n', '\r', '\\r'), ' ', $welcome_msg['subject']);
												$welcome_msg['body'] = htmlspecialchars(wp_strip_all_tags(do_shortcode($welcome_msg['body'])));
												$welcome_msg['body'] = str_replace(array('\r\n', '\\r\\n', '\n', '\\n', '\r', '\\r'), "\n", $welcome_msg['body']);
												//Send message
												if($welcome_msg['subject']) {
													ejabat_get_xmpp_data('send_message', array('type' => 'normal', 'from' => $host, 'to' => $login.'@'.$host, 'subject' => $welcome_msg['subject'], 'body' => $welcome_msg['body']));
												} else {
													ejabat_get_xmpp_data('send_message', array('type' => 'chat', 'from' => $host, 'to' => $login.'@'.$host, 'body' => $welcome_msg['body']));
												}
											}
										}
										//Registration watcher
										if(get_option('ejabat_watcher')) {
											ejabat_get_xmpp_data('send_message', array('type' => 'chat', 'from' => $host, 'to' => get_option('ejabat_watcher'), 'body' => '['.date_i18n('Y-m-d H:i:s', $now + get_option('gmt_offset') * 3600).'] The account '.$login.'@'.$host.' was registered from IP address '.$ip.' by using web registration form.'));
										}
										//Set registration timeout
										if(get_option('ejabat_registration_timeout', 3600)) {
											$data = array('timestamp' => $now, 'login' => $login, 'email' => $email);
											set_transient('ejabat_register_'.$ip, $data, get_option('ejabat_registration_timeout', 3600));
										}
									}
									//Already registered
									else if($response['code'] == 1) {
										$status = 'blocked';
										$message = __('Selected login is already registered, change it and try again.', 'ejabberd-account-tools');
									}
									//Unexpected error
									else {
										$status = 'error';
										$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
									}
								}
								//Invalid host
								else {
									$status = 'error';
									$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
								}
							}
						}
					}
				}
			}
		}
	}
	//Return response
	wp_send_json(array('status' => $status, 'message' => $message));
}
add_action('wp_ajax_ejabat_register', 'ajax_ejabat_register_form_callback');
add_action('wp_ajax_nopriv_ejabat_register', 'ajax_ejabat_register_form_callback');

//Check if an account exists or not
function ajax_ejabat_check_login() {
	//Get login
	$login = stripslashes_deep(sanitize_text_field($_POST['login']));
	//Verify login
	if(!preg_match('/'.get_option('ejabat_allowed_login_regexp', '^[a-z0-9_.-]{3,32}$').'/i', $login)) {
		$status = 'blocked';
		$message = __('Login contains illegal characters or it\'s too short.', 'ejabberd-account-tools');
	}
	else if(preg_match('/'.get_option('ejabat_blocked_login_regexp', '^(.*(404|abort|about|abuse|access|account|activat|address|adium|admin|adult|advertisin|affiliat|agile|ajax|allegro|analytics|android|anonymous|api|app|aqq|archiv|atom|auth|backup|billing|blog|board|bombus|bot|bug|business|cache|calendar|campaign|cancel|careers|cart|ceo|cgi|changelog|chat|check|chrome|client|cms|comercial|comment|compare|config|connect|contact|contest|contract|convers|cpp|creat|css|custome|dashboard|delete|demo|design|detail|develop|digsby|direct|disc|docs|document|domain|dot|drive|dropbox|ebay|ecommerce|edit|employment|enquiries|enterprise|error|event|facebook|faq|favorite|feed|file|firefox|flock|follow|form|forum|ftp|gadget|gajim|gist|github|google|group|guest|guide|help|homepage|host|htm|http|ijab|imap|index|info|instagram|instantbird|internal|intranet|invit|invoic|ipad|iphone|irc|irssi|issue|jabbear|jabber|jabbim|jabiru|jappix|java|jitsi|job|joomla|json|kadu|kopete|language|load|login|logout|logs|mail|manager|manual|market|media|member|message|messenger|microblog|microsoft|miranda|mobile|mozilla|mp3|msg|msn|mysql|name|network|news|nick|noreply|ns1|ns2|ns3|ns4|oauth|offers|office|olx|online|openid|operator|oracle|order|organizat|owner|page|pandion|panel|password|perl|php|pidgin|plugin|pop3|popular|porn|post|press|print|privacy|profil|promo|psi|pub|python|query|random|recruit|register|registrat|remove|replies|root|rss|ruby|sales|sample|save|script|search|secure|security|send|seo|service|session|setting|setup|shop|signin|signup|site|smtp|sql|ssh|ssl|staff|start|static|stats|status|store|subscrib|support|sysop|system|tablet|talk|task|team|tech|telnet|terms|test|theme|tigase|tkabber|tlen|tmp|todo|tool|translat|trillian|troll|tube|twitt|update|url|usage|user|vendas|video|visitor|voice|weather|web|widget|windows|work|wtw|www|xabber|xml|xmpp|yaml|yaxim|yml).*)$').'/i', $login)) {
		$status = 'blocked';
		$message = __('Login contains illegal words.', 'ejabberd-account-tools');
	}
	//Check login
	else {
		//Get host
		$host = stripslashes_deep(sanitize_text_field($_POST['host']));
		if($host == 'undefined') {
			$host = get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME']));
		}
		//Check host
		$hosts = explode(' ', get_option('ejabat_hostname', preg_replace('/^www\./','',$_SERVER['SERVER_NAME'])).' '.get_option('ejabat_registration_hosts'));
		if(in_array($host, $hosts)) {
			//Check account
			$response = ejabat_get_xmpp_data('check_account', array('user' => $login, 'host' => $host));
			//Server unavailable
			if(is_null($response)) {
				$status = 'error';
				$message = __('Server is temporarily unavailable.', 'ejabberd-account-tools');
			}
			//Login available
			else if($response['code'] == 1) {
				$status = 'success';
				$message = __('Selected login is available.', 'ejabberd-account-tools');
			}
			//Login already registered
			else if($response['code'] == 0) {
				$status = 'blocked';
				$message = __('Selected login is already registered.', 'ejabberd-account-tools');
			}
			//Unexpected error
			else {
				$status = 'error';
				$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
			}
		}
		//Invalid host
		else {
			$status = 'error';
			$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
		}
	}
	//Return response
	wp_send_json(array('status' => $status, 'message' => $message));
}
add_action('wp_ajax_ejabat_check_login', 'ajax_ejabat_check_login');
add_action('wp_ajax_nopriv_ejabat_check_login', 'ajax_ejabat_check_login');

//Send message - admin form callback
function ejabat_send_message_admin_callback() {
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
			//Verify body
			if($_POST['body']) {
				//Get subject and body
				$subject = htmlspecialchars(wp_strip_all_tags(do_shortcode(stripslashes_deep(sanitize_text_field($_POST['subject'])))));
				$body = htmlspecialchars(wp_strip_all_tags(do_shortcode(stripslashes_deep(sanitize_text_field($_POST['body'])))));
				//Send message
				if($subject) {
					$response = ejabat_get_xmpp_data('send_message', array('type' => 'normal', 'from' => $host, 'to' => $login.'@'.$host, 'subject' => $subject, 'body' => $body));
				} else {
					$response = ejabat_get_xmpp_data('send_message', array('type' => 'chat', 'from' => $host, 'to' => $login.'@'.$host, 'body' => $body));
				}
				//Server unavailable
				if(is_null($response)) {
					$status = 'error';
					$message = __('Server is temporarily unavailable, please try again in a moment.', 'ejabberd-account-tools');
				}
				//Message sent
				else if($response['code'] == 0) {
					$status = 'success';
					$message = __('The message was sent correctly.', 'ejabberd-account-tools');
				}
				//Unexpected error
				else {
					$status = 'error';
					$message = __('Unexpected error occurred, try again.', 'ejabberd-account-tools');
				}
			}
			else {
				$status = 'blocked';
				$message = __('Please enter a message body and check again.', 'ejabberd-account-tools');
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

//Add shortcode
add_shortcode('ejabat_register', 'ejabat_register_shortcode');
