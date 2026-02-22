---
description: Research keywords for a topic
argument-hint: <keyword-or-topic> [--serp-features] [--gap --competitors=<url>] [--cluster] [--brief]
---

# Keyword Research: $ARGUMENTS

Use the seo-specialist agent to perform keyword research and analysis.

## Workflow

1. **Analyze the seed term** - understand intent and context
2. **Generate keyword variations** - expand the seed into related terms
3. **Research keywords** - estimate volume, difficulty, CPC
4. **Categorize results** - Primary, Secondary, Long-tail
5. **Create content strategy** - recommendations for content creation

## Enhanced Options

| Flag | Description |
|------|-------------|
| `--serp-features` | Analyze SERP features for top keywords |
| `--gap` | Perform content gap analysis against competitors |
| `--competitors=<url>` | Comma-separated list of competitor URLs |
| `--cluster` | Group keywords into semantic clusters |
| `--brief` | Generate content brief in markdown format |

## Research Process

### 1. Seed Analysis
- Identify primary keyword: $ARGUMENTS
- Determine search intent (Informational, Commercial, Transactional)
- Estimate market size

### 2. Keyword Expansion
Generate variations across:
- Different match types (broad, phrase, exact)
- Long-tail variations
- Question-based queries
- Comparison keywords
- Competitor-related terms

### 3. Keyword Data (Estimated)
For each keyword, estimate:
- **Search Volume**: Monthly searches (estimated)
- **Difficulty**: Competition level (0-100)
- **CPC**: Cost per click (USD, estimated)
- **Intent**: Informational/Commercial/Transactional
- **Opportunity**: Very High/High/Medium/Low
- **SERP Features**: Presence of featured snippets, PAA, etc.

### 4. SERP Features Analysis (with --serp-features)

| SERP Feature | Description | Content Strategy |
|--------------|-------------|------------------|
| Featured Snippet | Box at top answering question | Structure content as Q&A, lists |
| People Also Ask | Expandable Q&A list | Target question-based keywords |
| Knowledge Panel | Brand/info box | Build entity authority |
| Local Pack | Map with 3 local results | Local SEO optimization |
| Image Pack | Image carousel | Include optimized images |
| Video Results | YouTube/videos | Create video content |
| Shopping Results | Product listings | E-commerce optimization |
| Reviews | Star ratings | Add review schema |
| Top Stories | News articles | News content strategy |

### 5. Content Gap Analysis (with --gap --competitors)

Compare your content against competitors:
- Keywords they rank for that you don't
- Content topics they cover that you miss
- Backlink opportunities
- Content format gaps

### 6. Keyword Clustering (with --cluster)

Group related keywords into topic clusters:
- Semantic similarity analysis
- Intent alignment per cluster
- Content pillar recommendations
- Internal linking strategy

### 7. Content Brief Generation (with --brief)

Generate ready-to-write content briefs:
- Target keyword + LSI terms
- Optimal structure (H2, H3 outline)
- Word count recommendations
- Competitor content analysis
- Schema recommendations

## Output Format

```
# Keyword Research: [Seed Term]
Generated: [Date]

## PRIMARY KEYWORDS (High Intent)

### 1. [Keyword]
- Volume: [X,XXX]/month
- Difficulty: [XX]/100
- CPC: $[X.XX]
- Intent: [Type]
- Opportunity: [Level]
- SERP Features: [Snippet|PAA|Local|Shopping|None]

### 2. [Keyword]
...

## SECONDARY KEYWORDS (Medium Intent)

### 3. [Keyword]
...

## LONG-TAIL KEYWORDS (Low Competition)

### 10. [Keyword]
...

## SERP FEATURES ANALYSIS (with --serp-features)

**Featured Snippet Opportunities:**
- [Keyword] - Create Q&A format
- [Keyword] - Use listicle structure

**People Also Ask Targets:**
- [Question keyword] - Answer directly

**Content Type Recommendations:**
- [Keyword] - Video content needed
- [Keyword] - Image-heavy article

## SEARCH INTENT ANALYSIS

**Informational** (Early Stage):
- Keywords for blog posts
- Educational content

**Commercial Investigation** (Mid Stage):
- Keywords for comparison pages
- Reviews and lists

**Transactional** (Late Stage):
- Keywords for pricing pages
- Direct conversion

## CONTENT GAP ANALYSIS (with --gap --competitors)

**You Rank, Competitors Don't:**
- [Keyword] - Strengthen position

**Competitors Rank, You Don't:**
- [Keyword] - [URL] - Create content
- [Keyword] - [URL] - Create content

**Opportunity Score:**
- [Keyword] - Very High (low competition, high intent)

## KEYWORD CLUSTERS (with --cluster)

### Cluster 1: [Topic Name]
- [Keyword 1]
- [Keyword 2]
- [Keyword 3]
- Recommended Pillar: [Pillar Page]

### Cluster 2: [Topic Name]
...

## CONTENT BRIEF (with --brief)

**Target Keyword:** [Keyword]
**Word Count:** [X,XXX] words
**Content Type:** [Guide|Tutorial|Comparison|Listicle]

### Recommended Structure:
1. H2: Introduction (150 words)
2. H2: [Section 1]
   - H3: [Subsection]
   - H3: [Subsection]
3. H2: [Section 2]
   ...

### Competitor Analysis:
- [URL 1]: [X] words, structure notes
- [URL 2]: [X] words, structure notes

### Schema to Add:
- Article
- FAQ (if Q&A format)

## CONTENT STRATEGY

### Immediate Wins (Create First):
1. [Content Title]
   - Target: [keyword]
   - Format: [type]
   - SERP Feature: [target]

2. [Content Title]
   - Target: [keyword]
   - Format: [type]

### Long-term Authority:
- [Content ideas]

## KEYWORD PRIORITIZATION

### Priority 1 (This Month):
1. [Keyword] - [Reason]
2. [Keyword] - [Reason]

### Priority 2 (Next Month):
1. [Keyword] - [Reason]
2. [Keyword] - [Reason]

## NEXT STEPS
/seo audit [url]
/seo schema [type]
/content [content-type] [title]
```

## Examples

**Basic keyword research:**
```
/seo keywords "project management software"
```

**With SERP features analysis:**
```
/seo keywords "project management software" --serp-features
```

**Content gap analysis:**
```
/seo keywords "saas pricing" --gap --competitors=https://competitor1.com,https://competitor2.com
```

**Keyword clustering:**
```
/seo keywords "marketing" --cluster --groups=10
```

**Generate content brief:**
```
/seo keywords "how to use ai for business" --brief
```

**Full analysis:**
```
/seo keywords "project management" --serp-features --gap --competitors=https://asana.com --cluster --brief
```
