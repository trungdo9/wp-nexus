# React to Next.js (Pages Router) Conversion Guide

Guide for converting React applications to Next.js with Pages Router, optimized for SEO and performance.

## When to Use

Use this skill when:
- Migrating from Create React App (CRA) or Vite to Next.js
- Converting React components to Next.js pages
- Optimizing existing Next.js apps for SEO and performance
- Setting up Next.js with Pages Router structure

---

## Conversion Workflow

### Phase 1: Analyze Source Project
- Identify React components and their structure
- Map react-router-dom routes to pages/ directory
- Find data fetching patterns (useEffect, SWR, React Query)
- Catalog CSS/styling approach

### Phase 2: Convert Pages Directory
- Create pages/ structure
- Convert routes (e.g., `/about` → `pages/about.js`)
- Handle dynamic routes (`[id].js` → `pages/posts/[id].js`)
- Migrate `_app.js` and `_document.js`

### Phase 3: Data Fetching Migration
| React Pattern | Next.js Equivalent |
|---------------|-------------------|
| `useEffect` + fetch | `getServerSideProps` |
| Static data | `getStaticProps` |
| Incremental updates | `getStaticProps` + `revalidate` |
| API calls | `pages/api/` routes |

### Phase 4: Component Conversion
- Class components → Function components with hooks
- Client components → Server Components where possible
- Remove unnecessary client-side JavaScript
- Add proper TypeScript types

### Phase 5: Styling Migration
| React | Next.js |
|-------|---------|
| CSS modules | CSS modules |
| Styled-components | styled-jsx (native) |
| Tailwind | Tailwind (native support) |
| CSS-in-JS | CSS modules / Tailwind |

---

## SEO Optimization

### Metadata API (Next.js 13+)

```typescript
// pages/index.tsx
import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: {
    default: 'Page Title',
    template: '%s | Site Name'
  },
  description: 'Page description for SEO',
  keywords: ['keyword1', 'keyword2', 'keyword3'],
  openGraph: {
    title: 'OG Title',
    description: 'OG Description',
    url: 'https://example.com',
    siteName: 'Site Name',
    images: [
      {
        url: '/og-image.jpg',
        width: 1200,
        height: 630,
      },
    ],
    locale: 'en_US',
    type: 'website',
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Twitter Title',
    description: 'Twitter Description',
    images: ['/twitter-image.jpg'],
  },
  robots: {
    index: true,
    follow: true,
  },
}
```

### Semantic HTML Structure

```tsx
// pages/index.tsx
export default function HomePage() {
  return (
    <>
      <header>
        <nav>{/* navigation */}</nav>
      </header>
      
      <main>
        <article>
          <h1>Main Heading (H1)</h1>
          <section>
            <h2>Section Heading (H2)</h2>
            <p>Content...</p>
          </section>
        </article>
      </main>
      
      <footer>
        <address>Contact info</address>
      </footer>
    </>
  )
}
```

### Sitemap Generation

```typescript
// pages/sitemap.xml.ts
import { GetServerSideProps } from 'next'

export default function Sitemap() {
  return null
}

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
  const baseUrl = 'https://example.com'
  
  // Fetch your dynamic routes
  const posts = await fetchPosts()
  
  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
      <url>
        <loc>${baseUrl}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
      </url>
      ${posts.map((post: any) => `
        <url>
          <loc>${baseUrl}/posts/${post.slug}</loc>
          <lastmod>${post.updatedAt}</lastmod>
          <changefreq>weekly</changefreq>
          <priority>0.8</priority>
        </url>
      `).join('')}
    </urlset>`
  
  res.setHeader('Content-Type', 'text/xml')
  res.write(sitemap)
  res.end()
  
  return { props: {} }
}
```

### Robots.txt

```typescript
// pages/robots.ts
import { GetServerSideProps } from 'next'

export default function Robots() {
  return null
}

export const getServerSideProps: GetServerSideProps = async ({ res }) => {
  const robotsTxt = `
    User-agent: *
    Allow: /
    
    Sitemap: https://example.com/sitemap.xml
  `
  
  res.setHeader('Content-Type', 'text/plain')
  res.write(robotsTxt)
  res.end()
  
  return { props: {} }
}
```

---

## Performance Optimization

### Image Optimization (next/image)

```tsx
import Image from 'next/image'

// Before (React)
<img src="/image.jpg" alt="Description" />

// After (Next.js)
<Image
  src="/image.jpg"
  alt="Description"
  width={800}
  height={600}
  priority={true}  // For above-the-fold images
  placeholder="blur"
  blurDataURL="data:image/..."
/>

// Responsive images
<Image
  src="/image.jpg"
  alt="Description"
  fill
  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
/>
```

### Font Optimization (next/font)

```tsx
import { Inter, Playfair_Display } from 'next/font/google'

