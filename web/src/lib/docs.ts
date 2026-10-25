import { getCollection, type CollectionEntry } from 'astro:content';
import { localePath, type Locale } from '../i18n/ui';

export const sectionOrder = ['start', 'guide', 'genetics', 'admin', 'api'] as const;

export type DocEntry = CollectionEntry<'docs'>;

/** Entry ids look like `en/guide/search-cats`; the first segment is the locale. The glob loader drops a trailing `index`, so a locale's docs home has the bare id `en`. */
export function entryLocale(entry: DocEntry): Locale {
  return entry.id === 'sk' || entry.id.startsWith('sk/') ? 'sk' : 'en';
}

/** Path below /docs, without locale or trailing `index` (`''` for the docs home). */
export function entrySlug(entry: DocEntry): string {
  return entry.id.replace(/^(en|sk)(\/|$)/, '').replace(/(^|\/)index$/, '');
}

export function entryUrl(entry: DocEntry): string {
  const slug = entrySlug(entry);
  return localePath(entryLocale(entry), slug ? `/docs/${slug}/` : '/docs/');
}

/** Docs of one locale in reading order: by section, then `order`, then title. */
export async function docsFor(locale: Locale): Promise<DocEntry[]> {
  const all = await getCollection('docs');
  return all
    .filter((e) => entryLocale(e) === locale)
    .sort(
      (a, b) =>
        sectionOrder.indexOf(a.data.section) - sectionOrder.indexOf(b.data.section) ||
        a.data.order - b.data.order ||
        a.data.title.localeCompare(b.data.title, locale),
    );
}

export async function docsStaticPaths(locale: Locale) {
  return (await docsFor(locale)).map((entry) => ({
    params: { slug: entrySlug(entry) || undefined },
    props: { entry },
  }));
}
