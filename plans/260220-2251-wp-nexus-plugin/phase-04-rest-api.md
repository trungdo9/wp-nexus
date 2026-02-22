# Phase 4: REST API Endpoint

**Date:** 2026-02-20
**Description:** Create REST API endpoint for retrieving pillar and sub-pillar posts
**Priority:** High
**Status:** Completed
**Review Status:** Not Reviewed

## Context Links
- Parent Plan: [plan.md](./plan.md)
- Dependency: Phase 1 - Plugin Structure

## Key Insights
- Use WordPress REST API framework
- Require X-API-Key header for authentication
- Return XML-sitemap-style format

## Requirements
1. Register REST route: `/wp-json/seo-nexus-links`
2. Authenticate using `X-API-Key` header
3. Return posts with nexus type 'pillar' and 'sub-pillar'
4. Format: similar to XML sitemap with url, seo-nexus-type

## Architecture

### REST Route
```php
register_rest_route( 'seo-nexus/v1', '/links', array(
    'methods' => 'GET',
    'callback' => array( $this, 'get_links' ),
    'permission_callback' => array( $this, 'check_api_key' ),
) );
```

### Response Format
```xml
<urlset>
  <url>
    <loc>https://example.com/post-slug/</loc>
    <type>pillar</type>
  </url>
  ...
</urlset>
```

### JSON Response Alternative
```json
{
  "links": [
    {
      "url": "https://example.com/post-slug/",
      "type": "pillar"
    }
  ]
}
```

## Related Code Files
- `includes/class-rest-api.php`

## Implementation Steps

### Step 1: Create REST API class
```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Nexus_REST_API {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'seo-nexus/v1', '/links', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_links' ),
            'permission_callback' => array( $this, 'check_api_key' ),
            'args' => array(
                'type' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );
    }

    public function check_api_key() {
        $headers = isset( $_SERVER ) ? $this->get_headers_from_apache_or_php() : array();

        if ( isset( $headers['X-API-KEY'] ) ) {
            $api_key = $headers['X-API-KEY'];
        } elseif ( isset( $_SERVER['HTTP_X_API_KEY'] ) ) {
            $api_key = $_SERVER['HTTP_X_API_KEY'];
        } else {
            return false;
        }

        return $api_key === WP_NEXUS_API_KEY;
    }

    private function get_headers_from_apache_or_php() {
        if ( function_exists( 'apache_request_headers' ) ) {
            $headers = apache_request_headers();
            $headers = array_combine( array_map( 'ucwords', array_keys( $headers ) ), array_values( $headers ) );
        } else {
            $headers = $_SERVER;
        }

        return $headers;
    }

    public function get_links( $request ) {
        $type = $request->get_param( 'type' );

        $post_types = get_post_types( array( 'public' => true ), 'names' );

        $args = array(
            'post_type' => array_values( $post_types ),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_seo_nexus_type',
            'meta_value' => array( 'pillar', 'sub-pillar' ),
            'compare' => 'IN',
        );

        if ( $type && in_array( $type, array( 'pillar', 'sub-pillar' ) ) ) {
            $args['meta_value'] = $type;
        }

        $query = new WP_Query( $args );
        $links = array();

        while ( $query->have_posts() ) {
            $query->the_post();
            $seo_nexus_type = get_post_meta( get_the_ID(), '_seo_nexus_type', true );

            if ( $seo_nexus_type ) {
                $links[] = array(
                    'url' => get_permalink(),
                    'type' => $seo_nexus_type,
                );
            }
        }

        wp_reset_postdata();

        return rest_ensure_response( $links );
    }
}
```

## Todo List
- [ ] Create class-rest-api.php
- [ ] Implement register_routes()
- [ ] Implement check_api_key() authentication (hardcoded)
- [ ] Implement get_links() for all public post types
- [ ] Handle X-API-Key header only

## Success Criteria
- API endpoint `/wp-json/seo-nexus-links` is accessible
- Requests with valid API key return data
- Requests without/invalid API key return 401
- Response includes url and type for each post
- Supports all public post types

## Risk Assessment
- Low: API key is hardcoded, only header authentication

## Security Considerations
- API key transmitted via X-API-Key header only
- Consider rate limiting for production
- Use HTTPS in production
