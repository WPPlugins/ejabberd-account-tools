=== Ejabberd Account Tools ===
Contributors: Beherit
Tags: xmpp, jabber, ejabberd
Donate link: https://beherit.pl/en/donations
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.9
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Provide ejabberd account tools such as the registration form, deleting the account, resetting the account password.

== Description ==
Provide ejabberd account tools such as the registration form, deleting the account, resetting the account password. The plugin uses ReST API (by using module mod_http_api), is useful when the XMPP server is located on another machine. Easy to configure and use - just need to type ReST API url and insert shortcodes on the page.

Plugin to work needs to install the [WordPress ReCaptcha Integration](https://wordpress.org/plugins/wp-recaptcha-integration/).

== Installation ==
This section describes how to install the plugin and get it working.

1. Install Ejabberd Account Tools either via the WordPress.org plugin directory, or by uploading the files to your server.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==
No questions yet.

== Changelog ==
= 1.9 (2017-05-06) =
* Minimize the number of requests to validator.pizza.
* Loading minified files only if SCRIPT_DEBUG isn't defined.
* Visual changes on the settings page.
* FontAwesome updated to 4.7.0.
* Hint.css updated to 2.4.1.
* Deleting all data after uninstalling the plugin.
* Other minor changes and improvements.
= 1.8 (2016-11-14) =
* Added two-step registration.
* Added blocking of disposable email addresses.
* Verify SSL in ReST API connections.
* Added option to temporarily disable all the forms for not logged-in users.
* Fix display of the hints.
* Visual changes in settings page.
* Other minor changes and improvements.
= 1.7 (2016-06-21) =
* Loading forms via Ajax (cache support).
* FontAwesome updated to 4.6.3.
* Hint.css updated to 2.3.1.
* Other minor changes and improvements.
= 1.6.2 (2016-04-27) =
* Better connection errors handling.
= 1.6 (2016-04-25) =
* Changed method of getting data from mod_rest to mod_http_api (core ReST API with basic authentication).
* Added retrying the connection in post data.
* Minor changes and fixes.
= 1.5.2 (2016-03-24) =
* Minor bugfix in email notifications.
= 1.5 (2016-03-14) =
* Added welcome message that is sent to each newly registered account.
* Removing expired transients in daily cron job.
* Ability to change the required password strength.
* Hint.css updated to 2.2.0.
* Minor changes in settings page.
* Small changes in CSS.
= 1.4 (2015-12-23) =
* Added vhosts support in registration.
* Added connection timeout settings.
* Added tools menu and function to manually change private email address.
* Remove angle brackets from URLs in email notifications.
* Fix date formatting in registration watcher.
* Minor changes in jQuery scripts.
* Changed language domain to ejabberd-account-tools to work with WordPress new translation process.
* Updated FontAwesome.
= 1.3.1 (2015-08-08) =
* Update FontAwesome.
= 1.3 (2015-07-23) =
* Added form to resetting the account password.
* Added form to deleting the account.
* Removing incorrect parameters from URL added to the emails.
* Changed the method of adding hints.
* Checking current private email address before sending message to change it.
* Repair captcha validation.
* Changed the form-response box style.
* Rename scripts files.
* Translation of the plugin metadata.
* Updated translations.
* Minor bugfix and changes.
= 1.2 (2015-06-30) =
* Added ability to show information hints on forms.
* Added more data to transients.
* Changes in default blocked logins regexp.
* Getting the properly default email address.
* Validating email address by checking MX record.
* Added vhosts support in changing email.
* Other minor changes.
= 1.1.2 (2015-06-24) =
* Removing slashes from the passwords.
* Improved post data.
* Minor changes in sending mails.
= 1.1 (2015-06-24) =
* The ability to change/add private email address.
* Turn off autocomplete on registration form.
* Properly added a link to the settings on plugins page.
* Small changes in translations.
* Minor visual changes.
= 1.0.2 (2015-06-08) =
* Checking if selected login exists or not.
* Major changes in jQuery validation.
* Minify jQuery script.
* Additional verification in ajax to avoid cheating jQuery script.
* Proper resetting the form after success registration.
* Minor changes in translation.
= 1.0 (2015-06-06) =
* First public version.

== Upgrade Notice ==
= 1.9 =
* Minimize the number of requests to validator.pizza.
* Load minified files only if SCRIPT_DEBUG isn't defined.
* Visual changes on the settings page.
* FontAwesome updated to 4.7.0.
* Hint.css updated to 2.4.1.
* Deleting all data after uninstalling the plugin.
* Other minor changes and improvements.
= 1.8 =
* Added two-step registration.
* Added blocking of disposable email addresses.
* Verify SSL in ReST API connections.
* Added option to temporarily disable all the forms for not logged-in users.
* Fix display of the hints.
* Visual changes in settings page.
* Other minor changes and improvements.
= 1.7 =
* Loading forms via Ajax (cache support).
* FontAwesome updated to 4.6.3.
* Hint.css updated to 2.3.1.
* Other minor changes and improvements.
= 1.6.2 =
* Better connection errors handling.
= 1.6 =
* Changed method of getting data from mod_rest to mod_http_api (core ReST API with basic authentication).
* Added retrying the connection in post data.
* Minor changes and fixes.
= 1.5.2 =
* Minor bugfix in email notifications.
= 1.5 =
* Added welcome message that is sent to each newly registered account.
* Removing expired transients in daily cron job.
* Ability to change the required password strength.
* Hint.css updated to 2.2.0.
* Minor changes in settings page.
* Small changes in CSS.
= 1.4 =
* Added vhosts support in registration.
* Added connection timeout settings.
* Added tools menu and function to manually change private email address.
* Remove angle brackets from URLs in email notifications.
* Fix date formatting in registration watcher.
* Minor changes in jQuery scripts.
* Updated FontAwesome.
= 1.3.1 =
* Update FontAwesome.
= 1.3 =
* Added form to resetting the account password.
* Added form to deleting the account.
* Removing incorrect parameters from URL added to the emails.
* Changed the method of adding hints.
* Checking current private email address before sending message to change it.
* Repair captcha validation.
* Changed the form-response box style.
* Rename scripts files.
* Translation of the plugin metadata.
* Updated translations.
* Minor bugfix and changes.
= 1.2 =
* Added ability to show information hints on forms.
* Added more data to transients.
* Changes in default blocked logins regexp.
* Getting the properly default email address.
* Validating email address by checking MX record.
* Added vhosts support in changing email.
* Other minor changes.
= 1.1.2 =
* Removing slashes from the passwords.
* Improved post data.
* Minor changes in sending mails.
= 1.1 =
* The ability to change/add private email address.
* Turn off autocomplete on registration form.
* Properly added a link to the settings on plugins page.
* Small changes in translations.
* Minor visual changes.
= 1.0.2 =
* Checking if selected login exists or not.
* Major changes in jQuery validation.
* Minify jQuery script.
* Additional verification in ajax to avoid cheating jQuery script.
* Proper resetting the form after success registration.
* Minor changes in translation.
= 1.0 =
* First public version.

== Other Notes ==
This plugin is using [HINT.css](https://github.com/chinchang/hint.css), [Font Awesome](https://fortawesome.github.io/Font-Awesome/) and [Loaders.css](https://github.com/ConnorAtherton/loaders.css).