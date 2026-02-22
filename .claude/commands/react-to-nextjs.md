---
name: react-to-nextjs
description: Convert React applications to Next.js with Pages Router, optimized for SEO and performance. Includes automated testing cross-check.
mode: default
---

# React to Next.js (Pages Router) Conversion

Convert a React application to Next.js with Pages Router, with full SEO and performance optimization.

## Usage

```
/react-to-nextjs <source-directory> [options]
```

## Arguments

| Argument | Description |
|----------|-------------|
| `source-directory` | Path to React project (default: current directory) |

## Options

| Flag | Description |
|------|-------------|
| `--typescript` | Convert to TypeScript |
| `--tailwind` | Include Tailwind CSS setup |
| `--pages` | Use Pages Router (default) |
| `--no-skip-tests` | Run tests after conversion |
| `--skip-tests` | Skip test execution (default) |
| `--help` | Show this help |

## Examples

```
# Basic conversion (current directory)
/react-to-nextjs

# Convert React project to Next.js
/react-to-nextjs ./my-react-app

# Convert with TypeScript
/react-to-nextjs ./my-react-app --typescript

# Full conversion with Tailwind and tests
/react-to-nextjs ./my-react-app --typescript --tailwind --no-skip-tests
```

## What Gets Converted

### Directory Structure
```
React                          Next.js (Pages Router)
├── src/components/      →     ├── components/
├── src/pages/           →     ├── pages/
├── src/App.js           →     ├── pages/_app.tsx
├── src/index.js         →     ├── pages/index.tsx
├── src/styles/          →     ├── styles/
└── public/              →     ├── public/
```

### Key Transformations

| React | Next.js |
|-------|---------|
| react-router-dom | File-based routing (`pages/`) |
| `<img src="...">` | `<Image src="..." width={...} height={...} />` |
| Google Fonts link | `next/font/google` |
| `useEffect` fetch | `getServerSideProps` / `getStaticProps` |
| Component imports | Automatic code splitting |
| CRA/Vite config | `next.config.js` |

### SEO Features Added

- ✅ Metadata API with title, description, keywords
- ✅ OpenGraph tags for social sharing
- ✅ Twitter Card meta tags
- ✅ Semantic HTML structure
- ✅ `sitemap.xml` generation
- ✅ `robots.txt` configuration

### Performance Features Added

- ✅ `next/image` for all images
- ✅ `next/font` for font optimization
- ✅ Dynamic imports for heavy components
- ✅ SSR/SSG/ISR data fetching
- ✅ Automatic code splitting

## Output

After conversion, you'll get:

1. **Converted Next.js project structure**
2. **Key changes explained** (routing, data fetching, SEO, performance)
3. **SEO Checklist** - all items verified ✅
4. **Performance Checklist** - all items verified ✅
5. **Test Results** (if `--no-skip-tests`)

### Sample Output

```markdown
## Converted Next.js Code

### File Structure
nextjs-project/
├── pages/
│   ├── _app.tsx
│   ├── _document.tsx
│   ├── index.tsx
│   ├── about.tsx
│   ├── api/
│   │   └── users.ts
│   └── posts/
│       ├── index.tsx
│       └── [slug].tsx
├── components/
├── styles/
└── public/

## Key Changes Explained

1. **Routing**: react-router-dom → file-based routing
2. **Data Fetching**: useEffect → getServerSideProps
3. **SEO**: Added Metadata API, OpenGraph, sitemap
4. **Performance**: next/image, next/font, dynamic imports

## SEO Checklist ✅
- [x] Metadata API implemented
- [x] OpenGraph tags added
- [x] Semantic HTML structure
- [x] sitemap.xml generated
- [x] robots.txt configured

## Performance Checklist ✅
- [x] next/image for images
- [x] next/font for fonts
- [x] Dynamic imports
- [x] SSR/SSG/ISR applied
```

## After Conversion

### 1. Install Dependencies
```bash
cd nextjs-project
npm install
```

### 2. Run Development Server
```bash
npm run dev
```

### 3. Run Tests (if skipped during conversion)
```bash
npm test
```

### 4. Build for Production
```bash
npm run build
npm start
```

## Notes

- Uses **Pages Router** (`pages/`) not App Router
- Automatically removes react-router-dom dependency
- Adds TypeScript types if `--typescript` flag used
- Preserves component logic while optimizing for Next.js
- Spawns `tester` agent for cross-check if tests are enabled

## Requirements

- Node.js 18+ recommended
- React project with standard structure
- For TypeScript: React project with TypeScript or will be converted

## See Also

- `/react-to-nextjs:help` - Show this help
- `/react-to-nextjs:docs` - Open documentation
