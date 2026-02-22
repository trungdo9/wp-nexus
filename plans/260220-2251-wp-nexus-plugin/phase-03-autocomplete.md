# Phase 3: Autocomplete Functionality

**Date:** 2026-02-20
**Description:** Implement keyword autocomplete with AJAX
**Priority:** High
**Status:** Completed
**Review Status:** Not Reviewed

## Context Links
- Parent Plan: [plan.md](./plan.md)
- Dependency: Phase 2 - Admin Meta Box UI

## Key Insights
- Use WordPress AJAX API for autocomplete
- Collect all unique keywords from existing posts
- Allow creating new keywords

## Requirements
1. Create AJAX endpoint for fetching existing keywords
2. Implement jQuery UI Autocomplete or custom solution
3. Handle new keyword creation
4. Enqueue scripts and styles properly

## Architecture

### AJAX Actions
- `wp_nexus_get_keywords` - Fetch existing keywords (returns JSON array)
- Parameters: `term` - search query

### Keyword Manager Class
```php
class WP_Nexus_Keyword_Manager {
    public function get_keywords( $term = '' ) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM $wpdb->postmeta
             WHERE meta_key = '_seo_nexus_keyword'
             AND meta_value LIKE %s
             ORDER BY meta_value ASC",
            '%' . $wpdb->esc_like( $term ) . '%'
        );
        return $wpdb->get_col( $query );
    }
}
```

## Related Code Files
- `includes/class-keyword-manager.php`
- `assets/js/admin.js`
- `assets/css/admin.css`

## Implementation Steps

### Step 1: Create keyword manager class
```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Nexus_Keyword_Manager {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_wp_nexus_get_keywords', array( $this, 'ajax_get_keywords' ) );
        add_action( 'wp_ajax_wp_nexus_add_keyword', array( $this, 'ajax_add_keyword' ) );
    }

    public function get_keywords( $term = '' ) {
        global $wpdb;

        if ( empty( $term ) ) {
            return array();
        }

        $query = $wpdb->prepare(
            "SELECT DISTINCT pm.meta_value
             FROM $wpdb->postmeta pm
             INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
             WHERE pm.meta_key = '_seo_nexus_keyword'
             AND pm.meta_value LIKE %s
             AND p.post_status = 'publish'
             ORDER BY pm.meta_value ASC
             LIMIT 20",
            '%' . $wpdb->esc_like( $term ) . '%'
        );

        return $wpdb->get_col( $query );
    }

    public function ajax_get_keywords() {
        check_ajax_referer( 'wp_nexus_nonce', 'nonce' );

        $term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';
        $keywords = $this->get_keywords( $term );

        wp_send_json_success( $keywords );
    }

    public function ajax_add_keyword() {
        // For future expansion - add custom keyword storage
        wp_send_json_success( array( 'message' => 'Keyword added' ) );
    }
}
```

### Step 2: Create admin.js for autocomplete
```javascript
jQuery(document).ready(function($) {
    var keywordInput = $('#seo-nexus-keyword');

    if (keywordInput.length) {
        keywordInput.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'wp_nexus_get_keywords',
                        term: request.term,
                        nonce: wpNexus.nonce
                    },
                    success: function(data) {
                        response(data.data);
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
```

### Step 3: Create CSS for styling
```css
#wp-nexus-meta-box .ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    z-index: 100000;
}
```

## Todo List
- [ ] Create class-keyword-manager.php
- [ ] Implement get_keywords() query
- [ ] Add AJAX handlers
- [ ] Create admin.js with autocomplete
- [ ] Create admin.css for styling
- [ ] Enqueue scripts and localize data

## Success Criteria
- Typing in keyword field shows autocomplete suggestions
- Only existing keywords are shown (matching search term)
- AJAX requests are authenticated with nonce

## Risk Assessment
- Low: Standard WordPress AJAX implementation

## Security Considerations
- Use nonce verification for AJAX requests
- Sanitize search term input
- Limit results to prevent large data transfer
