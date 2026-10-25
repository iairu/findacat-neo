// @ts-check
import { defineConfig } from 'astro/config';
import mdx from '@astrojs/mdx';
import tailwindcss from '@tailwindcss/vite';

// https://astro.build/config
export default defineConfig({
  integrations: [mdx()],
  vite: {
    plugins: [tailwindcss()],
  },
  // Code blocks follow the site theme (see `.astro-code` in global.css).
  markdown: {
    shikiConfig: { themes: { light: 'github-light', dark: 'github-dark' }, defaultColor: false },
  },
  site: 'https://findacat.eu',
  // English lives at /, Slovak at /sk/.
  i18n: {
    locales: ['en', 'sk'],
    defaultLocale: 'en',
    routing: { prefixDefaultLocale: false },
  },
  // Keep HTML whitespace as authored so prose like `word\n<strong>…` keeps its space.
  compressHTML: false,
});
