# Performance Checklist for Next.js

## Image Optimization

### Use next/image

```tsx
import Image from 'next/image'

// ✅ Good - with dimensions and alt
<Image
  src="/hero.jpg"
  alt="Hero image"
  width={1200}
  height={600}
  priority={true}  // For above-the-fold
/>

// ✅ Responsive with sizes
<Image
  src="/product.jpg"
  alt="Product image"
  fill
  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
  style={{ objectFit: 'cover' }}
/>

// ❌ Bad - missing dimensions
<img src="/image.jpg" />
```

### Image Optimization Tips

| Technique | Benefit |
|-----------|---------|
| `priority={true}` | Preload LCP image |
| `placeholder="blur"` | Visual stability |
| `sizes` prop | Serve correct size |
| `format={['avif', 'webp']}` | Modern formats |
| `quality={75}` | Balance quality/size |

---

## Font Optimization

### Use next/font

```tsx
import { Inter, Playfair_Display } from 'next/font/google'

// Google Fonts - self-hosted automatically
const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-inter',
})

const playfair = Playfair_Display({
  subsets: ['latin'],
  display: 'swap',
})

export default function Layout({ children }) {
  return (
    <html lang="en" className={`${inter.variable} ${playfair.variable}`}>
      <body>{children}</body>
    </html>
  )
}
```

### Font Performance Benefits

- ✅ Zero layout shift (FOUT/FOIT prevented)
- ✅ Self-hosted (no external requests)
- ✅ Automatic font subsetting
- ✅ Preload optimization

---

## Dynamic Imports

### Code Splitting

```tsx
import dynamic from 'next/dynamic'

// ✅ Heavy component loaded on demand
const HeavyChart = dynamic(
  () => import('../components/HeavyChart'),
  {
    loading: () => <p>Loading...</p>,
    ssr: false,  // Client-only
  }
)

// ✅ Conditional loading
const isAdmin = user.role === 'admin'
const AdminPanel = dynamic(() => import('./AdminPanel'), {
  ssr: false,
})

export default function Dashboard() {
  return (
    <>
      <MainContent />
      {isAdmin && <AdminPanel />}
    </>
  )
}
```

### When to Use Dynamic Imports

| Component | Use Dynamic? |
|-----------|--------------|
| Charts/graphs | ✅ Yes |
| Rich text editors | ✅ Yes |
| Video players | ✅ Yes |
| Maps | ✅ Yes |
| Navigation | ❌ No |
| Header/Footer | ❌ No |

---

## Data Fetching Strategies

### Static Generation (getStaticProps)

```tsx
export const getStaticProps = async () => {
  const posts = await fetchPosts()
  
  return {
    props: { posts },
    revalidate: 60,  // ISR: Regenerate every 60 seconds
  }
}
```

### Server-Side Rendering (getServerSideProps)

```tsx
export const getServerSideProps = async ({ query }) => {
  const product = await fetchProduct(query.id)
  
  return {
    props: { product },
  }
}
```

### Incremental Static Regeneration (ISR)

```tsx
export const getStaticProps = async () => {
  const allPosts = await fetchAllPosts()
  
  return {
    props: { allPosts },
    revalidate: 300,  // 5 minutes
  }
}
```

### When to Use Which

| Strategy | Use Case |
|----------|----------|
| **Static** | Blog posts, about page, docs |
| **ISR** | Frequently updated content |
| **SSR** | Personalized content, auth-required |
| **CSR** | Client-only dashboards |

---

## Script Optimization

### next/script

```tsx
import Script from 'next/script'

// ✅ Load third-party scripts efficiently
<Script
  src="https://analytics.js"
  strategy="lazyOnload"  // After page loads
/>

// ✅ Execute inline scripts safely
<Script
  id="google-analytics"
  dangerouslySetInnerHTML={{
    __html: `window.dataLayer = ...`,
  }}
/>
```

### Script Loading Strategies

| Strategy | When to Use |
|----------|-------------|
| `beforeInteractive` | Critical scripts |
| `afterInteractive` (default) | Analytics |
| `lazyOnload` | Low-priority scripts |
| `worker` (experimental) |独立性脚本 |

---

## Bundle Optimization

### Analyze Bundle Size

```bash
# Install analyzer
npm install @next/bundle-analyzer

# next.config.js
const withBundleAnalyzer = require('@next/bundle-analyzer')({
  enabled: process.env.ANALYZE === 'true',
})

module.exports = withBundleAnalyzer({
  // your config
})
```

