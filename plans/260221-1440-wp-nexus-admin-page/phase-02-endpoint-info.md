# Phase 2: Endpoint Info Display

**Date:** 2026-02-21
**Description:** Display API endpoint information on admin page
**Status:** Pending

## Context
- Parent Plan: plan.md
- Dependency: Phase 1

## Requirements
1. Show REST API endpoint URL
2. Show API key (masked/copyable)
3. Add copy-to-clipboard button

## Implementation

### Endpoint Info Section
```php
public function render_endpoint_info() {
    $api_url = rest_url( 'seo-nexus/v1/links' );
    $api_key = WP_NEXUS_API_KEY;
    ?>
    <div class="wp-nexus-endpoint-info">
        <h2><?php _e( 'API Endpoint', 'wp-nexus' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e( 'Endpoint URL', 'wp-nexus' ); ?></th>
                <td>
                    <code id="api-url"><?php echo esc_url( $api_url ); ?></code>
                    <button class="button copy-btn" data-copy="<?php echo esc_url( $api_url ); ?>">
                        <?php _e( 'Copy', 'wp-nexus' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'API Key', 'wp-nexus' ); ?></th>
                <td>
                    <code id="api-key"><?php echo esc_html( $api_key ); ?></code>
                    <button class="button copy-btn" data-copy="<?php echo esc_html( $api_key ); ?>">
                        <?php _e( 'Copy', 'wp-nexus' ); ?>
                    </button>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
```

### CSS for Endpoint Section
```css
.wp-nexus-endpoint-info {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ccd0d4;
}

.wp-nexus-endpoint-info code {
    background: #f1f1f1;
    padding: 4px 8px;
    border-radius: 4px;
}
```

## Todo
- [ ] Add endpoint info HTML
- [ ] Add copy button JavaScript
- [ ] Style endpoint section
