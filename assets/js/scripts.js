(function ($) {
	$.ajax({
		type: "POST",
		url: frontend_ajax_object.ajaxurl,
		dataType: "json",
		data: { action: "get_ajax_posts" },
		success: function (response) {
			$.each(response, function (key, value) {
				console.log(key, value);
			});
		},
	});
})(jQuery);
