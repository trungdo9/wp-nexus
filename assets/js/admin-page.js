jQuery(document).ready(function($) {
	// ============================================
	// Copy to clipboard functionality
	// ============================================
	$('.copy-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var textToCopy = $btn.data('copy');

		// Use Clipboard API
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(textToCopy).then(function() {
				showCopiedFeedback($btn);
			}).catch(function(err) {
				console.error('Failed to copy: ', err);
				fallbackCopy(textToCopy, $btn);
			});
		} else {
			// Fallback for older browsers
			fallbackCopy(textToCopy, $btn);
		}
	});

	function fallbackCopy(text, $btn) {
		var $temp = $('<input>');
		$('body').append($temp);
		$temp.val(text).select();
		try {
			document.execCommand('copy');
			showCopiedFeedback($btn);
		} catch (err) {
			console.error('Fallback copy failed: ', err);
		}
		$temp.remove();
	}

	function showCopiedFeedback($btn) {
		var originalText = $btn.text();
		$btn.text('Copied!').addClass('button-primary');
		setTimeout(function() {
			$btn.text(originalText).removeClass('button-primary');
		}, 2000);
	}

	// ============================================
	// Sitemap expand/collapse functionality
	// ============================================

	// Toggle expand/collapse on click
	$('.sitemap-node').on('click', '.toggle-icon.expandable', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $icon = $(this);
		var $node = $icon.closest('.sitemap-node');
		var $children = $node.find('> .sitemap-children').first();

		if ($children.length === 0) return;

		if ($children.is(':hidden')) {
			$children.slideDown(200);
			$icon.html('&#9660;'); // Down arrow
		} else {
			$children.slideUp(200);
			$icon.html('&#9658;'); // Right arrow
		}
	});

	// Also toggle on node header click (except on link)
	$('.sitemap-node .node-header').on('click', function(e) {
		// Don't trigger if clicking on the link
		if ($(this).find('a').is(e.target)) return;

		var $icon = $(this).find('.toggle-icon.expandable');
		if ($icon.length) {
			$icon.trigger('click');
		}
	});

	// Expand all button
	$('.expand-all-btn').on('click', function(e) {
		e.preventDefault();
		$('.sitemap-children').slideDown(200);
		$('.toggle-icon.expandable').html('&#9660;');
	});

	// Collapse all button
	$('.collapse-all-btn').on('click', function(e) {
		e.preventDefault();
		$('.sitemap-children').slideUp(200);
		$('.toggle-icon.expandable').html('&#9658;');
	});
});
