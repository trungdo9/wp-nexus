jQuery(document).ready(function($) {
	// Keyword autocomplete
	var keywordInput = $('#seo-nexus-keyword');

	if (keywordInput.length) {
		keywordInput.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: wpNexus.ajaxurl,
					type: 'GET',
					dataType: 'json',
					data: {
						action: 'wp_nexus_get_keywords',
						term: request.term,
						nonce: wpNexus.nonce
					},
					success: function(data) {
						response(data.data);
					},
					error: function() {
						response([]);
					}
				});
			},
			minLength: 1,
			delay: 300,
			select: function(event, ui) {
				// Optional: handle selection
			}
		});
	}

	// Parent keyword autocomplete
	var parentKeywordInput = $('#seo-nexus-parent-keyword');

	if (parentKeywordInput.length) {
		parentKeywordInput.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: wpNexus.ajaxurl,
					type: 'GET',
					dataType: 'json',
					data: {
						action: 'wp_nexus_get_keywords',
						term: request.term,
						nonce: wpNexus.nonce
					},
					success: function(data) {
						response(data.data);
					},
					error: function() {
						response([]);
					}
				});
			},
			minLength: 1,
			delay: 300,
			select: function(event, ui) {
				// Optional: handle selection
			}
		});
	}
});
