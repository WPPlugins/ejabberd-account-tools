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

//Get XMPP data by ReST API
function ejabat_get_xmpp_data($command, $arguments = '') {
	//POST arguments
	$args = array(
		'headers' => array(
			'Authorization' => 'Basic '.base64_encode(get_option('ejabat_login').':'.get_option('ejabat_password')),
			'X-Admin' => 'true'
		),
		'body' => json_encode($arguments),
		'timeout' => get_option('ejabat_rest_timeout', 5),
		'redirection' => 0,
		'httpversion' => '1.1',
		'sslverify' => apply_filters('ejabat_sslverify', true)
	);
	//POST data
	$rest_url = get_option('ejabat_rest_url');
	$retry_limit = get_option('ejabat_rest_retry', 3);
	$retry_count = 0;
	while($retry_count < $retry_limit) {
		$response = wp_remote_post($rest_url.'/'.$command, $args);
		if(is_wp_error($response)) { /* error */ }
		else if(($response['response']['code'] == 200) || ($response['response']['code'] == 500)) {
			break;
		}
		$retry_count++;
	}
	//Server temporarily unavailable
	if(is_wp_error($response)) {
		return null;
	}
	//Verify response
	else if(($response['response']['code'] == 200) || ($response['response']['code'] == 500)) {
		//Set last activity information
		if(get_option('ejabat_set_last', false)) {
			//POST arguments
			list($user, $host) = explode('@', get_option('ejabat_login'));
			$args = array(
				'headers' => array(
					'Authorization' => 'Basic '.base64_encode(get_option('ejabat_login').':'.get_option('ejabat_password')),
					'X-Admin' => 'true'
				),
				'body' => json_encode(array('user' => $user, 'host' => $host, 'timestamp' => current_time('timestamp', 1))),
				'timeout' => get_option('ejabat_rest_timeout', 5),
				'redirection' => 0,
				'httpversion' => '1.1',
				'sslverify' => apply_filters('ejabat_sslverify', true),
				'blocking' => false
			);
			//POST data
			wp_remote_post($rest_url.'/set_last', $args);
		}
		//Return response body
		if($response['response']['code'] == 200) {
			if(is_numeric($response['body'])) return array('code' => $response['body'], 'body' => $response['body']);
			else return array('code' => 0, 'body' => $response['body']);
		}
		else return array('code' => 1, 'body' => $response['body']);
	}
	//Unexpected error
	return array('code' => $response['response']['code'], 'body' => $response['response']['message']);
}

//Validating email address by checking MX record
function ejabat_validate_email_mxrecord($email) {
	//Explode email
	list($user, $domain) = explode('@', $email);
	//Check MX record
	$arr= dns_get_record($domain, DNS_MX);
	if($arr[0]['host'] == $domain && !empty($arr[0]['target'])) {
		return true;
	}
	return false;
}

//Validating email address on VALIDATOR.pizza
function ejabat_validate_email_pizza($email) {
	if(get_option('ejabat_validator_pizza', true)) {
		//Explode email
		list($user, $domain) = explode('@', $email);
		//GET arguments
		$args = array(
			'timeout' => 5,
			'redirection' => 0
		);
		//GET data
		$response = wp_remote_get('https://www.validator.pizza/domain/' . $domain, $args);
		//Server temporarily unavailable
		if(is_wp_error($response)) {
			return true;
		}
		//Verify response
		else if($response['response']['code'] == 200) {
			//Return response body
			return !json_decode($response['body'])->disposable;
		}
	}
	//Unexpected error
	return true;
}

//Ajax validate email address
function ajax_ejabat_validate_email() {
	//Get email
	$email = stripslashes_deep($_POST['email']);
	//Verify email
	if(!filter_var($email, FILTER_VALIDATE_EMAIL) || !ejabat_validate_email_mxrecord($email)) {
		$status = 'blocked';
	}
	//Return response
	wp_send_json(array('status' => $status ?: 'success'));
}
add_action('wp_ajax_ejabat_validate_email', 'ajax_ejabat_validate_email');
add_action('wp_ajax_nopriv_ejabat_validate_email', 'ajax_ejabat_validate_email');

//Masking email address
function mask_email($email, $mask = '*', $percent = 80)
{
	list($user, $domain) = explode('@', $email);
	$len = strlen($user);
	$mask_count = floor($len * $percent / 100);
	$offset = floor(($len - $mask_count) / 2 );
	$masked = substr($user, 0, $offset).str_repeat($mask, $mask_count).substr($user, $mask_count + $offset);
	return($masked.'@'.$domain);
}
