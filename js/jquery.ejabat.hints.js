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

jQuery(document).ready(function($) {
	//Set hint position
	var hintPos;
	function setHintsPosition() {
		if($(window).innerWidth() <= 767) {
			hintPos = 'hint--top';
		}
		else {
			hintPos = 'hint--right';
		}
	}
	setHintsPosition();
	//Change hint position
	$(window).on('resize', function() {
		var _hintPos = hintPos;
		setHintsPosition();
		$('.' + _hintPos).removeClass(_hintPos).addClass(hintPos);
	});
	//Login hint
	$(this).on('focusin', '#login.hints input', function() {
		$('#login').addClass('hint--always hint--info ' + hintPos).attr('aria-label', ejabat_hints.login);
	});
	$(this).on('focusout', '#login.hints input', function() {
		$('#login').removeAttr('class').addClass('hints').removeAttr('aria-label');
	});
	//Password hint
	if(ejabat_hints.password_strength > 0) {
		$(this).on('focusin', '#password.hints input', function() {
			$('#password').addClass('hint--always hint--info ' + hintPos).attr('aria-label', ejabat_hints.password);
		});
		$(this).on('focusout', '#password.hints input', function() {
			$('#password').removeAttr('class').addClass('hints').removeAttr('aria-label');
		});
	}
	//Email hint
	$(this).on('focusin', '#email.hints input', function() {
		$('#email').addClass('hint--always hint--info ' + hintPos).attr('aria-label', ejabat_hints.email);
	});
	$(this).on('focusout', '#email.hints input', function() {
		$('#email').removeAttr('class').addClass('hints').removeAttr('aria-label');
	});
});