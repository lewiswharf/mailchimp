/**
 * Simple jquery plugin for the mail chimp ajax form
 *
 * As for the normal use of this extention, be sure to include all merge fields
 *
 * Basic Usage
 *
 * $('#the-form').mailchimp({
 * 		success: completeCallback(t, data)
 * 		complete: completeCallback(t, data), // this is set to #the-form
 * 		error: errorCallback(t, data) // data.error -> error message
 * });
 * 
 * window.mailchimp.submit($('#the-form'), {
 * 
 * });
 *
 * @author nicolasbrassard - http://www.nitriques.com
 *  */

(function ($, undefined) {
	'use strict';

	var defaults = {
		complete: $.noop,
		error: $.noop,
		url: '/symphony/extension/mailchimp/login/'
	};

	var sendForm = function (form, options, e) {

		if (e) {
			e.preventDefault();
		}

		// gets the POST params
		var data = form.serialize();

		// adds the button field
		data += '&' + window.escape('action[subscribe]') + '=Send';

		// ajax request
		$.ajax({
			type: 'POST',
			url: options.url,
			data: data,
			dataType: 'json',
			success: function (data) {
				if (!data.error && data['@attributes'] && data['@attributes'].result == 'success') {
					if (data['@attributes'].result) {
						if ($.isFunction(options.success)) {
							options.success(form, data);
						}
					}
				} else if ($.isFunction(options.error)) {
					options.error(form, data);
				}
			},
			error: function (data) {
				if ($.isFunction(options.error)) {
					options.error(form, data);
				}
			},
			complete: function (data) {
				if ($.isFunction(options.complete)) {
					options.complete(form, data);
				}
			}
		});

		return false;
	};

	// actual plugin
	var mailchimp = function (options) {
		var t = $(this);
		var opts = $.extend({}, defaults, options);

		if (!t || !t.length) {
			return this;
		}

		var hookOne = function (index) {
			return sendForm($(this), opts);
		};

		return t.each(hookOne);
	};

	// extend fn object
	// should be called on <form> element
	$.fn.extend({
		mailchimp: mailchimp
	});

	window.mailchimp = {};
	window.mailchimp.submit = function (form, options, e) {
		return sendForm(form, $.extend({}, defaults, options), e);
	};

})(jQuery);
