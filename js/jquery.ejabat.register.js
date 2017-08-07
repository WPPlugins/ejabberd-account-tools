/*
	Copyright (c) 2015-2016 Krzysztof Grochocki

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
	along with GNU Radio. If not, see <http://www.gnu.org/licenses/>.
*/

//Validate variables
var val_login, val_password, val_password_retyped, val_email, val_recaptcha;

//Validate login
function validateLogin($login, $host, $loginTip) {
	//Reset
	$login.removeClass('invalid');
	$loginTip.removeClass('invalid');
	$loginTip.html(ejabat.checking_login);
	//Get login
	var login = $login.val();
	if(login) {
		var login_regexp = new RegExp(ejabat.login_regexp);
		//Invalid
		if(!login_regexp.test(login)) {
			$login.addClass('invalid');
			$loginTip.addClass('invalid');
			$loginTip.html(ejabat.invalid_login);
			val_login = false;
		}
		else {
			//Check if an account exists or not
			jQuery.ajax({
				method: 'POST',
				url: ejabat.ajax_url + '?action=ejabat_check_login',
				timeout: ejabat.timeout,
				data: 'login=' + login + '&host=' + $host.val(),
				dataType: 'json',
				success: function(response) {
					//Valid
					if(response.status == 'success') {
						$login.removeClass('invalid');
						$loginTip.removeClass('invalid');
						$loginTip.html(response.message);
						val_login = true;
					}
					//Invalid
					else {
						$login.addClass('invalid');
						$loginTip.addClass('invalid');
						$loginTip.html(response.message);
						val_login = false;
					}
				},
				error: function() {
					$login.addClass('invalid');
					$loginTip.addClass('invalid');
					$loginTip.html(ejabat.error);
					val_login = false;
				}
			});
		}
	}
	//Empty field
	else {
		$login.addClass('invalid');
		$loginTip.addClass('invalid');
		$loginTip.html(ejabat.empty_field);
		val_login = false;
	}
}

//Validate password
function validatePassword($password, $passwordTip) {
	//Get password
	var password = $password.val();
	//Get the password strength
	var strength = wp.passwordStrength.meter(password, wp.passwordStrength.userInputBlacklist(), password);
	//Reset
	$password.removeClass('invalid too-weak very-weak weak good strong');
	$passwordTip.removeClass('invalid');
	$passwordTip.empty();
	//Empty field
	if(!password) {
		$password.addClass('invalid');
		$passwordTip.addClass('invalid');
		$passwordTip.html(ejabat.empty_field);
		val_password = false;
	}
	//Password strength validation
	else if(ejabat.password_strength > 0) {
		//Too week password
		if(strength == 0) {
			$password.addClass('too-weak');
			$passwordTip.html(ejabat.password_too_weak);
		}
		//Very week password
		if(strength == 1) {
			$password.addClass('very-weak');
			$passwordTip.html(ejabat.password_very_weak);
		}
		//Week password
		else if(strength == 2) {
			$password.addClass('weak');
			$passwordTip.html(ejabat.password_weak);
		}
		//Good password
		else if(strength == 3) {
			$password.addClass('good');
			$passwordTip.html(ejabat.password_good);
		}
		//Strong password
		else if(strength == 4) {
			$password.addClass('strong');
			$passwordTip.html(ejabat.password_strong);
		}
		//Check password strength
		if(ejabat.password_strength <= strength) {
			val_password = true;
		}
		else {
			$passwordTip.addClass('invalid');
			val_password = false;
		}
	}
	//No password strength validation
	else {
		val_password = true;
	}
}

function validatePasswordRetyped($password, $passwordRetyped, $passwordRetypedTip) {
	//Get passwords
	var password = $password.val();
	var passwordRetyped = $passwordRetyped.val();
	//Empty field
	if(!passwordRetyped) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.empty_field);
		val_password_retyped = false;
	}
	//Mismatch
	else if(password && (password != passwordRetyped)) {
		$passwordRetyped.addClass('invalid');
		$passwordRetypedTip.addClass('invalid');
		$passwordRetypedTip.html(ejabat.passwords_mismatch);
		val_password_retyped = false;
	}
	else {
		$passwordRetyped.removeClass('invalid');
		$passwordRetypedTip.removeClass('invalid');
		$passwordRetypedTip.empty();
		val_password_retyped = true;
	}
}

//Validate email
function validateEmail($email, $emailTip) {
	//Reset
	$email.removeClass('invalid');
	$emailTip.removeClass('invalid');
	$emailTip.html(ejabat.checking_email);
	//Get email
	var email = $email.val();
	if(email) {
		var email_regexp = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		//Invalid
		if(!email_regexp.test(email)) {
			$email.addClass('invalid');
			$emailTip.addClass('invalid');
			$emailTip.html(ejabat.invalid_email);
			val_email = false;
		}
		else {
			jQuery.ajax({
				method: 'POST',
				url: ejabat.ajax_url + '?action=ejabat_validate_email',
				timeout: ejabat.timeout,
				data: 'email=' + email,
				dataType: 'json',
				success: function(response) {
					//Valid
					if(response.status == 'success') {
						$email.removeClass('invalid');
						$emailTip.removeClass('invalid');
						$emailTip.empty();
						val_email = true;
					}
					//Invalid
					else {
						$email.addClass('invalid');
						$emailTip.addClass('invalid');
						$emailTip.html(ejabat.invalid_email);
						val_email = false;
					}
				},
				error: function() {
					$email.addClass('invalid');
					$emailTip.addClass('invalid');
					$emailTip.html(ejabat.error);
					val_email = false;
				}
			});
		}
	}
	//Empty field
	else {
		$email.addClass('invalid');
		$emailTip.addClass('invalid');
		$emailTip.html(ejabat.empty_field);
		val_email = false;
	}
}

