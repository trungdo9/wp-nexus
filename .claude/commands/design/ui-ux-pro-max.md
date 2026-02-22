---
description: Advanced UI/UX design with comprehensive style toolkit - delegates to ui-ux-designer agent
argument-hint: [product-type] [style] [industry]
---

# UI/UX Pro Max - Design Intelligence

**This command delegates to the `ui-ux-designer` agent which has full Style Intelligence capabilities.**

## Usage

```bash
/design:ui-ux-pro-max [product-type] [style] [industry]
```

**Examples:**
```bash
/design:ui-ux-pro-max SaaS minimal fintech
/design:ui-ux-pro-max landing page elegant beauty
/design:ui-ux-pro-max dashboard brutalism gaming
```

## What Happens

1. **Analyzes** user requirements (product type, style, industry)
2. **Delegates** to `ui-ux-designer` agent
3. **Agent applies** Style Intelligence:
   - Selects appropriate style (50+ options)
   - Chooses color palette (21 options)
   - Picks font pairing (50 options)
   - Follows domain-specific patterns
4. **Agent validates** using Pre-Delivery Checklist
5. **Outputs** comprehensive UI implementation

## Style Toolkit Reference

| Category | Options |
|----------|---------|
| Styles | 50+ (Minimalism, Brutalism, Glassmorphism, Neumorphism, Dark Mode, etc.) |
| Palettes | 21 (SaaS Blue, Healthcare, Beauty/Spa, Fintech, etc.) |
| Fonts | 50 pairings (Elegant/Luxury, Modern/Tech, Professional, etc.) |

## For More Details

See `.claude/agents/ui-ux-designer.md` for complete Style Intelligence documentation including:
- Step-by-step style selection process
- Domain-specific design patterns
- UX best practices
- Pre-delivery checklist

## Stack Support

Default: `html-tailwind`
Supported: react, nextjs, vue, svelte, swiftui, react-native, flutter, shadcn

**Note**: For simple quick designs, use `/design:fast`. For award-winning quality designs, use `/design:good`.
