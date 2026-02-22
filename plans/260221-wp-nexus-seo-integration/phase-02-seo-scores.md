# Phase 2: SEO Scores Display

**Date:** 2026-02-21
**Description:** Display SEO scores in sitemap
**Status:** Pending

## Context
- Parent Plan: plan.md
- Dependency: Phase 1

## Requirements
1. Show total SEO score (0-100) with color indicator
2. Show key improvements needed
3. Support both Yoast and Rank Math

## SEO Score Sources

### Yoast SEO
```php
// Score (0-100 or 'na')
$score = get_post_meta($post_id, '_yoast_wpseo_score', true);

// Issues/Results
$results = get_post_meta($post_id, '_yoast_wpseo_results', true);
// Format: array with 'msg' and 'msg微观' keys
```

### Rank Math
```php
// Score (0-100)
$score = get_post_meta($post_id, 'rank_math_seo_score', true);

// Validation issues
$issues = get_post_meta($post_id, 'rank_math_validation_notice', true);
// Format: array of validation items
```

## Score Colors
| Score | Color | Label |
|-------|-------|-------|
| 80-100 | Green | Good |
| 50-79 | Orange | Needs Work |
| 0-49 | Red | Poor |
| N/A | Gray | Not Available |

## Implementation

### PHP: Get SEO Data
```php
private function get_seo_score( $post_id ) {
    $result = array(
        'score'   => null,
        'source'  => null,
        'issues'  => array(),
    );

    // Check Rank Math first (higher priority if available)
    $score = get_post_meta( $post_id, 'rank_math_seo_score', true );
    if ( $score !== '' && $score !== false ) {
        $result['score']  = intval( $score );
        $result['source'] = 'rankmath';

        // Get validation issues
        $validation = get_post_meta( $post_id, 'rank_math_validation_notice', true );
        if ( is_array( $validation ) && ! empty( $validation ) ) {
            $result['issues'] = array_slice( $validation, 0, 3 );
        }
        return $result;
    }

    // Check Yoast
    $score = get_post_meta( $post_id, '_yoast_wpseo_score', true );
    if ( $score !== '' && $score !== false && $score !== 'na' ) {
        $result['score']  = intval( $score );
        $result['source'] = 'yoast';

        // Get results/issues
        $results = get_post_meta( $post_id, '_yoast_wpseo_results', true );
        if ( is_array( $results ) && ! empty( $results ) ) {
            // Extract issue messages
            foreach ( $results as $issue ) {
                if ( isset( $issue['msg'] ) ) {
                    $result['issues'][] = $issue['msg'];
                }
            }
            $result['issues'] = array_slice( $result['issues'], 0, 3 );
        }
        return $result;
    }

    return $result;
}
```

### HTML: Display in Sitemap
```php
// In render_sitemap node
$seo = $this->get_seo_score( $post_id );

if ( $seo['score'] !== null ) :
    $color_class = $seo['score'] >= 80 ? 'good' : ( $seo['score'] >= 50 ? 'ok' : 'poor' );
    ?>
    <span class="seo-score <?php echo esc_attr( $color_class ); ?>">
        <?php echo esc_html( $seo['score'] ); ?>
    </span>
    <?php if ( ! empty( $seo['issues'] ) ) : ?>
        <span class="seo-issues" title="<?php echo esc_attr( implode( ', ', $seo['issues'] ) ); ?>">
            ⚠️
        </span>
    <?php endif; ?>
<?php endif; ?>
```

### CSS
```css
.seo-score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
}

.seo-score.good {
    background: #4caf50;
    color: white;
}

.seo-score.ok {
    background: #ff9800;
    color: white;
}

.seo-score.poor {
    background: #f44336;
    color: white;
}

.seo-score.na {
    background: #9e9e9e;
    color: white;
}

.seo-issues {
    margin-left: 4px;
    cursor: help;
}
```

## Display in Sitemap
Each node will show:
- Post title + keyword (existing)
- SEO score badge (colored circle)
- Warning icon if issues exist (tooltip with details)

## Todo
- [ ] Add get_seo_score() method
- [ ] Update render_sitemap to show scores
- [ ] Add CSS for score badges
- [ ] Add tooltip for issues
