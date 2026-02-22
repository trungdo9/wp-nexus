---
description: Perform comprehensive SEO audit of a URL
argument-hint: <url-to-audit> [--compare <competitors>] [--trend --days=<n>] [--crawl --limit=<n>]
---

# SEO Audit: $ARGUMENTS

Use the seo-specialist agent to perform a comprehensive SEO audit.

## Workflow

1. **Fetch the page** using playwright_navigate to get HTML content
2. **Parse and analyze** the page for SEO issues
3. **Generate audit report** with score and recommendations

## Enhanced Options

| Flag | Description |
|------|-------------|
| `--compare <url1,url2>` | Compare against competitor URLs |
| `--trend --days=<n>` | Show historical trend (30, 60, 90 days) |
| `--crawl --limit=<n>` | Full site crawl (default: 50 pages) |
| `--full` | Comprehensive audit including all checks |
| `--mobile` | Mobile-specific audit |
| `--technical` | Technical SEO deep-dive only |

## Audit Process

### 1. Page Analysis
- Fetch URL: $ARGUMENTS
- Check status code, load time
- Identify HTTPS, mobile-friendliness
- Detect CDN, hosting, technology stack

### 2. On-Page SEO Check
- Title tag (50-60 chars optimal)
- Meta description (150-160 chars optimal)
- H1-H6 heading structure
- Image alt texts
- Internal/external links
- URL structure
- Open Graph tags
- Twitter cards

### 3. Technical SEO
- Core Web Vitals (estimate LCP, INP, CLS)
- Schema markup presence + validation
- Canonical tags
- robots.txt, sitemap
- hreflang tags
- Mixed content issues
- Broken links detection

### 4. Content Analysis
- Content length and quality
- Keyword usage and density
- E-E-A-T signals
- FAQ sections
- Readability score
- Content freshness

### 5. Competitor Comparison (with --compare)

Compare your page against competitors:

| Metric | Your Site | Competitor 1 | Competitor 2 |
|--------|-----------|--------------|--------------|
| SEO Score | [XX] | [XX] | [XX] |
| Word Count | [XXX] | [XXX] | [XXX] |
| Title Length | [XX] | [XX] | [XX] |
| Backlinks | [XXX] | [XXX] | [XXX] |
| Domain Authority | [XX] | [XX] | [XX] |

**Gap Analysis:**
- Competitor strengths you lack
- Opportunities they miss
- Content they have that you don't

### 6. Historical Trend (with --trend)

Track SEO metrics over time:
- Score changes
- Traffic estimates
- Ranking keywords
- Core Web Vitals progress

### 7. Site-wide Crawl (with --crawl)

Full site analysis:
- All pages audited
- Site architecture
- Internal linking structure
- Duplicate content detection
- Orphan pages
- Redirect chains

## Output Format

```
# SEO Audit: [URL]
Generated: [Date]

## OVERVIEW
URL: [url]
Status: [200 OK]
Load Time: [x.xs]
Mobile-Friendly: Yes/No
HTTPS: Yes/No
Technology: [detected stack]
Crawl Depth: [X]

## SCORE
[XX/100] - [Grade]

Previous Score: [XX/100] ([+X/-X] from last audit)

## COMPETITOR COMPARISON (with --compare)

### Score Comparison
| Metric | You | [Comp1] | [Comp2] |
|--------|-----|---------|---------|
| SEO Score | [XX] | [XX] | [XX] |
| Word Count | [XXX] | [XXX] | [XXX] |
| Schema | [Yes/No] | [Yes/No] | [Yes/No] |
| Images Optimized | [XX%] | [XX%] | [XX%] |

### Gap Analysis
**You Rank, They Don't:**
- [Keyword/Feature]

**They Rank, You Don't:**
- [URL] - [Keyword]
- [URL] - [Keyword]

**Shared Opportunities:**
- [Feature both miss]

## HISTORICAL TREND (with --trend)

| Date | Score | Change |
|------|-------|--------|
| [Date] | [XX] | [+X] |
| [Date] | [XX] | [-X] |
| [Date] | [XX] | [0] |

## CRITICAL ISSUES (Fix Immediately)
- [Issue 1] - [Impact]
- [Issue 2] - [Impact]

## HIGH PRIORITY (Fix This Week)
- [Issue 1] - [Impact]
- [Issue 2] - [Impact]

## MEDIUM PRIORITY (Fix This Month)
- [Issue 1] - [Impact]
- [Issue 2] - [Impact]

## STRENGTHS
- [Strength 1]
- [Strength 2]

## TECHNICAL DETAILS

### Core Web Vitals
| Metric | Value | Status |
|--------|-------|--------|
| LCP | [X.Xs] | [Good/Poor] |
| INP | [Xms] | [Good/Poor] |
| CLS | [0.XX] | [Good/Poor] |

### Schema Found
- [Type 1]
- [Type 2]

### On-Page Elements
- Title: [XX chars] - [Optimal/Short/Long]
- Meta: [XX chars] - [Optimal/Short/Long]
- Headings: H1(1), H2(X), H3(X)
- Images: Total(X), With Alt(X), Without Alt(X)

## SITE CRAWL SUMMARY (with --crawl)

- Pages Crawled: [XXX]
- Issues Found: [XX]
- Critical: [X]
- Warnings: [X]
- Notices: [X]

### Top Issues
1. [Issue] - [X] pages
2. [Issue] - [X] pages

### Site Architecture
- Depth: Max [X] clicks from home
- Orphan Pages: [X]
- Links per Page: Avg [X]

## ACTION PLAN

### Immediate (Today):
1. [Critical fix]

### This Week:
1. [High priority fix]
2. [High priority fix]

### This Month:
1. [Medium priority fix]
2. [Medium priority fix]

## NEXT STEPS
/seo keywords [related-keyword]
/seo schema [type]
/seo audit [competitor-url] --compare
```

## Examples

**Basic audit:**
```
/seo audit https://example.com/pricing
```

**Full audit with screenshots:**
```
/seo audit https://example.com/blog/post-1 --full
```

**Competitor comparison:**
```
/seo audit https://example.com/pricing --compare=https://competitor1.com,https://competitor2.com
```

**Historical trend:**
```
/seo audit https://example.com --trend --days=90
```

**Site-wide crawl:**
```
/seo audit https://example.com --crawl --limit=100
```

**Mobile-specific audit:**
```
/seo audit https://example.com --mobile
```

**Technical SEO deep-dive:**
```
/seo audit https://example.com --technical
```

**Full analysis with all features:**
```
/seo audit https://example.com --full --compare=https://comp1.com --trend --days=30
```
