# React to Next.js Migration Guide

## Quick Reference

### Before You Start

```bash
# Check current structure
ls -la src/
cat package.json | grep -A5 '"dependencies"'
```

### Step-by-Step Migration

#### 1. Install Next.js Dependencies

```bash
npm install next react react-dom
npm install -D typescript @types/react @types/node
```

#### 2. Update package.json

```json
{
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start",
    "lint": "next lint"
  }
}
```

#### 3. Create Next.js Config

**next.config.js:**
```javascript
/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  images: {
    domains: ['example.com'],
  },
}

module.exports = nextConfig
```

**tsconfig.json:**
```json
{
  "compilerOptions": {
    "target": "es5",
    "lib": ["dom", "dom.iterable", "esnext"],
    "allowJs": true,
    "skipLibCheck": true,
    "strict": true,
    "forceConsistentCasingInFileNames": true,
    "noEmit": true,
    "esModuleInterop": true,
    "module": "esnext",
    "moduleResolution": "node",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "jsx": "preserve",
    "incremental": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./*"]
    }
  },
  "include": ["next-env.d.ts", "**/*.ts", "**/*.tsx"],
  "exclude": ["node_modules"]
}
```

#### 4. Directory Structure Migration

```
Before (React)              After (Next.js)
├── src/                    ├── pages/
│   ├── components/         │   ├── _app.tsx
│   ├── pages/              │   ├── _document.tsx
│   │   ├── index.jsx       │   ├── index.tsx
│   │   └── about.jsx       │   ├── about.tsx
│   ├── App.jsx             │   └── api/
│   └── index.jsx           │       └── hello.ts
├── public/                 ├── components/
└── package.json            ├── styles/
                            ├── public/
                            └── package.json
```

#### 5. Migrate _app.js → _app.tsx

```tsx
// pages/_app.tsx
import '@/styles/globals.css'
import type { AppProps } from 'next/app'
import { Inter } from 'next/font/google'

const inter = Inter({ subsets: ['latin'] })

export default function App({ Component, pageProps }: AppProps) {
  return <main className={inter.className}><Component {...pageProps} /></main>
}
```

#### 6. Migrate index.js → index.tsx

```tsx
// pages/index.tsx
import Head from 'next/head'
import Image from 'next/image'
import Link from 'next/link'

export default function HomePage() {
  return (
    <>
      <Head>
        <title>Home - My Site</title>
        <meta name="description" content="Welcome to my site" />
      </Head>
      
      <main>
        <h1>Welcome to Next.js</h1>
        <Link href="/about">Go to About</Link>
        <Image src="/hero.jpg" width={1200} height={600} alt="Hero" />
      </main>
    </>
  )
}
```

#### 7. Migrate React Router to File-Based

**Before (react-router-dom):**
```jsx
// App.jsx
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import About from './pages/About'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/about" element={<About />} />
      </Routes>
    </BrowserRouter>
  )
}
```

**After (Next.js):**
```
pages/
├── index.tsx      # / route
├── about.tsx      # /about route
└── contact.tsx    # /contact route
```

#### 8. Dynamic Routes

```
React Router:        /users/:id/posts/:slug
Next.js Pages:       pages/users/[id]/posts/[slug].tsx
```

**pages/users/[id]/posts/[slug].tsx:**
```tsx
import { useRouter } from 'next/router'

export default function PostPage() {
  const router = useRouter()
  const { id, slug } = router.query
  
  return <h1>Post {slug} by User {id}</h1>
}
```

#### 9. Data Fetching Migration

**Before (useEffect):**
```tsx
useEffect(() => {
  fetch('/api/data')
    .then(res => res.json())
    .then(data => setData(data))
}, [])
```

**After (getServerSideProps):**
```tsx
export const getServerSideProps = async () => {
  const res = await fetch('https://api.example.com/data')
  const data = await res.json()
  
  return { props: { data } }
}

export default function Page({ data }) {
  return <div>{data.title}</div>
}
```

#### 10. Remove React Router

```bash
npm uninstall react-router-dom
```

---

## Common Issues

### 1. "useRouter" not working

Make sure you're using `useRouter` from `next/router`, not `react-router-dom`.

### 2. Image not loading

Use `next/image` with proper width/height:
```tsx
<Image src="/image.jpg" width={800} height={600} alt="Desc" />
```

### 3. Global styles not applying

Add to `_app.tsx`:
```tsx
import '@/styles/globals.css'
```

### 4. API routes not working

Create in `pages/api/`:
```tsx
// pages/api/users.ts
export default function handler(req, res) {
  res.status(200).json({ users: [] })
}
```

### 5. TypeScript errors

Install types:
```bash
npm install -D @types/react @types/node
```

---

## Rollback Plan

If migration fails, you can:

1. Keep the old `src/pages/` as backup
2. Use `next.js` folder for new code
3. Migrate one page at a time
4. Test after each migration

## Testing Checklist

- [ ] All pages render correctly
- [ ] Links work between pages
- [ ] Dynamic routes function
- [ ] API routes respond
- [ ] Images load with next/image
- [ ] Build succeeds (`npm run build`)
- [ ] Production build works (`npm start`)
