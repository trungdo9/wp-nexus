# Plan: WP Nexus SEO Integration

**Date:** 2026-02-21
**Description:** Integrate with Yoast SEO and Rank Math for keyword sync and SEO scores display
**Priority:** High

## Overview
Sync keywords from Yoast SEO / Rank Math to WP Nexus and display SEO scores in sitemap

## Requirements
1. Auto-sync focus keywords from Yoast/Rank Math to WP Nexus (1-way)
2. Bulk sync button for old posts
3. Display SEO scores (total + improvements needed) in sitemap
4. Support both plugins, handle cases where one/both are active

## Architecture

### Sync Logic
```
save_post hook
    → Check Rank Math keyword (priority 1)
    → If empty, check Yoast keyword (priority 2)
    → If WP Nexus keyword empty, auto-fill
    → Save to _seo_nexus_keyword
```

### SEO Score Sources
| Plugin | Score Key | Issues Key |
|--------|-----------|------------|
| Yoast | `_yoast_wpseo_score` | `_yoast_wpseo_results` |
| Rank Math | `rank_math_seo_score` | `rank_math_validation_notice` |

### Display Format
- Total Score: 0-100 with color indicator
- Issues: List of improvements needed (max 3-5)

## Implementation Phases
| Phase | Name | Status |
|-------|------|--------|
| 1 | Keyword Sync | Pending |
| 2 | SEO Scores Display | Pending |

## Files Modified
- `includes/class-meta-box.php` - Add sync on save
- `includes/class-admin-page.php` - Add score display
