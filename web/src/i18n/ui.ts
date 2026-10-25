export const locales = ['en', 'sk'] as const;
export type Locale = (typeof locales)[number];
export const defaultLocale: Locale = 'en';

export const ui = {
  en: {
    'site.name': 'Find A Cat',
    'site.tagline': 'Cat pedigrees and inbreeding checks for breeders',
    'nav.home': 'Home',
    'nav.docs': 'Docs',
    'nav.skip': 'Skip to content',
    'nav.primary': 'Primary',
    'search.button': 'Search',
    'search.placeholder': 'Search the docs…',
    'search.hint': 'Type to search, or jump to a section',
    'search.none': 'No results found',
    'search.one': 'result',
    'search.many': 'results',
    'theme.toggle': 'Toggle light and dark theme',
    'lang.switch': 'Slovenčina',
    'lang.switchLabel': 'Prepnúť na slovenčinu',
    'docs.onThisPage': 'On this page',
    'docs.sections': 'Documentation sections',
    'docs.draft': 'This page is an outline. The full text is still being written.',
    'docs.prev': 'Previous',
    'docs.next': 'Next',
    'section.start': 'Start here',
    'section.guide': 'User guide',
    'section.genetics': 'Breeding and genetics',
    'section.admin': 'Admin and self-hosting',
    'section.api': 'API reference',
    'footer.license': 'Based on Silsilah (MIT). Thesis and demo data © Ondrej Špánik.',
  },
  sk: {
    'site.name': 'Find A Cat',
    'site.tagline': 'Rodokmene mačiek a kontrola príbuzenskej plemenitby pre chovateľov',
    'nav.home': 'Domov',
    'nav.docs': 'Dokumentácia',
    'nav.skip': 'Preskočiť na obsah',
    'nav.primary': 'Hlavná navigácia',
    'search.button': 'Hľadať',
    'search.placeholder': 'Hľadať v dokumentácii…',
    'search.hint': 'Začnite písať alebo prejdite do sekcie',
    'search.none': 'Nič sa nenašlo',
    'search.one': 'výsledok',
    'search.many': 'výsledkov',
    'theme.toggle': 'Prepnúť svetlú a tmavú tému',
    'lang.switch': 'English',
    'lang.switchLabel': 'Switch to English',
    'docs.onThisPage': 'Na tejto stránke',
    'docs.sections': 'Sekcie dokumentácie',
    'docs.draft': 'Táto stránka je len osnova. Úplný text sa ešte píše.',
    'docs.prev': 'Predchádzajúca',
    'docs.next': 'Ďalšia',
    'section.start': 'Začnite tu',
    'section.guide': 'Používateľská príručka',
    'section.genetics': 'Chov a genetika',
    'section.admin': 'Správa a vlastný hosting',
    'section.api': 'Referencia API',
    'footer.license': 'Založené na Silsilah (MIT). Práca a demo dáta © Ondrej Špánik.',
  },
} as const satisfies Record<Locale, Record<string, string>>;

export type UiKey = keyof (typeof ui)['en'];

export function t(locale: Locale) {
  return (key: UiKey): string => ui[locale][key] ?? ui[defaultLocale][key];
}

export function getLocale(pathname: string): Locale {
  return pathname === '/sk' || pathname.startsWith('/sk/') ? 'sk' : 'en';
}

/** Prefix an absolute site path with the locale (English has no prefix). */
export function localePath(locale: Locale, path: string): string {
  const clean = path.startsWith('/') ? path : `/${path}`;
  if (locale === defaultLocale) return clean;
  return clean === '/' ? '/sk/' : `/sk${clean}`;
}

/** The same page in the other language. */
export function switchLocalePath(locale: Locale, pathname: string): string {
  const bare = locale === 'sk' ? pathname.replace(/^\/sk(?=\/|$)/, '') || '/' : pathname;
  return localePath(locale === 'sk' ? 'en' : 'sk', bare);
}
