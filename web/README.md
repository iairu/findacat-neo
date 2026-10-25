# Find A Cat: web

Static Astro site for findacat.eu: the homepage, the documentation and (later) the portal that talks to the Laravel API in `../api`. English is served at `/`, Slovak at `/sk/`.

## Commands

```bash
npm install
npm run dev       # local dev server
npm run build     # static output in dist/
npm run preview   # preview the production build
```

## Structure

```text
src/
  components/   Home, DocsPage, SearchModal, Logo, Icon
  content/docs/ MDX docs, one folder per locale: en/<section>/<page>.mdx and sk/…
  i18n/ui.ts    UI strings and locale path helpers
  layouts/      BaseLayout (header, footer, theme)
  lib/docs.ts   Docs collection helpers
  pages/        /, /sk/, /docs/[...slug], /sk/docs/[...slug], /search-index/[lang].json
  styles/       global.css (design tokens, shell, prose)
```

## Conventions

- Every doc page exists in both `en/` and `sk/` with the same path, so the language switch never 404s.
- Docs front matter: `title`, `description`, `section` (`start | guide | genetics | admin | api`), `order`, optional `draft: true` for outlines.
- Colours come from the tokens at the top of `global.css` (warm cream and espresso, amber accent). Do not hard-code hex values elsewhere.
- Fonts are self-hosted through `@fontsource-variable/*`; the site loads nothing from third-party hosts.
- Search is a client-side filter over `/search-index/<locale>.json`, generated at build time from the docs.