//Validate recaptcha
function validateRecaptcha($recaptcha, $recaptchaInput, $recaptchaTip) {
	var recaptcha = $recaptcha.val();
	//Valid
	if(recaptcha || (recaptcha==null)) {
		$recaptchaInput.removeClass('invalid');
		$recaptchaTip.removeClass('invalid');
		$recaptchaTip.empty();
		val_recaptcha = true;
	}
	//Invalid
	else {
		$recaptchaInput.addClass('invalid');
		$recaptchaTip.addClass('invalid');
		$recaptchaTip.html(ejabat.recaptcha_verify);
		val_recaptcha = false;
	}
}

//Get URL parameter
function GetURLParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
}​

jQuery(document).ready(function($) {
	//Load registration form
	$.ajax({
		method: 'POST',
		url: ejabat.ajax_url + '?action=ejabat_register_form',
		timeout: ejabat.timeout,
		data: 'referer=' + window.location.pathname + '&host=' + GetURLParameter('host') + '&code=' + GetURLParameter('code'),
		dataType: 'json',
		success: function(response) {
			$('#ejabat_register_form').replaceWith(response.data);
		},
		error: function() {
			$('#ejabat_register_form').replaceWith(ejabat.form_error);
		},
		complete: function(jqXHR, status) {
			if(status == 'success') {
				//Validate login
				$('#login input').on('change', function() {
					$(this).val($(this).val().toLowerCase().trim());
					validateLogin($(this), $('#host select'), $('#login span'));
				});
				$('#host select').on('change', function() {
					$('#login input').val($('#login input').val().toLowerCase().trim());
					validateLogin($('#login input'), $(this), $('#login span'));
				});
				//Validate password
				$('#password input').on('keyup change', function(e) {
					var charCode = e.which || e.keyCode;
					if(!((charCode === 9) || (charCode === 16))) {
						validatePassword($(this), $('#password span'));
					}
				});
				$('#password_retyped input').on('change', function() {
					validatePasswordRetyped($('#password input'), $(this), $('#password_retyped span'));
				});
				//Validate email
				$('#email input').on('change', function(e) {
					validateEmail($(this), $('#email span'));
				});
				//Submit
				$("#ejabat_register").submit(function() {
					//Remove response message
					$('#response').css('display', '');
					$('#response').removeClass('ejabat-form-blocked ejabat-form-error ejabat-form-success');
					$('#response').empty();
					//Show spinner
					$('#spinner').css('visibility', 'visible');
					//Validate recaptcha
					validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
					//Validation errors
					if(!val_login || !val_password || !val_password_retyped || !val_email || !val_recaptcha) {
						//Empty login
						if(!val_login) {
							if(!$('#login input').val()) {
								$('#login input').addClass('invalid');
								$('#login span').addClass('invalid');
								$('#login span').html(ejabat.empty_field);
							}
						}
						//Empty password
						if(!val_password) {
							if(!$('#password input').val()) {
								$('#password input').addClass('invalid');
								$('#password span').addClass('invalid');
								$('#password span').html(ejabat.empty_field);
							}
						}
						//Empty retyped password
						if(!val_password_retyped) {
							if(!$('#password_retyped input').val()) {
								$('#password_retyped input').addClass('invalid');
								$('#password_retyped span').addClass('invalid');
								$('#password_retyped span').html(ejabat.empty_field);
							}
						}
						//Empty email
						if(!val_email) {
							if(!$('#email input').val()) {
								$('#email input').addClass('invalid');
								$('#email span').addClass('invalid');
								$('#email span').html(ejabat.empty_field);
							}
						}
						//Add error response message
						$('#response').css('display', 'inline-block');
						$('#response').addClass('ejabat-form-blocked');
						$('#response').html(ejabat.empty_fields);
						//Hide spinner
						$('#spinner').css('visibility', 'hidden');
					}
					else {
						//Send data
						$.ajax({
							method: 'POST',
							url: ejabat.ajax_url + '?action=ejabat_register',
							timeout: ejabat.timeout,
							data: $('#ejabat_register').serialize(),
							dataType: 'json',
							success: function(response) {
								//Success
								if(response.status == 'success') {
									$('#ejabat_register')[0].reset();
									$('#password input').removeClass('weak good strong');
									$('#password span').empty();
								}
								$('#response').css('display', 'inline-block');
								$('#response').addClass('ejabat-form-'+response.status);
								$('#response').html(response.message);
								//Reset recaptcha
								( typeof Recaptcha != 'undefined' && Recaptcha.reload() );
								( typeof grecaptcha != 'undefined' && grecaptcha.reset() );
								//Hide spinner
								$('#spinner').css('visibility', 'hidden');
							},
							error: function() {
								//Add error response message
								$('#response').css('display', 'inline-block');
								$('#response').addClass('ejabat-form-error');
								$('#response').html(ejabat.error);
								//Hide spinner
								$('#spinner').css('visibility', 'hidden');
							}
						});
					}
				});
			}
		}
	});
});