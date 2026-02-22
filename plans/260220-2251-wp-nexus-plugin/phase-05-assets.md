# Phase 5: Assets (CSS/JS)

**Date:** 2026-02-20
**Description:** Enqueue and organize plugin assets properly
**Priority:** Medium
**Status:** Completed
**Review Status:** Not Reviewed

## Context Links
- Parent Plan: [plan.md](./plan.md)
- Dependency: Phase 2 - Admin Meta Box UI, Phase 3 - Autocomplete

## Key Insights
- Use WordPress enqueue API for assets
- Include jQuery UI for autocomplete
- Localize script with AJAX URL and nonce

## Requirements
1. Create admin.css for meta box styling
2. Create admin.js for autocomplete functionality
3. Enqueue assets only on relevant admin screens
4. Localize scripts with necessary data

## Architecture

### Asset Loading
```php
add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

public function enqueue_assets( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

    wp_enqueue_style( 'wp-nexus-admin', WP_NEXUS_URL . 'assets/css/admin.css', array(), WP_NEXUS_VERSION );
    wp_enqueue_script( 'wp-nexus-admin', WP_NEXUS_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-autocomplete' ), WP_NEXUS_VERSION, true );

    wp_localize_script( 'wp-nexus-admin', 'wpNexus', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wp_nexus_nonce' ),
    ) );
}
```

## Related Code Files
- `assets/css/admin.css`
- `assets/js/admin.js`

## Implementation Steps

### Step 1: Create admin.css
```css
#wp-nexus-meta-box .description {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

#wp-nexus-meta-box select,
#wp-nexus-meta-box input[type="text"] {
    width: 100%;
    max-width: 100%;
}

.ui-autocomplete-loading {
    background: url('../images/loader.gif') no-repeat right center;
    background-size: 16px 16px;
}
```

### Step 2: Create admin.js with enhanced autocomplete
```javascript
jQuery(document).ready(function($) {
    var keywordInput = $('#seo-nexus-keyword');
    var selectedKeyword = null;

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
                        if (data.success) {
                            response(data.data);
                        } else {
                            response([]);
                        }
                    },
                    error: function() {
                        response([]);
                    }
                });
            },
            minLength: 1,
            delay: 300,
            select: function(event, ui) {
                selectedKeyword = ui.item.value;
                keywordInput.val(ui.item.value);
                return false;
            }
        }).data('ui-autocomplete')._renderItem = function(ul, item) {
            return $('<li>')
                .append('<a>' + item.value + '</a>')
                .appendTo(ul);
        };

        // Handle free text entry
        keywordInput.on('blur', function() {
            var value = $(this).val();
            if (value && value !== selectedKeyword) {
                // New keyword entered - allow it
                selectedKeyword = null;
            }
        });
    }
});
```

### Step 3: Add loader image
Create `assets/images/loader.gif` (16x16 animated GIF)

## Todo List
- [ ] Create assets directory structure
- [ ] Create admin.css with meta box styles
- [ ] Create admin.js with autocomplete
- [ ] Create placeholder loader.gif
- [ ] Update meta box class to enqueue assets
- [ ] Add jQuery UI dependency

## Success Criteria
- CSS is applied to meta box
- JavaScript works on post edit screens
- AJAX calls are properly authenticated
- No console errors

## Risk Assessment
- Low: Standard WordPress asset enqueue

## Security Considerations
- Nonce protection for all AJAX requests
- Use wp_ajax_ hooks properly
- Sanitize all data in JavaScript
