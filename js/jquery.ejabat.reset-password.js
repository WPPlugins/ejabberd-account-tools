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
var val_login, val_password, val_password_retyped, val_recaptcha;

//Validate login
function validateLogin($login, $loginTip) {
	//Reset
	$login.removeClass('invalid');
	$loginTip.removeClass('invalid');
	$loginTip.empty();
	//Empty field
	if(!$login.val()) {
		$login.addClass('invalid');
		$loginTip.addClass('invalid');
		$loginTip.html(ejabat.empty_field);
		val_login = false;
	}
	else val_login = true;
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
	//Load reset password form
	$.ajax({
		method: 'POST',
		url: ejabat.ajax_url + '?action=ejabat_reset_password_form',
		timeout: ejabat.timeout,
		data: 'referer=' + window.location.pathname + '&code=' + GetURLParameter('code'),
		dataType: 'json',
		success: function(response) {
			$('#ejabat_reset_password_form').replaceWith(response.data);
		},
		error: function() {
			$('#ejabat_reset_password_form').replaceWith(ejabat.form_error);
		},
		complete: function(jqXHR, status) {
			if(status == 'success') {
				//Validate login
				$('#login input').on('change', function() {
					$(this).val($(this).val().toLowerCase().trim());
					validateLogin($(this), $('#login span'));
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
				//Submit reset password form
				$("#ejabat_reset_password").submit(function() {
					//Remove response message
					$('#response').css('display', '');
					$('#response').removeClass('ejabat-form-blocked ejabat-form-error ejabat-form-success');
					$('#response').empty();
					//Show spinner
					$('#spinner').css('visibility', 'visible');
					//Validate recaptcha
					validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
					//Validation errors
					if(!val_login || !val_recaptcha) {
						//Empty login
						if(!val_login) {
							if(!$('#login input').val()) {
								$('#login input').addClass('invalid');
								$('#login span').addClass('invalid');
								$('#login span').html(ejabat.empty_field);
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
							url: ejabat.ajax_url + '?action=ejabat_reset_password',
							timeout: ejabat.timeout,
							data: $('#ejabat_reset_password').serialize(),
							dataType: 'json',
							success: function(response) {
								//Success
								if(response.status == 'success') {
									$('#ejabat_reset_password')[0].reset();
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
				//Submit change password form
				$("#ejabat_change_password").submit(function() {
					//Remove response message
					$('#response').css('display', '');
					$('#response').removeClass('ejabat-form-blocked ejabat-form-error ejabat-form-success');
					$('#response').empty();
					//Show spinner
					$('#spinner').css('visibility', 'visible');
					//Validate recaptcha
					validateRecaptcha($('#g-recaptcha-response'), $('#g-recaptcha-0'), $('#recaptcha'));
					//Validation errors
					if(!val_password || !val_password_retyped || !val_recaptcha) {
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
							url: ejabat.ajax_url + '?action=ejabat_reset_password',
							timeout: ejabat.timeout,
							data: $('#ejabat_change_password').serialize(),
							dataType: 'json',
							success: function(response) {
								//Success
								if(response.status == 'success') {
									$('#ejabat_change_password')[0].reset();
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