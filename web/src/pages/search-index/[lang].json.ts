import type { APIRoute, GetStaticPaths } from 'astro';
import { docsFor, entryUrl } from '../../lib/docs';
import { locales, t, type Locale } from '../../i18n/ui';

export const getStaticPaths: GetStaticPaths = () => locales.map((lang) => ({ params: { lang } }));

/** Plain-text approximation of an MDX body, good enough for substring search. */
function plain(body: string): string {
  return body
    .replace(/^import .*$/gm, '')
    .replace(/```[\s\S]*?```/g, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/[#>*_`|\[\]()-]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

export const GET: APIRoute = async ({ params }) => {
  const locale = params.lang as Locale;
  const tr = t(locale);
  const docs = await docsFor(locale);
  const index = docs.map((entry) => ({
    title: entry.data.title,
    url: entryUrl(entry),
    section: tr(`section.${entry.data.section}`),
    content: `${entry.data.description} ${plain(entry.body ?? '')}`,
  }));
  return new Response(JSON.stringify(index), { headers: { 'Content-Type': 'application/json' } });
};
