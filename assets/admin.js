/**
 * Admin JS
 * @package WordPress
 * @subpackage  Facebook Page Fans
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


(function($) {
	"use strict";

	var setUrlParameter = function(url, key, value) {
		var baseUrl = url.split('?').length === 2 ? url.split('?')[0] : url,
			urlQueryString = url.split('?').length === 2 ? '?' + url.split('?')[1] : '',
			newParam = key + '=' + value,
			params = '?' + newParam;

		// If the "search" string exists, then build params from it
		if (urlQueryString) {
			var updateRegex = new RegExp('([\?&])' + key + '[^&]*');
			var removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');

			if (typeof value === 'undefined' || value === null || value === '') { // Remove param if value is empty
				params = urlQueryString.replace(removeRegex, "$1");
				params = params.replace(/[&;]$/, "");

			} else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it
				params = urlQueryString.replace(updateRegex, "$1" + newParam);

			} else { // Otherwise, add it to end of query string
				params = urlQueryString + '&' + newParam;
			}
		}

		// no parameter was set so we don't need the question mark
		params = params === '?' ? '' : params;
		return baseUrl + params;
	};

	$(document).ready(function(){
		/* confirm action */
		$(document.body).on('click', '.fbpf_ca', function(){
			var d = $(this).data('confirm') || 'Are you sure you want to do this ?';
			if(! confirm(d)){
				return false;
			}
		});

		/* project forms */
		$(document.body).on('fbpf_settings_form/done', function($form, r){
			if (r.success) {
				window.location.href = setUrlParameter(fbpf.settingsUrl, 'message', r.message);
			}
		});

		if ($('#fbpf_settings_form').length > 0) {
			var $form = $('#fbpf_settings_form');
			$form.on('change', 'input[name="enable_test"]', function(){
				var val = $form.find('input[name="enable_test"]:checked').val();
				console.log(val);
				if ('yes' == val) {
					$form.find('.wffwi_test_access_token, .wffwi_test_facebook_page').show();
				} else {
					$form.find('.wffwi_test_access_token, .wffwi_test_facebook_page').hide();
				}
			});
			$form.find('input[name="enable_test"]').trigger('change');
		}
	});
})(jQuery);
