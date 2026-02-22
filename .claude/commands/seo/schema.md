---
description: Generate JSON-LD schema markup
argument-hint: <schema-type> [parameters] [--auto <url>] [--validate] [--nested]
---

# Schema Generation: $ARGUMENTS

Use the seo-specialist agent to generate JSON-LD schema markup.

## Workflow

1. **Parse the request** - identify schema type and parameters
2. **Gather information** - extract details from context or prompt
3. **Generate schema** - create proper JSON-LD structure
4. **Provide implementation** - code + where to add it

## Enhanced Options

| Flag | Description |
|------|-------------|
| `--auto <url>` | Auto-generate schema from URL content |
| `--validate` | Validate existing schema on a URL |
| `--nested` | Create nested schemas (Organization + LocalBusiness) |
| `--extract` | Extract and enhance existing schema |

## Auto-Generate from URL (--auto)

Extract schema directly from an existing page:

```bash
/seo schema auto https://example.com/blog/post-title
/seo schema auto https://example.com/product-page --type=product
```

**What it extracts:**
- Title, description, images
- Publication dates
- Author information
- Pricing/offers
- FAQ content
- Ratings/reviews

## Validate Schema (--validate)

Check schema validity and rich result eligibility:

```bash
/seo schema validate --url=https://example.com
/seo schema validate --url=https://example.com --type=article
```

**Validation checks:**
- Syntax validity (JSON-LD)
- Required properties per type
- Property value formats
- Rich Results Test eligibility
- Schema.org compliance

## Nested Schema Builder (--nested)

Create connected schemas with proper @graph nesting:

```bash
/seo schema nested organization localbusiness
/seo schema nested organization product --expand
```

**Supported nestings:**
- Organization + LocalBusiness + OpeningHoursSpecification
- Product + Offer + AggregateRating
- Article + Author + Organization + Publisher
- Event + Offer + Location + Organization

## Supported Schema Types

| Type | Use Case | Example |
|------|----------|---------|
| **Product** | E-commerce products | Pricing, reviews |
| **Pricing** | Pricing plans | SaaS pricing, subscriptions |
| **Article** | Blog posts, news | Articles, guides |
| **FAQ** | FAQ sections | Q&A content |
| **HowTo** | Tutorial content | Step-by-step guides |
| **LocalBusiness** | Local businesses | Store, restaurant |
| **Organization** | Company info | Brand, contact |
| **Event** | Events | Webinars, conferences |
| **Course** | Educational content | Training, courses |
| **Review** | Product reviews | Ratings, testimonials |
| **Person** | Author/creator | Bio, credentials |
| **WebSite** | Website | Search functionality |
| **WebPage** | Generic pages | All page types |
| **VideoObject** | Video content | YouTube, embedded |
| **BreadcrumbList** | Breadcrumbs | Navigation paths |

## Schema Templates

### Product Schema
```json
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "Product Name",
  "description": "Product description",
  "image": "https://example.com/image.jpg",
  "brand": {
    "@type": "Brand",
    "name": "Brand Name"
  },
  "offers": {
    "@type": "Offer",
    "price": "99.00",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.5",
    "reviewCount": "100"
  }
}
```

### Nested Organization + LocalBusiness
```json
{
  "@context": "https://schema.org/",
  "@graph": [
    {
      "@type": "Organization",
      "name": "Company Name",
      "url": "https://example.com",
      "logo": "https://example.com/logo.png"
    },
    {
      "@type": "LocalBusiness",
      "parentOrganization": {
        "name": "Company Name"
      },
      "name": "Store Location",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "123 Main St",
        "addressLocality": "City",
        "addressCountry": "US"
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
        "opens": "09:00",
        "closes": "17:00"
      }
    }
  ]
}
```

### FAQ Schema
```json
{
  "@context": "https://schema.org/",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Question 1?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Answer 1..."
      }
    }
  ]
}
```

### Article Schema
```json
{
  "@context": "https://schema.org/",
  "@type": "Article",
  "headline": "Article Title",
  "image": "https://example.com/image.jpg",
  "datePublished": "2025-01-01",
  "dateModified": "2025-01-15",
  "author": {
    "@type": "Person",
    "name": "Author Name"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Publisher Name"
  },
  "description": "Article description..."
}
```

### HowTo Schema
```json
{
  "@context": "https://schema.org/",
  "@type": "HowTo",
  "name": "How to Do Something",
  "step": [
    {
      "@type": "HowToStep",
      "name": "Step 1",
      "text": "Do this first...",
      "url": "https://example.com/page#step1"
    }
  ]
}
```

## Implementation Guide

### Where to Add
Add the JSON-LD script tag to your HTML `<head>` or before `</body>`:

```html
<script type="application/ld+json">
{...schema content...}
</script>
```

### Validation Tools
- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Schema.org Validator](https://validator.schema.org/)
- [Bing Webmaster Tools](https://bing.com/webmasters)

### Benefits
- Rich snippets in search results
- Enhanced click-through rate (+15-30%)
- Better AI citation potential
- Improved structured data visibility

## Examples

**Generate Product schema:**
```
/seo schema product "My SaaS Product"
```

**Generate Pricing schema:**
```
/seo schema pricing "SaaS Platform"
```

**Generate FAQ schema:**
```
/seo schema faq
```

**Generate Article schema:**
```
/seo schema article "Blog Post Title" --image https://example.com/img.jpg --author "John"
```

**Generate HowTo schema:**
```
/seo schema howto "How to Install Software"
```

**Auto-generate from URL:**
```
/seo schema auto https://example.com/blog/post-title
/seo schema auto https://example.com/product --type=product
```

**Validate existing schema:**
```
/seo schema validate --url=https://example.com
/seo schema validate --url=https://example.com --type=article
```

**Create nested Organization + LocalBusiness:**
```
/seo schema nested organization localbusiness --city="Hanoi" --country="VN"
```

**Extract and enhance existing schema:**
```
/seo schema auto https://example.com --extract --enhance
```

## Output Format

```
# Schema: [Type]
Generated: [Date]

## Auto-Extracted Content (with --auto)
- Title: [extracted title]
- Description: [extracted description]
- Images: [list]
- Author: [extracted author]
- Date: [extracted date]

## JSON-LD Code
```json
{...}
```

## Validation Results (with --validate)
- Syntax: Valid
- Required Properties: Present
- Rich Results: Eligible
- Warnings: [if any]

## Nested Schemas (with --nested)
- [Schema 1] -> [Schema 2] (relationship)

## Implementation

Add this script tag to your HTML:
```html
<script type="application/ld+json">
{...}
</script>
```

## Benefits
- Rich snippets in search results
- Enhanced CTR (+15-30%)
- Better AI citation potential
- Improved structured data visibility

## Validation
Test at: https://search.google.com/test/rich-results

## Next Steps
/seo audit [url]  # Verify schema is present
/content [type] [title]  # Create content with this schema
