/* simple jquery plugin for the mail chimp ajax form */

(function ($, undefined) {
	
	var defaults = {
		/*emailField : '#email',
		mergeFields : [
		               {'FNAME': '#fname'},
		               {'LNAME': '#lname'}
		              ],*/
		complete : $.noop,
		error: $.noop,
		url: '/symphony/extension/mailchimp/subscribe/'
	};
	
	// actual plusgin
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
				
				/*var data = {
						email : $(opts.emailField, t).val()
				}
				
				// merge fields
				for (var m in opts.mergeFields) {
					alert(m);
					
					var f = opts.mergeFields[m],
						val = $(f[1], t).val();
					
					data['merge['+f[0]+']'] = val;
				}*/
				
				// gets the POST params
				var data = t.serialize();
				
				// adds the bouton field
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
					error: opts.error
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