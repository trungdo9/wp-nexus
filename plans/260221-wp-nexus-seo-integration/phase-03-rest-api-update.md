# Plan: REST API Update - Filter by Type & Add post_type

**Date:** 2026-02-21
**Description:** Update REST API to filter by nexus type and include post_type
**Priority:** High
**Status:** Pending

## Context
- Parent: WP Nexus Plugin
- Current endpoint: `/wp-json/seo-nexus/v1/links`

## Requirements
1. Filter posts by nexus type: pillar, sub-pillar, cluster
2. Return all public post types (not limited)
3. Add `post_type` attribute in response

## Current Response Format
```json
[
  {"url": "...", "type": "pillar"},
  {"url": "...", "type": "sub-pillar"}
]
```

## New Response Format
```json
[
  {"url": "...", "type": "pillar", "post_type": "post"},
  {"url": "...", "type": "sub-pillar", "post_type": "page"},
  {"url": "...", "type": "cluster", "post_type": "manga"}
]
```

## Implementation

### Changes to class-rest-api.php

1. **Update get_links() method**:
   - Remove single post_type filter
   - Get all public post types
   - Add post_type to response

```php
public function get_links( $request ) {
    $type = $request->get_param( 'type' );

    // Get all public post types
    $post_types = get_post_types( array( 'public' => true ), 'names' );

    $args = array(
        'post_type'      => array_values( $post_types ),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    // Filter by type if provided
    if ( $type && in_array( $type, array( 'pillar', 'sub-pillar', 'cluster' ), true ) ) {
        $args['meta_query'] = array(
            array(
                'key'   => '_seo_nexus_type',
                'value' => $type,
            ),
        );
    } else {
        // Get all types if no filter
        $args['meta_query'] = array(
            array(
                'key'     => '_seo_nexus_type',
                'value'   => array( 'pillar', 'sub-pillar', 'cluster' ),
                'compare' => 'IN',
            ),
        );
    }

    $query = new WP_Query( $args );
    $links = array();

    foreach ( $query->posts as $post ) {
        $seo_nexus_type = get_post_meta( $post->ID, '_seo_nexus_type', true );

        if ( $seo_nexus_type ) {
            $links[] = array(
                'url'       => get_permalink( $post->ID ),
                'type'      => $seo_nexus_type,
                'post_type' => $post->post_type,
            );
        }
    }

    wp_reset_postdata();

    return rest_ensure_response( $links );
}
```

## API Usage

### Get all links
```
GET /wp-json/seo-nexus/v1/links
Header: X-API-Key: 1234567890
```

### Filter by type
```
GET /wp-json/seo-nexus/v1/links?type=pillar
GET /wp-json/seo-nexus/v1/links?type=sub-pillar
GET /wp-json/seo-nexus/v1/links?type=cluster
```

## Todo
- [ ] Update get_links() to include post_type
- [ ] Ensure type filter works correctly
- [ ] Test API with filters
