# SEO Checklist for Next.js

## Essential SEO Elements

### 1. Metadata API

Every page should have proper metadata:

```tsx
import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: 'Page Title | Site Name',
  description: 'Page description (150-160 characters)',
  keywords: ['keyword1', 'keyword2', 'keyword3'],
}
```

### 2. Title Tag

```tsx
// pages/index.tsx
import Head from 'next/head'

export default function HomePage() {
  return (
    <>
      <Head>
        <title>Home - My Amazing Site</title>
        <meta name="title" content="Home - My Amazing Site" />
      </Head>
      <main>...</main>
    </>
  )
}
```

### 3. Meta Description

```tsx
<meta name="description" content="Learn how to build amazing things with our comprehensive guide. Step-by-step instructions for beginners." />
```

### 4. Open Graph (Facebook, LinkedIn)

```tsx
<meta property="og:title" content="Page Title" />
<meta property="og:description" content="Page description" />
<meta property="og:type" content="website" />
<meta property="og:url" content="https://example.com/page" />
<meta property="og:image" content="https://example.com/og-image.jpg" />
<meta property="og:site_name" content="Site Name" />
<meta property="og:locale" content="en_US" />
```

### 5. Twitter Card

```tsx
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@username" />
<meta name="twitter:title" content="Page Title" />
<meta name="twitter:description" content="Page description" />
<meta name="twitter:image" content="https://example.com/twitter-image.jpg" />
```

### 6. Canonical URL

```tsx
<link rel="canonical" href="https://example.com/original-page" />
```

### 7. Favicon

```tsx
<link rel="icon" href="/favicon.ico" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
```

---

## Semantic HTML Structure

### Proper Heading Hierarchy

```tsx
export default function BlogPost() {
  return (
    <article>
      <h1>Main Title (H1 - Only one per page)</h1>
      
      <section>
        <h2>Section Heading (H2)</h2>
        <p>Content...</p>
        
        <section>
          <h3>Subsection (H3)</h3>
          <p>Content...</p>
        </section>
      </section>
      
      <aside>
        <h2>Related Content (H2)</h2>
      </aside>
    </article>
  )
}
```

### Semantic Elements

```tsx
<header>
  <nav>...</nav>
  <h1>Site Name</h1>
</header>

<main>
  <article>
    <header>
      <h1>Article Title</h1>
      <time dateTime="2024-01-15">January 15, 2024</time>
    </header>
    <section>
      <p>Content...</p>
    </section>
    <footer>
      <address>By <a href="mailto:author@example.com">Author</a></address>
    </footer>
  </article>
</main>

<aside>
  <h2>Sidebar</h2>
  <nav>...</nav>
</aside>

<footer>
  <address>Contact info</address>
  <nav>Footer links</nav>
</footer>
```

---

## Image SEO

### Alt Text (Required)

```tsx
<Image
  src="/product.jpg"
  alt="Blue cotton t-shirt size medium"
  width={800}
  height={600}
/>
```

### Lazy Loading (Default in next/image)

Next.js automatically lazy loads images below the fold.

### Image Sitemap

For large sites, consider creating an image sitemap.

---

## Sitemap

### pages/sitemap.xml.ts

```tsx
import { GetServerSideProps } from 'next'

export default function Sitemap() { return null }

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
  const baseUrl = 'https://example.com'
  
  // Static pages
  const staticPages = ['', '/about', '/contact', '/pricing']
  
  // Dynamic pages (fetch from DB/CMS)
  const posts = await fetchPosts()
  
  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
      ${staticPages.map(path => `
        <url>
          <loc>${baseUrl}${path}</loc>
          <changefreq>daily</changefreq>
          <priority>${path === '' ? '1.0' : '0.8'}</priority>
        </url>
      `).join('')}
      ${posts.map((post: any) => `
        <url>
          <loc>${baseUrl}/blog/${post.slug}</loc>
          <lastmod>${post.updatedAt}</lastmod>
          <changefreq>weekly</changefreq>
          <priority>0.7</priority>
          <image:image>
            <image:loc>${baseUrl}${post.image}</image:loc>
            <image:title>${post.title}</image:title>
          </image:image>
        </url>
      `).join('')}
    </urlset>`
  
  res.setHeader('Content-Type', 'text/xml')
  res.write(sitemap)
  res.end()
  return { props: {} }
}
```

---

## Robots.txt

### pages/robots.ts

```tsx
import { GetServerSideProps } from 'next'

export default function Robots() { return null }

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
  const robotsTxt = `
User-agent: *
Allow: /

User-agent: Googlebot
Allow: /

User-agent: Bingbot
Allow: /

Sitemap: https://example.com/sitemap.xml
Disallow: /private/
Disallow: /api/
  `
  
  res.setHeader('Content-Type', 'text/plain')
  res.write(robotsTxt)
  res.end()
  return { props: {} }
}
```

---

## Structured Data (JSON-LD)

### Article Schema

```tsx
import Head from 'next/head'

export default function BlogPost({ post }) {
  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    headline: post.title,
    description: post.description,
    image: post.image,
    author: {
      '@type': 'Person',
      name: post.author,
    },
    publisher: {
      '@type': 'Organization',
      name: 'Site Name',
      logo: {
        '@type': 'ImageObject',
        url: 'https://example.com/logo.png',
      },
    },
    datePublished: post.publishedAt,
    dateModified: post.updatedAt,
  }

  return (
    <>
      <Head>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
        />
      </Head>
      <main>...</main>
    </>
  )
}
```

### Organization Schema

```tsx
const orgSchema = {
  '@context': 'https://schema.org',
  '@type': 'Organization',
  name: 'Company Name',
  url: 'https://example.com',
  logo: 'https://example.com/logo.png',
  socialLinks: [
    'https://twitter.com/company',
    'https://facebook.com/company',
  ],
}
```

---

## Performance Impact on SEO

### Core Web Vitals

| Metric | What to Optimize |
|--------|-----------------|
| **LCP** (Largest Contentful Paint) | Optimize hero images with `priority` |
| **FID** (First Input Delay) | Reduce JavaScript, use dynamic imports |
| **CLS** (Cumulative Layout Shift) | Always specify image dimensions |

```tsx
// Optimize LCP - add priority to hero image
<Image
  src="/hero.jpg"
  alt="Hero image"
  width={1200}
  height={600}
  priority={true}  // Loads immediately
/>
```

---

## Checklist Summary

- [ ] Title tag (60-70 characters)
- [ ] Meta description (150-160 characters)
- [ ] Open Graph tags
- [ ] Twitter Card tags
- [ ] Canonical URL
- [ ] Semantic HTML (header, main, article, footer)
- [ ] Heading hierarchy (H1 → H2 → H3)
- [ ] Alt text for all images
- [ ] Sitemap.xml
- [ ] Robots.txt
- [ ] Structured data (JSON-LD)
- [ ] Fast loading (Core Web Vitals)
- [ ] Mobile-friendly (responsive design)
- [ ] HTTPS enabled
- [ ] 404 page configured