### Reduce Bundle Size

```tsx
// ✅ Good - named imports
import { Button, Card } from 'ui-lib'

// ❌ Bad - default import (includes all)
import UI from 'ui-lib'

// ✅ Good - dynamic imports for large libraries
const QRCode = dynamic(() => import('qrcode'), { ssr: false })
```

---

## Caching Strategies

### Static Assets

```javascript
// next.config.js
module.exports = {
  // Cache static assets
  headers: async () => [
    {
      source: '/images/:path*',
      headers: [
        { key: 'Cache-Control', value: 'public, max-age=31536000, immutable' },
      ],
    },
  ],
}
```

### API Routes with Caching

```typescript
// pages/api/products.ts
export default async function handler(req, res) {
  // Cache for 60 seconds
  res.setHeader('Cache-Control', 's-maxage=60, stale-while-revalidate')
  
  const products = await fetchProducts()
  res.status(200).json(products)
}
```

---

## Component Optimization

### React.memo

```tsx
import { memo } from 'react'

const ExpensiveComponent = memo(function ExpensiveComponent({ data }) {
  // Heavy computation
  return <div>{data.map(item => process(item))}</div>
})

// Prevents re-render when props haven't changed
```

### useMemo and useCallback

```tsx
import { useMemo, useCallback } from 'react'

export default function ProductPage({ products }) {
  // Memoize expensive calculation
  const totalValue = useMemo(
    () => products.reduce((sum, p) => sum + p.price * p.quantity, 0),
    [products]
  )
  
  // Memoize callback
  const handleSelect = useCallback((id: string) => {
    setSelectedId(id)
  }, [])
  
  return (
    <ProductList
      products={products}
      total={totalValue}
      onSelect={handleSelect}
    />
  )
}
```

---

## Server-Side Optimization

### Enable Compression

Next.js enables Gzip/Brotli compression by default in production.

### Edge Functions (Vercel)

```tsx
// pages/api/hello.ts
export default async function handler(req, res) {
  res.status(200).json({ message: 'Hello!' })
}
```

---

## Core Web Vitals Optimization

### LCP (Largest Contentful Paint)

```tsx
// ✅ Optimize hero image
<Image
  src="/hero.jpg"
  alt="Hero"
  width={1200}
  height={600}
  priority={true}  // Critical for LCP
/>
```

### FID (First Input Delay)

```tsx
// ✅ Reduce main thread work
const HeavyModal = dynamic(() => import('./HeavyModal'), {
  loading: () => <p>Loading...</p>,
})

// ❌ Don't load heavy on initial render
import HeavyModal from './HeavyModal'  // Bad!
```

### CLS (Cumulative Layout Shift)

```tsx
// ✅ Always set dimensions
<Image
  src="/image.jpg"
  alt="Desc"
  width={800}
  height={600}
/>

// ✅ Reserve space for dynamic content
<div style={{ minHeight: '200px' }}>
  {isLoading ? <Skeleton /> : <Content />}
</div>
```

---

## Performance Checklist Summary

### Images
- [ ] Use `next/image` for all images
- [ ] Add `priority` to above-the-fold images
- [ ] Specify `sizes` for responsive images
- [ ] Use modern formats (WebP/AVIF)

### Fonts
- [ ] Use `next/font/google`
- [ ] Specify `display: swap`
- [ ] Subset fonts (latin vs all)

### Code Splitting
- [ ] Dynamic imports for heavy components
- [ ] Named imports (not default)
- [ ] Analyze bundle with `@next/bundle-analyzer`

### Data Fetching
- [ ] Static generation when possible
- [ ] ISR for frequently updated content
- [ ] SSR for personalized data
- [ ] API caching headers

### Scripts
- [ ] Use `next/script` with strategy
- [ ] Defer non-critical scripts
- [ ] Lazy load third-party scripts

### React Performance
- [ ] `React.memo` for pure components
- [ ] `useMemo` for expensive calculations
- [ ] `useCallback` for callbacks

### Build
- [ ] Run production build (`npm run build`)
- [ ] Verify bundle size
- [ ] Test LCP, FID, CLS scores

---

## Performance Monitoring

### Lighthouse Score Targets

| Metric | Target |
|--------|--------|
| Performance | 90+ |
| SEO | 100 |
| Accessibility | 100 |
| Best Practices | 100 |

### Tools

```bash
# Lighthouse CI
npm install -D @lhci/cli

# Run audit
npx lighthouse http://localhost:3000 --output=json
```