// Google Fonts with automatic optimization
const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-inter',
})

const playfair = Playfair_Display({
  subsets: ['latin'],
  display: 'swap',
})

export default function Layout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${inter.variable} ${playfair.variable}`}>
      <body>{children}</body>
    </html>
  )
}
```

### Dynamic Imports

```tsx
import dynamic from 'next/dynamic'

// Heavy component loaded on demand
const HeavyChart = dynamic(
  () => import('../components/HeavyChart'),
  {
    loading: () => <p>Loading...</p>,
    ssr: false,  // Disable SSR for client-only components
  }
)

// Usage
<HeavyChart data={data} />
```

### Data Fetching with Caching

```typescript
// pages/posts/[id].tsx
import { GetServerSideProps } from 'next'

export const getServerSideProps: GetServerSideProps = async ({ params }) => {
  const post = await fetchPost(params.id)
  
  if (!post) {
    return { notFound: true }
  }
  
  return {
    props: {
      post,
    },
  }
}
```

### ISR (Incremental Static Regeneration)

```typescript
// pages/posts/index.tsx
export const getStaticProps: GetStaticProps = async () => {
  const posts = await fetchAllPosts()
  
  return {
    props: {
      posts,
    },
    revalidate: 60,  // Re-generate every 60 seconds
  }
}
```

---

## Pages Router Structure

```
src/
├── pages/
│   ├── _app.tsx           # Custom App
│   ├── _document.tsx      # Custom Document
│   ├── index.tsx          # Home page
│   ├── about.tsx          # Static page
│   ├── api/
│   │   └── users.ts       # API route
│   └── posts/
│       ├── index.tsx      # /posts
│       └── [slug].tsx     # /posts/:slug
├── components/
│   ├── Header.tsx
│   ├── Footer.tsx
│   └── PostCard.tsx
├── styles/
│   └── globals.css
└── public/
    └── images/
```

---

## Common Conversion Patterns

### React Router → Next.js Routing

```tsx
// Before (React with react-router-dom)
import { Link, Routes, Route } from 'react-router-dom'

<Link to="/about">About</Link>
<Routes>
  <Route path="/about" element={<About />} />
</Routes>

// After (Next.js)
import Link from 'next/link'
import { useRouter } from 'next/router'

<Link href="/about">About</Link>
const router = useRouter()
router.push('/about')
```

### Class Component → Function Component

```tsx
// Before (Class Component)
class UserProfile extends React.Component {
  state = { user: null }
  
  async componentDidMount() {
    const user = await fetchUser(this.props.id)
    this.setState({ user })
  }
  
  render() {
    const { user } = this.state
    return <div>{user?.name}</div>
  }
}

// After (Function Component with TypeScript)
import { useState, useEffect } from 'react'
import { useRouter } from 'next/router'

interface User {
  id: string
  name: string
}

export default function UserProfile() {
  const router = useRouter()
  const { id } = router.query
  const [user, setUser] = useState<User | null>(null)
  
  useEffect(() => {
    if (id) {
      fetchUser(id).then(setUser)
    }
  }, [id])
  
  return <div>{user?.name}</div>
}
```

### API Routes (pages/api/)

```typescript
// pages/api/users.ts
import type { NextApiRequest, NextApiResponse } from 'next'

type User = {
  id: number
  name: string
  email: string
}

export default function handler(
  req: NextApiRequest,
  res: NextApiResponse<User[]>
) {
  const users: User[] = [
    { id: 1, name: 'John', email: 'john@example.com' },
  ]
  
  res.status(200).json(users)
}
```

---

## SEO Checklist

- [ ] Metadata API implemented with title, description
- [ ] OpenGraph tags for social sharing
- [ ] Twitter Card metadata
- [ ] Semantic HTML structure (header, main, section, article)
- [ ] Proper heading hierarchy (H1 → H2 → H3...)
- [ ] Alt text for all images
- [ ] sitemap.xml generated
- [ ] robots.txt configured
- [ ] Structured data (JSON-LD) where applicable

## Performance Checklist

- [ ] `next/image` used instead of `<img>`
- [ ] `next/font` for font optimization
- [ ] Dynamic imports for heavy components
- [ ] SSR/SSG/ISR appropriately applied
- [ ] Code splitting (automatic in Next.js)
- [ ] API caching and revalidation configured
- [ ] Unnecessary client-side JavaScript removed
- [ ] Responsive images with sizes prop

## References

- [Next.js Pages Router](https://nextjs.org/docs/pages)
- [Next.js Image Component](https://nextjs.org/docs/pages/api/components/image)
- [Next.js Font](https://nextjs.org/docs/pages/api/components/font)
- [Next.js Data Fetching](https://nextjs.org/docs/pages/building-your-application/data-fetching)
- [Next.js SEO](https://nextjs.org/docs/pages/building-your-application/optimizing/static-assets)
