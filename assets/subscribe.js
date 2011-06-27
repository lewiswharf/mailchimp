/**
 * Simple jquery plugin for the mail chimp ajax form
 * 
 * As for the normal use of this extention, be sure to include all merge fields
 * 
 * Basic Usage
 * 
 * $('#the-form').mailchimp({
 * 		complete: completeCallback(data), // this is set to #the-form
 * 		error: errorCallback(data) // data.error -> error message
 * });
 * 
 * @author nicolasbrassard - http://www.nitriques.com
 *  */

(function ($, undefined) {
	
	var defaults = {
		complete : $.noop,
		error: $.noop,
		url: '/symphony/extension/mailchimp/subscribe/'
	};
	
	// actual plugin
	function mailchimp(options) {
		var t = $(this),
			opts = $.extend({}, defaults, options);
		
		if (!t || !t.length) {
			return this;
		}
		
		function hookOne(index, value) {
			var t = $(this); // current element, represents the form container
				
				
			// actual subscription
			function ajax(e) {
				
				if (e) {
					e.preventDefault();
				}
				
				// gets the POST params
				var data = t.serialize();
				
				// adds the button field
				data += '&' + escape('action[signup]') + '=Send';
			
				// ajax request
				$.ajax({
					type: 'POST',
					url: opts.url,
					data: data,
					dataType: 'json',
					success: function (data) {
					
						if (!data.error && data['@attributes'] && data['@attributes'].result == 'success') {
							
							if (data['@attributes'].result) {
								
								if ($.isFunction(opts.complete)) {
									opts.complete.call(t, data);
								}
							} 
							
						} else {
							
							if ($.isFunction(opts.error)) {
								opts.error.call(t, data);
							}
						}
					} ,
					error: function (data) {
						
						if ($.isFunction(opts.error)) {
							opts.error.call(t, data);
						}
					}
				});
				
				return false;
			
			};
			
			// hook submit form
			t.submit(ajax);
		}
		
		return t.each(hookOne);
	};
	
	// extend fn object
	// should be called on <form> element
	$.fn.extend({
		mailchimp: mailchimp
	});
	
})(jQuery);