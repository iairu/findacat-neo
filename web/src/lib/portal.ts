import { api, ApiError, query, upload } from './api';

type Dict = Record<string, string>;
type Json = any;

const app = document.getElementById('app')!;
const page = app.dataset.page!;
const locale = app.dataset.locale!;
const sub = document.getElementById('subheader')!;
/** Page controls live in a band above the page heading (the slot is rendered by Portal.astro). */
const subheader = (html: string) => { sub.innerHTML = `<div class="subheader">${html}</div>`; return sub; };
const dict: Dict = JSON.parse(document.getElementById('i18n')!.textContent!);

const t = (key: string, vars: Record<string, string | number> = {}) =>
  (dict[key] ?? key).replace(/\{(\w+)\}/g, (_, n) => String(vars[n] ?? ''));
const href = (p: string) => (locale === 'sk' ? '/sk' : '') + p;
const esc = (v: unknown) =>
  String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]!);
const params = new URLSearchParams(location.search);
const fmtDate = (d?: string | null) => (d ? new Date(d + 'T00:00:00').toLocaleDateString(locale === 'sk' ? 'sk-SK' : 'en-GB') : '');
const pct = (f: number) => (f * 100).toLocaleString(locale === 'sk' ? 'sk-SK' : 'en-GB', { maximumFractionDigits: 2, minimumFractionDigits: 2 }) + ' %';
const sexLabel = (s?: string | null) => (s === 'male' ? t('common.male') : s === 'female' ? t('common.female') : t('common.unknown'));
const catUrl = (id: string) => href(`/cat/?id=${encodeURIComponent(id)}`);
const catName = (c: Json) => [c.title_prefix, c.name, c.title_suffix].filter(Boolean).join(' ');

// ---------------------------------------------------------------- EMS colours

const normEms = (v: unknown) => String(v ?? '').trim().replace(/\s+/g, ' ').toLowerCase();

/** First EMS letter = coat colour; a trailing s / y in the first group marks silver / golden. */
const COAT: Record<string, string> = {
  n: 'black', a: 'blue', b: 'chocolate', c: 'lilac', o: 'cinnamon', p: 'fawn', d: 'red', e: 'cream', w: 'white',
  f: 'black-tortie', g: 'blue-tortie', h: 'chocolate-tortie', j: 'lilac-tortie', q: 'cinnamon-tortie', r: 'fawn-tortie',
};

/** CSS classes colouring a code (and its name) by the coat colour the code spells out; '' when unrecognised. */
function coatClass(code: unknown) {
  const group = normEms(code).split(' ')[0] ?? '';
  const coat = group.length <= 3 ? COAT[group[0] ?? ''] : undefined;
  if (!coat) return '';
  return `coat coat--${coat}${group.slice(1).includes('s') ? ' coat--silver' : ''}${group.slice(1).includes('y') ? ' coat--golden' : ''}`;
}

/**
 * Exact code -> name match, mirroring the API: case and spacing are ignored, never a prefix.
 * The cat's own breed wins; otherwise the name must be the same on every row, else it is ambiguous (null).
 */
function emsName(rows: Json[], breeds: Json[], code: unknown, breedCode?: unknown): string | null {
  const key = normEms(code);
  const matches = key ? rows.filter((r) => String(r.english_name ?? '').trim() && normEms(r.code) === key) : [];
  if (!matches.length) return null;
  const breed = normEms(breedCode);
  const own = breed ? matches.find((r) => normEms(breeds.find((b) => b.id === r.breed_id)?.code) === breed) : undefined;
  if (own) return String(own.english_name).trim();
  return new Set(matches.map((r) => normEms(r.english_name))).size === 1 ? String(matches[0].english_name).trim() : null;
}

/** Countries by their English name (what the API stores), with the name in the page language as a hint. */
let countries: { en: string; local: string }[] | null = null;
function countryList() {
  if (countries) return countries;
  try {
    const en = new Intl.DisplayNames(['en'], { type: 'region' });
    const local = new Intl.DisplayNames([locale], { type: 'region' });
    const out: { en: string; local: string }[] = [];
    for (let a = 65; a <= 90; a++) for (let b = 65; b <= 90; b++) {
      const code = String.fromCharCode(a, b);
      const name = en.of(code);
      if (name && name !== code && !/^Unknown/.test(name)) out.push({ en: name, local: local.of(code) ?? name });
    }
    countries = out.sort((x, y) => x.en.localeCompare(y.en, 'en'));
  } catch { countries = []; }
  return countries;
}

const emsBadge = (code: string, name?: string | null) =>
  `<span class="ems ${coatClass(code)}"><span class="ems__swatch" aria-hidden="true"></span><span class="ems__code">${esc(code)}</span>${name ? ` <span class="ems__name">${esc(name)}</span>` : ''}</span>`;

const catCard = (c: Json) => {
  const titles = [c.title_prefix, c.title_suffix].filter(Boolean).join(' ');
  const deceased = Boolean(c.died_on);
  const sexClass = c.sex === 'male' ? 'cat-card--male' : c.sex === 'female' ? 'cat-card--female' : '';
  const classes = ['cat-card', sexClass, deceased ? 'cat-card--deceased' : '', titles ? 'cat-card--titled' : ''].filter(Boolean).join(' ');
  const sexSymbol = c.sex === 'male' ? '♂' : c.sex === 'female' ? '♀' : '';
  const coat = c.ems_code ? coatClass(c.ems_code) : '';
  const fact = (label: string, value: unknown, kind: string, extra = '') => (value
    ? `<div class="cat-card__fact cat-card__fact--${kind}${extra ? ` ${extra}` : ''}"><dt>${esc(label)}</dt><dd title="${esc(value)}">${esc(value)}</dd></div>` : '');
  return `<li class="${classes}"><a class="cat-card__link" href="${catUrl(c.id)}">
    <div class="cat-card__media">
      ${c.photo ? `<img class="cat-card__photo" src="${esc(c.photo)}" alt="" loading="lazy" referrerpolicy="no-referrer">` : `<div class="cat-card__placeholder" aria-hidden="true">🐾</div>`}
      ${c.sex ? `<span class="cat-card__sex cat-card__sex--${esc(c.sex)}" role="img" aria-label="${esc(sexLabel(c.sex))}" title="${esc(sexLabel(c.sex))}"><span aria-hidden="true">${sexSymbol}</span></span>` : ''}
    </div>
    <div class="cat-card__body">
      <h3 class="cat-card__name">${esc(catName(c))}</h3>
      <p class="cat-card__chips">
        ${deceased ? `<span class="cat-card__badge cat-card__badge--deceased">${esc(t('cats.deceased'))}</span>` : ''}
        ${c.breed_code ? `<span class="cat-chip cat-chip--breed">${esc(t('cat.fields.breed'))} <strong>${esc(c.breed_code)}</strong></span>` : ''}
        ${c.ems_code ? `<span class="cat-chip cat-chip--ems ${coat}"${c.ems_name ? ` title="${esc(c.ems_name)}"` : ''}><span class="ems__swatch" aria-hidden="true"></span>${esc(t('cat.fields.ems'))} <strong>${esc(c.ems_code)}</strong></span>` : ''}
      </p>
      <dl class="cat-card__facts">
        ${fact(t('cat.fields.colour'), c.ems_name, 'colour', coat)}
        ${fact(t('cat.fields.born'), fmtDate(c.born_on), 'born')}
        ${deceased ? fact(t('cat.fields.died'), fmtDate(c.died_on), 'died') : ''}
        ${fact(t('cat.fields.reg'), c.reg_number, 'reg')}
        ${fact(t('cat.fields.breeder'), c.breeder, 'breeder')}
        ${fact(t('cat.fields.owner'), c.owner_name, 'owner')}
        ${fact(t('cat.fields.country'), c.country, 'country')}
      </dl>
    </div></a></li>`;
};

const setHint = (user: Json | null) => {
  try {
    user ? localStorage.setItem('fac-user', user.name) : localStorage.removeItem('fac-user');
  } catch {}
};

async function me(): Promise<Json | null> {
  try {
    const { data } = await api('GET', '/user');
    setHint(data);
    return data;
  } catch (e) {
    if (e instanceof ApiError && e.status === 401) setHint(null);
    else if (!(e instanceof ApiError)) throw e;
    return null;
  }
}

const messageOf = (e: unknown) => (e instanceof ApiError ? Object.values(e.errors)[0]?.[0] ?? e.message : t('common.network'));
const note = (kind: 'error' | 'ok' | 'info', text: string) => `<p class="notice notice--${kind}" role="${kind === 'error' ? 'alert' : 'status'}">${esc(text)}</p>`;
const loginPrompt = () => `<p>${esc(t('auth.needLogin'))} <a href="${href('/login/')}">${esc(t('auth.login'))}</a></p>`;

// ---------------------------------------------------------------- form helpers

/** Colour group of a cat field; shared by the profile page and the edit form. */
const FIELD_KIND: Record<string, string> = {
  sex: 'sex', born_on: 'born', died_on: 'died', reg_number: 'reg', breed_code: 'breed', ems_code: 'ems',
  breeder: 'breeder', owner_name: 'owner', country: 'country',
};
const fieldClass = (name: string) => (FIELD_KIND[name] ? ` field--${FIELD_KIND[name]}` : '');

function input(name: string, label: string, opts: { type?: string; value?: unknown; required?: boolean; hint?: string; autocomplete?: string } = {}) {
  const id = 'f-' + name;
  return `<div class="field${fieldClass(name)}"><label for="${id}">${esc(label)}</label>
    <input id="${id}" name="${name}" type="${opts.type ?? 'text'}" value="${esc(opts.value)}"${opts.required ? ' required' : ''}${opts.autocomplete ? ` autocomplete="${opts.autocomplete}"` : ''}>
    ${opts.hint ? `<small>${esc(opts.hint)}</small>` : ''}<small class="field-error" data-error-for="${name}" role="alert"></small></div>`;
}

function select(name: string, label: string, options: [string, string][], value = '') {
  return `<div class="field${fieldClass(name)}"><label for="f-${name}">${esc(label)}</label><select id="f-${name}" name="${name}">
    ${options.map(([v, l]) => `<option value="${esc(v)}"${v === value ? ' selected' : ''}>${esc(l)}</option>`).join('')}</select>
    <small class="field-error" data-error-for="${name}" role="alert"></small></div>`;
}

type ComboOption = { value: string; html: string; text: string };

/** Markup of an editable dropdown (combobox); call enhanceCombo() on it afterwards to wire up the list. */
function combo(name: string, label: string, value: unknown, hint?: string) {
  const id = 'f-' + name;
  return `<div class="field field--combo${fieldClass(name)}"><label for="${id}">${esc(label)}</label>
    <div class="combobox">
      <input id="${id}" name="${name}" type="text" value="${esc(value)}" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="${id}-list" autocomplete="off" spellcheck="false">
      <button class="combobox__toggle" type="button" tabindex="-1" aria-label="${esc(label)}"><svg viewBox="0 0 12 8" width="12" height="8" aria-hidden="true"><path d="M1 1.5 6 6.5l5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      <ul class="combobox__list" id="${id}-list" role="listbox" aria-label="${esc(label)}" hidden></ul>
    </div>
    ${hint ? `<small>${esc(hint)}</small>` : ''}<small class="field-error" data-error-for="${name}" role="alert"></small></div>`;
}

/**
 * A proper dropdown over a text input: the full list opens on focus, click or arrow keys, narrows while
 * typing, and any custom value can still be typed. Selecting fires `input` so other widgets can react.
 */
function enhanceCombo(root: ParentNode, name: string, getOptions: () => ComboOption[]) {
  const box = root.querySelector<HTMLInputElement>(`#f-${name}`)!;
  const list = root.querySelector<HTMLUListElement>(`#f-${name}-list`)!;
  const toggle = root.querySelector<HTMLButtonElement>(`#f-${name} ~ .combobox__toggle`)!;
  const MAX = 100;
  let shown: ComboOption[] = [];
  let active = -1;
  let all: ComboOption[] = [];

  const setActive = (i: number) => {
    active = i;
    list.querySelectorAll('[role=option]').forEach((el, n) => {
      el.classList.toggle('is-active', n === i);
      if (n === i) { box.setAttribute('aria-activedescendant', el.id); el.scrollIntoView({ block: 'nearest' }); }
    });
    if (i < 0) box.removeAttribute('aria-activedescendant');
  };
  const close = () => { list.hidden = true; box.setAttribute('aria-expanded', 'false'); box.removeAttribute('aria-activedescendant'); active = -1; };
  const render = (filter: string) => {
    const f = normEms(filter);
    const hits = f ? all.filter((o) => normEms(o.text).includes(f)) : all;
    shown = hits.slice(0, MAX);
    const current = normEms(box.value);
    list.innerHTML = shown.map((o, n) => `<li id="f-${name}-opt${n}" role="option" data-i="${n}" aria-selected="${normEms(o.value) === current}">${o.html}</li>`).join('')
      + (hits.length > MAX ? `<li class="combobox__note" role="presentation">${esc(t('edit.typeToNarrow', { n: hits.length - MAX }))}</li>` : '')
      + (!hits.length ? `<li class="combobox__note" role="presentation">${esc(t('edit.customValue'))}</li>` : '');
    setActive(-1);
  };
  const open = (filter = '') => {
    all = getOptions();
    render(filter);
    list.hidden = false;
    box.setAttribute('aria-expanded', 'true');
    const selected = list.querySelector('[aria-selected=true]');
    if (selected && !filter) { setActive(Number((selected as HTMLElement).dataset.i)); }
  };
  const choose = (i: number) => {
    const o = shown[i];
    if (!o) return;
    box.value = o.value;
    box.dispatchEvent(new Event('input', { bubbles: true }));
    close();
  };

  box.addEventListener('focus', () => { if (list.hidden) open(); });
  box.addEventListener('click', () => { if (list.hidden) open(); });
  box.addEventListener('input', () => { if (document.activeElement === box) { open(box.value); } });
  toggle.addEventListener('mousedown', (ev) => { ev.preventDefault(); if (list.hidden) { box.focus(); open(); } else close(); });
  list.addEventListener('mousedown', (ev) => {
    ev.preventDefault();
    const li = (ev.target as HTMLElement).closest<HTMLElement>('[data-i]');
    if (li) choose(Number(li.dataset.i));
  });
  box.addEventListener('blur', close);
  box.addEventListener('keydown', (ev) => {
    if (ev.key === 'ArrowDown' || ev.key === 'ArrowUp') {
      ev.preventDefault();
      if (list.hidden) { open(); return; }
      const n = shown.length;
      if (n) setActive(ev.key === 'ArrowDown' ? (active + 1) % n : (active - 1 + n) % n);
    } else if (ev.key === 'Enter' && !list.hidden && active >= 0) {
      ev.preventDefault();
      choose(active);
    } else if (ev.key === 'Escape' && !list.hidden) {
      ev.preventDefault();
      close();
    } else if (ev.key === 'Tab') close();
  });
}

/** Compact, placeholder-led fields for the cats page filter bar. */
function compactInput(name: string, label: string, opts: { type?: string; value?: unknown; placeholder?: string; hint?: string; className?: string; required?: boolean; autocomplete?: string } = {}) {
  const id = 'f-' + name;
  return `<div class="subbar__field${opts.className ? ` ${opts.className}` : ''}">
    <label class="sr-only" for="${id}">${esc(label)}</label>
    <input id="${id}" name="${name}" type="${opts.type ?? 'search'}" value="${esc(opts.value)}" placeholder="${esc(opts.placeholder ?? label)}"${opts.hint ? ` title="${esc(opts.hint)}"` : ''}${opts.required ? ' required' : ''} autocomplete="${opts.autocomplete ?? 'off'}">
  </div>`;
}

/** Compact editable dropdown for a subheader; call enhanceCombo() on it afterwards. */
function compactCombo(name: string, label: string, value: unknown) {
  const id = 'f-' + name;
  return `<div class="subbar__field">
    <label class="sr-only" for="${id}">${esc(label)}</label>
    <div class="combobox">
      <input id="${id}" name="${name}" type="text" value="${esc(value)}" placeholder="${esc(label)}" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="${id}-list" autocomplete="off" spellcheck="false">
      <button class="combobox__toggle" type="button" tabindex="-1" aria-label="${esc(label)}"><svg viewBox="0 0 12 8" width="12" height="8" aria-hidden="true"><path d="M1 1.5 6 6.5l5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      <ul class="combobox__list" id="${id}-list" role="listbox" aria-label="${esc(label)}" hidden></ul>
    </div></div>`;
}

function compactSelect(name: string, label: string, options: [string, string][], value = '') {
  const id = 'f-' + name;
  return `<div class="subbar__field">
    <label class="sr-only" for="${id}">${esc(label)}</label>
    <select id="${id}" name="${name}">${options.map(([v, l]) => `<option value="${esc(v)}"${v === value ? ' selected' : ''}>${esc(l)}</option>`).join('')}</select>
  </div>`;
}

function showErrors(form: HTMLElement, e: unknown) {
  form.querySelectorAll('.field-error').forEach((el) => (el.textContent = ''));
  const general = form.querySelector('.form-error')!;
  general.textContent = '';
  if (e instanceof ApiError && e.status === 422) {
    let shown = false;
    for (const [field, msgs] of Object.entries(e.errors)) {
      const slot = form.querySelector(`[data-error-for="${field}"]`);
      if (slot) { slot.textContent = msgs[0]; shown = true; }
    }
    if (!shown) general.textContent = e.message;
  } else {
    general.textContent = e instanceof ApiError ? e.message : t('common.network');
  }
}

function onSubmit(form: HTMLFormElement, handler: (data: Record<string, string>) => Promise<void>) {
  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const button = form.querySelector<HTMLButtonElement>('button[type=submit]')!;
    button.disabled = true;
    try {
      await handler(Object.fromEntries(new FormData(form)) as Record<string, string>);
    } catch (e) {
      showErrors(form, e);
    } finally {
      button.disabled = false;
    }
  });
}

// ---------------------------------------------------------------- pedigree

function pedigreeNode(n: Json | null, depth: number): string {
  if (!n) return `<div class="ped-branch"><div class="ped-node ped-node--empty">${esc(t('common.unknown'))}</div></div>`;
  const meta = [n.breed_code, n.ems_code, fmtDate(n.born_on)].filter(Boolean).join(' · ');
  const parents = n.sire || n.dam ? `<div class="ped-parents">${pedigreeNode(n.sire, depth + 1)}${pedigreeNode(n.dam, depth + 1)}</div>` : '';
  return `<div class="ped-branch"><a class="ped-node ped-node--${esc(n.sex)}" href="${catUrl(n.id)}"><strong>${esc(catName(n))}</strong>${meta ? `<small>${esc(meta)}</small>` : ''}</a>${parents}</div>`;
}

const pedigreeHtml = (tree: Json) => `<div class="ped-scroll" role="group" aria-label="${esc(t('cat.pedigree'))}"><div class="ped">${pedigreeNode(tree, 0)}</div></div>`;

// ---------------------------------------------------------------- cat picker

type Picker = { id: string | null };

function picker(host: HTMLElement, label: string, sex: 'male' | 'female', initial?: { id: string; name: string } | null): Picker {
  const state: Picker = { id: initial?.id ?? null };
  host.innerHTML = `<div class="field picker"><label>${esc(label)}</label>
    <input type="search" autocomplete="off" placeholder="${esc(t('mating.typeToSearch'))}" value="${esc(initial?.name)}" aria-label="${esc(label)}">
    <ul class="picker__list" hidden></ul><small class="field-error" data-error-for="${host.dataset.name ?? ''}" role="alert"></small></div>`;
  const box = host.querySelector('input')!;
  const list = host.querySelector('ul')!;
  let timer: number;

  box.addEventListener('input', () => {
    state.id = null;
    clearTimeout(timer);
    timer = window.setTimeout(async () => {
      if (box.value.trim().length < 2) { list.hidden = true; return; }
      try {
        const res = await api('GET', '/cats' + query({ q: box.value.trim(), sex, per_page: 8 }));
        list.innerHTML = res.data.length
          ? res.data.map((c: Json) => `<li><button type="button" data-id="${esc(c.id)}" data-name="${esc(c.name)}">${esc(catName(c))}<small>${esc([c.breed_code, c.ems_code, fmtDate(c.born_on)].filter(Boolean).join(' · '))}</small></button></li>`).join('')
          : `<li class="picker__none">${esc(t('common.empty'))}</li>`;
        list.hidden = false;
      } catch { list.hidden = true; }
    }, 250);
  });
  list.addEventListener('click', (ev) => {
    const b = (ev.target as HTMLElement).closest<HTMLButtonElement>('button[data-id]');
    if (!b) return;
    state.id = b.dataset.id!;
    box.value = b.dataset.name!;
    list.hidden = true;
  });
  box.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') list.hidden = true; });
  return state;
}

// ---------------------------------------------------------------- pages

/** The EMS and Breeds pages: every code with the cats assigned to it, filterable, with a link to the full search. */
async function catalogPage(kind: 'ems' | 'breeds') {
  const isEms = kind === 'ems';
  subheader(`<form class="subbar" role="search" aria-label="${esc(t(isEms ? 'catalog.emsTitle' : 'catalog.breedsTitle'))}">
      ${compactInput('filter', t('catalog.filter'), { value: params.get('filter'), className: 'subbar__field--grow' })}
      <label class="subbar__check"><input type="checkbox" name="unused" value="1"${params.get('unused') ? ' checked' : ''}> ${esc(t('catalog.showUnused'))}</label></form>`);
  app.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
  let groups: Json[];
  try { groups = (await api('GET', isEms ? '/ems/cats' : '/breeds/cats')).data; } catch (e) { app.innerHTML = note('error', messageOf(e)); return; }
  const form = sub.querySelector<HTMLFormElement>('form')!;
  const filter = form.querySelector<HTMLInputElement>('[name=filter]')!;
  const unused = form.querySelector<HTMLInputElement>('[name=unused]')!;
  const field = isEms ? 'ems_code' : 'breed_code';

  const head = (g: Json) => isEms ? emsBadge(g.code, g.name) : `<strong>${esc(g.code)}</strong>${g.name ? ` <span class="combobox__sub">${esc(g.name)}</span>` : ''}`;
  const draw = () => {
    const f = normEms(filter.value);
    const shown = groups.filter((g) => (unused.checked || g.total > 0) && (!f || normEms(`${g.code} ${g.name ?? ''}`).includes(f)));
    app.innerHTML = shown.length ? `<p class="count">${esc(t('catalog.count', { n: shown.length }))}</p><div class="catalog">${shown.map((g) => {
      const summary = `<span class="catalog__head">${head(g)}</span><span class="catalog__total">${esc(t('catalog.cats', { n: g.total }))}</span>`;
      if (!g.total) return `<div class="catalog__group catalog__group--empty"><div class="catalog__summary">${summary}</div></div>`;
      const more = g.total > g.cats.length ? `<a class="btn btn--ghost" href="${href('/cats/')}${query({ [field]: g.code, exact: 1 })}">${esc(t('catalog.showAll', { n: g.total }))}</a>` : '';
      return `<details class="catalog__group"><summary class="catalog__summary">${summary}</summary>
        <ul class="catalog__cats">${g.cats.map((c: Json) => `<li><a href="${catUrl(c.id)}"><strong>${esc(c.name)}</strong><small>${esc([isEms ? c.breed_code : c.ems_code, fmtDate(c.born_on)].filter(Boolean).join(' · '))}</small></a></li>`).join('')}</ul>${more}</details>`;
    }).join('')}</div>` : `<p class="empty">${esc(t('common.empty'))}</p>`;
  };
  form.addEventListener('submit', (ev) => ev.preventDefault());
  filter.addEventListener('input', draw);
  unused.addEventListener('change', draw);
  draw();
}

async function catsPage() {
  const sexOptions: [string, string][] = [['', t('cats.anySex')], ['male', t('common.male')], ['female', t('common.female')]];
  subheader(`<form class="subbar" role="search" aria-label="${esc(t('cats.title'))}">
        ${compactInput('q', t('cats.name'), { value: params.get('q'), className: 'subbar__field--grow' })}
        ${compactSelect('sex', t('cat.fields.sex'), sexOptions, params.get('sex') ?? '')}
        ${compactCombo('breed_code', t('cat.fields.breed'), params.get('breed_code'))}
        ${compactCombo('ems_code', t('cat.fields.ems'), params.get('ems_code'))}
        ${compactInput('born', t('cat.fields.born'), { type: 'text', value: params.get('born'), hint: 'YYYY, YYYY-MM, YYYY-MM-DD' })}
        ${compactInput('reg_number', t('cat.fields.reg'), { value: params.get('reg_number') })}
        <label class="subbar__check"><input type="checkbox" name="exact" value="1"${params.get('exact') ? ' checked' : ''}> ${esc(t('cats.exact'))}</label>
        <button class="btn btn--primary" type="submit">${esc(t('common.search'))}</button>
        <p class="form-error" role="alert"></p>
      </form>`);
  app.innerHTML = `<div id="results" aria-live="polite"></div>`;
  const form = sub.querySelector<HTMLFormElement>('form')!;
  let emsRows: Json[] | null = null;
  enhanceCombo(sub, 'ems_code', () => {
    const seen = new Set<string>();
    return (emsRows ?? []).filter((e) => e.code).sort((x, y) => String(x.code).localeCompare(String(y.code), 'en', { numeric: true }))
      .filter((e) => { const k = `${normEms(e.code)}|${normEms(e.english_name)}`; return !seen.has(k) && seen.add(k); })
      .map((e) => ({ value: e.code, text: `${e.code} ${e.english_name ?? ''}`, html: emsBadge(e.code, e.english_name) }));
  });
  api('GET', '/ems').then((r) => { emsRows = r.data; }).catch(() => { emsRows = []; });
  let breedRows: Json[] = [];
  enhanceCombo(sub, 'breed_code', () => breedRows.filter((b) => b.code).map((b) => ({
    value: b.code, text: `${b.code} ${b.name ?? ''}`, html: `<strong>${esc(b.code)}</strong>${b.name ? ` <span class="combobox__sub">${esc(b.name)}</span>` : ''}`,
  })));
  api('GET', '/breeds').then((r) => { breedRows = r.data; }).catch(() => { breedRows = []; });
  const results = app.querySelector<HTMLElement>('#results')!;
  const cards = (data: Json[]) => `<ul class="cat-cards">${data.map((c: Json) => catCard(c)).join('')}</ul>`;

  async function loadRandom() {
    results.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
    try {
      const res = await api('GET', '/cats/random?limit=10');
      results.innerHTML = res.data.length
        ? `<p class="count">${esc(t('cats.random', { n: res.data.length }))}</p>${cards(res.data)}`
        : `<p class="empty">${esc(t('common.empty'))}</p>`;
    } catch (e) { results.innerHTML = note('error', messageOf(e)); }
  }

  async function run(pageNo: number) {
    const f = Object.fromEntries(new FormData(form)) as Record<string, string>;
    results.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
    history.replaceState(null, '', query({ ...f, page: pageNo > 1 ? pageNo : undefined }) || location.pathname);
    try {
      const res = await api('GET', '/cats' + query({ ...f, page: pageNo }));
      if (!res.data.length) { results.innerHTML = `<p class="empty">${esc(t('common.empty'))}</p>`; return; }
      results.innerHTML = `<p class="count">${esc(t('cats.count', { n: res.total }))}</p>${cards(res.data)}
        ${res.last_page > 1 ? `<nav class="pager"><button class="btn btn--ghost" data-go="${res.current_page - 1}"${res.current_page <= 1 ? ' disabled' : ''}>${esc(t('common.prev'))}</button>
          <span>${esc(t('common.page', { page: res.current_page, last: res.last_page }))}</span>
          <button class="btn btn--ghost" data-go="${res.current_page + 1}"${res.current_page >= res.last_page ? ' disabled' : ''}>${esc(t('common.next'))}</button></nav>` : ''}`;
    } catch (e) { results.innerHTML = note('error', messageOf(e)); }
  }
  results.addEventListener('click', (ev) => {
    const b = (ev.target as HTMLElement).closest<HTMLButtonElement>('button[data-go]');
    if (b) run(Number(b.dataset.go));
  });
  form.addEventListener('submit', (ev) => { ev.preventDefault(); run(1); });
  const hasSearch = ['q', 'sex', 'breed_code', 'ems_code', 'born', 'reg_number'].some((key) => params.get(key));
  if (hasSearch) run(Number(params.get('page') ?? 1));
  else loadRandom();
}

async function catPage() {
  const id = params.get('id');
  if (!id) { app.innerHTML = note('info', t('cat.missing')); return; }
  app.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
  let generations = Number(params.get('generations') ?? 4);
  try {
    const { data: c } = await api('GET', `/cats/${encodeURIComponent(id)}`);
    document.title = catName(c) + ' · ' + document.title;
    document.querySelector('.portal__title')!.textContent = catName(c);
    const row = (label: string, value: unknown, kind = '', extra = '') => (value ? `<div class="fact${kind ? ` fact--${kind}` : ''}${extra ? ` ${extra}` : ''}"><dt>${esc(label)}</dt><dd>${value}</dd></div>` : '');
    const parent = (p: Json | null, fallbackId: string | null) => (p ? `<a href="${catUrl(p.id)}">${esc(p.name)}</a>` : fallbackId ? esc(t('common.unknown')) : '');
    app.innerHTML = `
      ${c.moderation_status === 'pending' ? note('info', t('cat.pendingNote')) : ''}${c.moderation_status === 'rejected' ? note('error', t('cat.rejectedNote')) : ''}
      ${c.can_edit ? `<p><a class="btn btn--ghost" href="${href('/cat-edit/')}?id=${encodeURIComponent(c.id)}">${esc(t('common.edit'))}</a></p>` : ''}
      <div class="cat-grid">
        <dl class="facts">
          ${row(t('cat.fields.sex'), esc(sexLabel(c.sex)), c.sex === 'male' ? 'male' : c.sex === 'female' ? 'female' : '')}${row(t('cat.fields.born'), esc(fmtDate(c.born_on)), 'born')}${row(t('cat.fields.died'), esc(fmtDate(c.died_on)), 'died')}
          ${row(t('cat.fields.breed'), esc(c.breed_code), 'breed')}${row(t('cat.fields.ems'), c.ems_code ? emsBadge(c.ems_code, c.ems_name) : '', 'ems', coatClass(c.ems_code))}${row(t('cat.fields.reg'), esc(c.reg_number), 'reg')}
          ${row(t('cat.fields.sire'), parent(c.sire, c.sire_id), 'male')}${row(t('cat.fields.dam'), parent(c.dam, c.dam_id), 'female')}
          ${row(t('cat.fields.breeder'), esc(c.breeder), 'breeder')}${row(t('cat.fields.owner'), esc(c.owner_name), 'owner')}${row(t('cat.fields.country'), esc(c.country), 'country')}
          ${row(t('cat.fields.notes'), esc(c.notes))}
        </dl>
        <aside class="coi"><h2>${esc(t('cat.inbreeding'))}</h2><p class="coi__value">${esc(pct(c.inbreeding))}</p>
          <div class="coi__bar" aria-hidden="true"><span style="width:${Math.min(100, c.inbreeding * 100 * 2)}%"></span></div>
          <p class="coi__help">${esc(t('cat.inbreedingHelp'))}</p></aside>
        ${c.photo ? `<img class="cat-photo" src="${esc(c.photo)}" alt="${esc(c.name)}" loading="lazy" referrerpolicy="no-referrer">` : ''}
      </div>
      <section><div class="section-bar"><h2>${esc(t('cat.pedigree'))}</h2>
        <label>${esc(t('cat.generations'))} <select id="gens">${[2, 3, 4, 5, 6].map((n) => `<option${n === generations ? ' selected' : ''}>${n}</option>`).join('')}</select></label></div>
        <div id="tree"></div></section>
      ${c.children.length ? `<section><h2>${esc(t('cat.kittens'))}</h2><ul class="cat-list">${c.children.map((k: Json) => `<li><a href="${catUrl(k.id)}"><strong>${esc(k.name)}</strong><span>${esc([sexLabel(k.sex), fmtDate(k.born_on)].filter(Boolean).join(' · '))}</span></a></li>`).join('')}</ul></section>` : ''}`;

    const tree = app.querySelector<HTMLElement>('#tree')!;
    const load = async () => {
      try { tree.innerHTML = pedigreeHtml((await api('GET', `/cats/${encodeURIComponent(id)}/pedigree?generations=${generations}`)).data); }
      catch (e) { tree.innerHTML = note('error', messageOf(e)); }
    };
    app.querySelector<HTMLSelectElement>('#gens')!.addEventListener('change', (ev) => { generations = Number((ev.target as HTMLSelectElement).value); load(); });
    load();
  } catch (e) { app.innerHTML = note('error', e instanceof ApiError && e.status === 404 ? t('common.empty') : messageOf(e)); }
}

function matingPage() {
  subheader(`<form class="subbar subbar--end" role="search" aria-label="${esc(t('mating.title'))}"><div id="sire-host" class="subbar__host" data-name="sire"></div><div id="dam-host" class="subbar__host" data-name="dam"></div>
      <button class="btn btn--primary" type="submit">${esc(t('mating.run'))}</button>
      <p class="form-error" role="alert"></p></form>`);
  app.innerHTML = `<p class="lead">${esc(t('mating.lead'))}</p><div id="out" aria-live="polite"></div>`;
  const sire = picker(sub.querySelector('#sire-host')!, t('mating.sire'), 'male');
  const dam = picker(sub.querySelector('#dam-host')!, t('mating.dam'), 'female');
  const out = app.querySelector<HTMLElement>('#out')!;
  const form = sub.querySelector<HTMLFormElement>('form')!;
  onSubmit(form, async () => {
    if (!sire.id || !dam.id) { form.querySelector('.form-error')!.textContent = t('mating.need'); return; }
    form.querySelector('.form-error')!.textContent = '';
    const { data } = await api('GET', '/test-mating' + query({ sire: sire.id, dam: dam.id, generations: 4 }));
    out.innerHTML = `<section class="coi coi--wide"><h2>${esc(t('mating.result'))}</h2><p class="coi__value">${esc(pct(data.inbreeding))}</p>
      <div class="coi__bar" aria-hidden="true"><span style="width:${Math.min(100, data.inbreeding * 200)}%"></span></div>
      <p class="coi__help">${esc(t('cat.inbreedingHelp'))} <a href="${href('/docs/genetics/inbreeding/')}">?</a></p></section>
      <h2>${esc(t('cat.pedigree'))}</h2>
      <div class="ped-scroll"><div class="ped"><div class="ped-branch"><div class="ped-node ped-node--empty">${esc(t('mating.result'))}</div>
      <div class="ped-parents">${pedigreeNode(data.sire, 0)}${pedigreeNode(data.dam, 0)}</div></div></div></div>`;
  });
}

async function birthdaysPage() {
  const now = new Date();
  const year = now.getFullYear();
  const day = 86400000;
  const iso = (d: Date) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const addDays = (d: Date, n: number) => new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
  const mondayOf = (d: Date) => addDays(d, -((d.getDay() + 6) % 7));
  const isoWeek = (monday: Date) => {
    const thursday = addDays(monday, 3);
    return Math.floor((thursday.getTime() - new Date(thursday.getFullYear(), 0, 1).getTime()) / day / 7) + 1;
  };
  const short = (d: Date) => d.toLocaleDateString(locale === 'sk' ? 'sk-SK' : 'en-GB', { day: 'numeric', month: 'short' });
  const thisMonday = mondayOf(now);
  let monday = thisMonday;

  subheader(`<div class="subbar" role="group" aria-label="${esc(t('birthdays.title'))}">
      <button class="btn btn--ghost" type="button" data-week="-1" aria-label="${esc(t('birthdays.prev'))}">‹</button>
      <div class="subbar__label" id="week-label" aria-live="polite"></div>
      <button class="btn btn--ghost" type="button" data-week="1" aria-label="${esc(t('birthdays.next'))}">›</button>
      <button class="btn btn--ghost" type="button" id="this-week">${esc(t('birthdays.thisWeek'))}</button>
    </div>`);
  app.innerHTML = `<p class="lead">${esc(t('birthdays.lead'))}</p><div id="out" aria-live="polite"></div>`;
  const out = app.querySelector<HTMLElement>('#out')!;
  const label = sub.querySelector<HTMLElement>('#week-label')!;
  const prev = sub.querySelector<HTMLButtonElement>('[data-week="-1"]')!;
  const next = sub.querySelector<HTMLButtonElement>('[data-week="1"]')!;
  const current = sub.querySelector<HTMLButtonElement>('#this-week')!;

  async function load() {
    const sunday = addDays(monday, 6);
    label.innerHTML = `${esc(t('birthdays.week', { n: isoWeek(monday) }))}<small>${esc(short(monday))} – ${esc(short(sunday))}</small>`;
    // The API works within the current year, so weeks are kept inside it.
    prev.disabled = addDays(monday, -1).getFullYear() < year;
    next.disabled = addDays(monday, 7).getFullYear() > year;
    current.disabled = monday.getTime() === thisMonday.getTime();
    out.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
    const start = monday.getFullYear() < year ? new Date(year, 0, 1) : monday;
    try {
      const { data } = await api('GET', '/birthdays' + query({ month: start.getMonth() + 1, day: start.getDate() }));
      const from = iso(monday), to = iso(sunday);
      const rows = data.filter((b: Json) => b.birthday >= from && b.birthday <= to);
      out.innerHTML = rows.length
        ? `<ul class="cat-list">${rows.map((b: Json) => `<li><a href="${catUrl(b.id)}"><strong>${esc(b.name)}</strong><span>${esc(fmtDate(b.birthday))} · ${esc(t('birthdays.turns', { n: b.turns }))}</span></a></li>`).join('')}</ul>`
        : `<p class="empty">${esc(t('common.empty'))}</p>`;
    } catch (e) { out.innerHTML = note('error', messageOf(e)); }
  }
  sub.querySelector('.subbar')!.addEventListener('click', (ev) => {
    const b = (ev.target as HTMLElement).closest<HTMLButtonElement>('button');
    if (!b || b.disabled) return;
    monday = b.id === 'this-week' ? thisMonday : addDays(monday, Number(b.dataset.week) * 7);
    load();
  });
  load();
}

function authForm(fields: string, submitLabel: string) {
  return `<form class="stack" novalidate>${fields}<p class="form-error" role="alert"></p><button class="btn btn--primary" type="submit">${esc(submitLabel)}</button></form>`;
}

function loginPage() {
  app.innerHTML = authForm(input('email', t('auth.email'), { type: 'email', required: true, autocomplete: 'username' }) + input('password', t('auth.password'), { type: 'password', required: true, autocomplete: 'current-password' }), t('auth.login'))
    + `<p class="aside"><a href="${href('/forgot-password/')}">${esc(t('auth.forgot'))}</a><br>${esc(t('auth.noAccount'))} <a href="${href('/register/')}">${esc(t('auth.register'))}</a></p>`;
  onSubmit(app.querySelector('form')!, async (d) => {
    const { data } = await api('POST', '/login', d);
    setHint(data);
    location.href = href('/account/');
  });
}

async function registerPage() {
  const settings = await api('GET', '/settings').catch(() => ({ signups: true }));
  if (!settings.signups) {
    app.innerHTML = note('info', t('auth.signupsClosed'))
      + `<p class="aside">${esc(t('auth.haveAccount'))} <a href="${href('/login/')}">${esc(t('auth.login'))}</a></p>`;
    return;
  }
  app.innerHTML = `<p class="lead">${esc(t('auth.registerLead'))}</p>` + authForm(
    input('name', t('auth.name'), { required: true, autocomplete: 'name' }) + input('email', t('auth.email'), { type: 'email', required: true, autocomplete: 'email' })
    + input('password', t('auth.password'), { type: 'password', required: true, hint: t('auth.passwordHint'), autocomplete: 'new-password' })
    + input('password_confirmation', t('auth.passwordConfirm'), { type: 'password', required: true, autocomplete: 'new-password' }), t('auth.register'))
    + `<p class="aside">${esc(t('auth.haveAccount'))} <a href="${href('/login/')}">${esc(t('auth.login'))}</a></p>`;
  onSubmit(app.querySelector('form')!, async (d) => {
    const { data } = await api('POST', '/register', { ...d, locale });
    setHint(data);
    location.href = href('/account/');
  });
}

function forgotPage() {
  app.innerHTML = authForm(input('email', t('auth.email'), { type: 'email', required: true, autocomplete: 'email' }), t('auth.forgotSend'));
  const form = app.querySelector('form')!;
  onSubmit(form, async (d) => {
    await api('POST', '/forgot-password', d);
    form.outerHTML = note('ok', t('auth.forgotDone'));
  });
}

function resetPage() {
  app.innerHTML = authForm(
    `<input type="hidden" name="token" value="${esc(params.get('token'))}"><input type="hidden" name="email" value="${esc(params.get('email'))}">`
    + input('password', t('auth.passwordNew'), { type: 'password', required: true, hint: t('auth.passwordHint'), autocomplete: 'new-password' })
    + input('password_confirmation', t('auth.passwordConfirm'), { type: 'password', required: true, autocomplete: 'new-password' }), t('common.save'));
  const form = app.querySelector('form')!;
  onSubmit(form, async (d) => {
    await api('POST', '/reset-password', d);
    form.outerHTML = note('ok', t('auth.resetDone')) + `<p><a class="btn btn--primary" href="${href('/login/')}">${esc(t('auth.login'))}</a></p>`;
  });
}

async function accountPage() {
  const user = await me();
  if (!user) { app.innerHTML = loginPrompt(); return; }
  const statusLabel = (s: string) => t('status.' + s);
  subheader(`<nav class="subbar" aria-label="${esc(t('account.title'))}">
    <a class="btn btn--ghost" href="${href('/account-edit/')}">${esc(t('account.edit'))}</a>
    ${user.is_admin ? `<a class="btn btn--ghost" href="${href('/admin/')}">${esc(t('nav.admin'))}</a>` : ''}
    <button class="btn btn--ghost subbar__push" id="logout" type="button">${esc(t('auth.logout'))}</button></nav>`);
  app.innerHTML = `${params.get('verified') ? note('ok', t('auth.verifiedNow')) : ''}
    ${user.verified ? '' : `<div class="notice notice--info">${esc(t('auth.verifyNotice'))} <button class="btn btn--ghost" id="resend" type="button">${esc(t('auth.verifyResend'))}</button><span id="resend-out"></span></div>`}
    <p class="lead">${esc(user.name)} · ${esc(user.email)}</p>
    <section id="mine"></section>`;
  app.querySelector('#resend')?.addEventListener('click', async () => {
    try { await api('POST', '/email/resend'); app.querySelector('#resend-out')!.textContent = ' ' + t('auth.verifySent'); } catch (e) { app.querySelector('#resend-out')!.textContent = ' ' + messageOf(e); }
  });
  sub.querySelector('#logout')!.addEventListener('click', async () => {
    await api('POST', '/logout').catch(() => {});
    setHint(null);
    location.href = href('/');
  });
  const mine = app.querySelector<HTMLElement>('#mine')!;
  if (!user.verified) return;
  try {
    const { data } = await api('GET', '/my/cats');
    mine.innerHTML = `<div class="section-bar"><h2>${esc(t('account.myCats'))}</h2><a class="btn btn--primary" href="${href('/cat-edit/')}">${esc(t('account.addCat'))}</a></div>`
      + (data.length ? `<ul class="cat-list">${data.map((c: Json) => `<li><a href="${catUrl(c.id)}"><strong>${esc(catName(c))}</strong><span>${esc(statusLabel(c.moderation_status))}</span></a></li>`).join('')}</ul>` : `<p class="empty">${esc(t('account.noCats'))}</p>`);
  } catch (e) { mine.innerHTML = note('error', messageOf(e)); }
}

async function accountEditPage() {
  const user = await me();
  if (!user) { app.innerHTML = loginPrompt(); return; }
  subheader(`<nav class="subbar" aria-label="${esc(t('account.edit'))}">
    <a class="btn btn--ghost subbar__back" href="${href('/account/')}"><span aria-hidden="true">‹</span> ${esc(t('account.back'))}</a></nav>`);
  app.innerHTML = `<form class="stack" id="profile" novalidate>${input('name', t('auth.name'), { value: user.name, required: true, autocomplete: 'name' })}
    ${select('locale', t('auth.language'), [['en', 'English'], ['sk', 'Slovenčina']], user.locale)}
    <p class="form-error" role="alert"></p><div class="row"><button class="btn btn--primary" type="submit">${esc(t('common.save'))}</button><span class="subbar__status" id="saved" role="status"></span></div></form>`;
  const saved = app.querySelector<HTMLElement>('#saved')!;
  onSubmit(app.querySelector('#profile')!, async (d) => {
    saved.textContent = '';
    await api('PATCH', '/user', d);
    saved.textContent = t('account.saved');
  });
}

async function catEditPage() {
  const user = await me();
  if (!user) { app.innerHTML = loginPrompt(); return; }
  if (!user.verified) { app.innerHTML = note('info', t('auth.needVerify')); return; }
  const id = params.get('id');
  let cat: Json = {};
  if (id) {
    try {
      cat = (await api('GET', `/cats/${encodeURIComponent(id)}`)).data;
      if (!cat.can_edit) { app.innerHTML = note('error', t('common.error')); return; }
    } catch (e) { app.innerHTML = note('error', messageOf(e)); return; }
  }
  document.querySelector('h1')!.textContent = t(id ? 'edit.editTitle' : 'edit.newTitle');
  const list = (r: string) => api('GET', '/' + r).then((x) => x.data as Json[]).catch(() => [] as Json[]);
  const [breeds, emsColors] = await Promise.all([list('breeds'), list('ems')]);
  const breedById = Object.fromEntries(breeds.map((b) => [b.id, b]));
  const breedOptions = (): ComboOption[] => breeds.map((b) => ({
    value: b.code, text: `${b.code} ${b.name ?? ''}`, html: `<strong>${esc(b.code)}</strong>${b.name ? ` <span class="combobox__sub">${esc(b.name)}</span>` : ''}`,
  }));
  const emsOptions = (): ComboOption[] => {
    // A known breed in the breed field limits the list to that breed's colours (plus colours not tied to any breed).
    const own = normEms((app.querySelector('#f-breed_code') as HTMLInputElement | null)?.value);
    const rows = emsColors.filter((e) => e.code).map((e) => ({ e, breed: breedById[e.breed_id]?.code as string | undefined }));
    const forBreed = rows.filter(({ breed }) => !breed || normEms(breed) === own);
    // Colours with no breed belong to every breed; if the breed has none at all, offer everything rather than an empty list.
    const known = !!own && breeds.some((b) => normEms(b.code) === own) && forBreed.length > 0;
    // The selected breed's own colours come first, then the generic ones, then (only with no known breed) other breeds' colours.
    const rank = (breed?: string) => (breed && normEms(breed) === own ? 0 : !breed ? 1 : 2);
    const seen = new Set<string>();
    return rows
      .filter(({ breed }) => !known || !breed || normEms(breed) === own)
      .sort((x, y) => rank(x.breed) - rank(y.breed) || String(x.e.code).localeCompare(String(y.e.code), 'en', { numeric: true }))
      .filter(({ e, breed }) => { const k = `${normEms(e.code)}|${normEms(e.english_name)}|${normEms(breed)}`; return !seen.has(k) && seen.add(k); })
      .map(({ e, breed }) => ({
        value: e.code, text: `${e.code} ${e.english_name ?? ''} ${breed ?? ''}`,
        html: `${emsBadge(e.code, e.english_name)}${breed && !known ? ` <span class="combobox__sub">${esc(breed)}</span>` : ''}`,
      }));
  };
  const countryOptions = (): ComboOption[] => countryList().map(({ en, local }) => ({
    value: en, text: `${en} ${local}`, html: `${esc(en)}${local !== en ? ` <span class="combobox__sub">${esc(local)}</span>` : ''}`,
  }));
  const titleOptions = (): ComboOption[] => ['CH', 'IC', 'GIC', 'SC', 'IP', 'GIP', 'NW', 'WW', 'JW', 'DSM', 'DVM', 'DM'].map((x) => ({ value: x, text: x, html: `<strong>${x}</strong>` }));
  const emsHint = (breed: unknown, code: unknown) => {
    const name = emsName(emsColors, breeds, code, breed);
    return normEms(code) ? (name ? emsBadge(String(code).trim(), name) : `<span class="ems-hint__none">${esc(t('edit.emsUnknown'))}</span>`) : '';
  };
  const text = (name: string, label: string, type = 'text') => input(name, label, { value: cat[name], type });
  app.innerHTML = `<form class="stack cat-form" novalidate>
      ${input('name', t('cat.fields.name'), { value: cat.name, required: true })}
      ${select('sex', t('cat.fields.sex'), [['male', t('common.male')], ['female', t('common.female')]], cat.sex ?? 'female')}
      <div id="sire-host" data-name="sire_id"></div><div id="dam-host" data-name="dam_id"></div>
      ${text('born_on', t('cat.fields.born'), 'date')}${text('died_on', t('cat.fields.died'), 'date')}
      ${combo('title_prefix', t('cat.fields.titlePrefix'), cat.title_prefix)}${combo('title_suffix', t('cat.fields.titleSuffix'), cat.title_suffix)}
      ${combo('breed_code', t('cat.fields.breed'), cat.breed_code)}${combo('ems_code', t('cat.fields.ems'), cat.ems_code)}${text('reg_number', t('cat.fields.reg'))}
      ${text('breeder', t('cat.fields.breeder'))}${text('owner_name', t('cat.fields.owner'))}${combo('country', t('cat.fields.country'), cat.country)}
      ${text('chip_number', t('cat.fields.chip'))}${text('photo', t('cat.fields.photo'), 'url')}
      <div class="field"><label for="f-notes">${esc(t('cat.fields.notes'))}</label><textarea id="f-notes" name="notes" rows="4">${esc(cat.notes)}</textarea><small class="field-error" data-error-for="notes" role="alert"></small></div>
      <p class="form-error" role="alert"></p>
      <div class="row"><button class="btn btn--primary" type="submit">${esc(t('common.save'))}</button>
        ${id ? `<button class="btn btn--ghost" type="button" id="delete">${esc(t('common.delete'))}</button>` : ''}</div></form>`;
  const emsInput = app.querySelector<HTMLInputElement>('#f-ems_code')!;
  const breedInput = app.querySelector<HTMLInputElement>('#f-breed_code')!;
  const hint = document.createElement('small');
  hint.className = 'ems-hint';
  hint.setAttribute('aria-live', 'polite');
  emsInput.closest('.combobox')!.after(hint);
  const refreshHint = () => { hint.innerHTML = emsHint(breedInput.value, emsInput.value); };
  emsInput.addEventListener('input', refreshHint);
  breedInput.addEventListener('input', refreshHint);
  refreshHint();
  for (const [field, options] of [['title_prefix', titleOptions], ['title_suffix', titleOptions], ['breed_code', breedOptions], ['ems_code', emsOptions], ['country', countryOptions]] as const) enhanceCombo(app, field, options);
  const sire = picker(app.querySelector('#sire-host')!, t('cat.fields.sire'), 'male', cat.sire ?? null);
  const dam = picker(app.querySelector('#dam-host')!, t('cat.fields.dam'), 'female', cat.dam ?? null);
  const form = app.querySelector<HTMLFormElement>('form')!;
  onSubmit(form, async (d) => {
    const body: Json = Object.fromEntries(Object.entries(d).map(([k, v]) => [k, v === '' ? null : v]));
    body.sire_id = sire.id;
    body.dam_id = dam.id;
    const { data } = id ? await api('PUT', `/cats/${encodeURIComponent(id)}`, body) : await api('POST', '/cats', body);
    if (data.moderation_status === 'pending') {
      app.innerHTML = note('ok', t('edit.savedPending')) + `<p><a class="btn btn--primary" href="${href('/account/')}">${esc(t('nav.account'))}</a></p>`;
    } else location.href = catUrl(data.id);
  });
  app.querySelector('#delete')?.addEventListener('click', async () => {
    if (!confirm(t('edit.confirmDelete'))) return;
    try { await api('DELETE', `/cats/${encodeURIComponent(id!)}`); location.href = href('/account/'); } catch (e) { showErrors(form, e); }
  });
}

// ---------------------------------------------------------------- admin

const TABLES = ['cats', 'breeds', 'ems'] as const;

async function adminPage() {
  const user = await me();
  if (!user) { app.innerHTML = loginPrompt(); return; }
  if (!user.is_admin) { app.innerHTML = note('error', t('admin.forbidden')); return; }

  const tabs: Record<string, () => Promise<void>> = { queue: queueTab, breeds: breedsTab, ems: emsTab, users: usersTab, backup: backupTab };
  subheader(`<nav class="subbar" aria-label="${esc(t('nav.admin'))}">
    <a class="btn btn--ghost subbar__back" href="${href('/account/')}"><span aria-hidden="true">‹</span> ${esc(t('admin.back'))}</a>
    <div class="subbar__tabs" role="tablist">${Object.keys(tabs).map((k) => `<button class="btn btn--ghost" role="tab" data-tab="${k}">${esc(t('admin.tab.' + k))}</button>`).join('')}</div></nav>`);
  app.innerHTML = `<div id="panel"></div>`;
  const panel = app.querySelector<HTMLElement>('#panel')!;
  const open = async (key: string) => {
    sub.querySelectorAll<HTMLElement>('[data-tab]').forEach((b) => b.setAttribute('aria-selected', String(b.dataset.tab === key)));
    panel.innerHTML = '';
    try { await tabs[key](); } catch (e) { panel.innerHTML = note('error', messageOf(e)); }
  };
  sub.querySelector('.subbar__tabs')!.addEventListener('click', (ev) => {
    const b = (ev.target as HTMLElement).closest<HTMLElement>('[data-tab]');
    if (b) { history.replaceState(null, '', '#' + b.dataset.tab); open(b.dataset.tab!); }
  });
  open(tabs[location.hash.slice(1)] ? location.hash.slice(1) : 'queue');

  async function queueTab() {
    const { data } = await api('GET', '/admin/cats?status=pending');
    panel.innerHTML = data.length
      ? `<ul class="cat-list cat-list--wide">${data.map((c: Json) => `<li data-id="${esc(c.id)}"><a href="${catUrl(c.id)}"><strong>${esc(catName(c))}</strong><span>${esc([c.ems_code, c.owner ? t('admin.addedBy', { name: c.owner.name }) : ''].filter(Boolean).join(' · '))}</span></a>
          <div class="row"><button class="btn btn--primary" data-act="approve">${esc(t('admin.approve'))}</button><button class="btn btn--ghost" data-act="reject">${esc(t('admin.reject'))}</button></div></li>`).join('')}</ul>`
      : `<p class="empty">${esc(t('admin.queueEmpty'))}</p>`;
    panel.onclick = async (ev) => {
      const b = (ev.target as HTMLElement).closest<HTMLButtonElement>('[data-act]');
      if (!b) return;
      const li = b.closest<HTMLElement>('li')!;
      b.disabled = true;
      try {
        await api('POST', `/admin/cats/${encodeURIComponent(li.dataset.id!)}/${b.dataset.act}`);
        li.remove();
        if (!panel.querySelector('li')) panel.innerHTML = `<p class="empty">${esc(t('admin.queueEmpty'))}</p>`;
      } catch (e) { b.disabled = false; panel.insertAdjacentHTML('afterbegin', note('error', messageOf(e))); }
    };
  }

  /** Shared list-with-add-form editor for breeds and EMS colours. */
  async function crudTab(resource: 'breeds' | 'ems', fields: { name: string; label: string; required?: boolean; options?: [string, string][] }[], label: (r: Json) => string) {
    const { data } = await api('GET', '/' + resource);
    panel.innerHTML = `<form class="stack row-form" novalidate>${fields.map((f) => (f.options ? select(f.name, f.label, f.options) : input(f.name, f.label, { required: f.required }))).join('')}
        <p class="form-error" role="alert"></p><div><button class="btn btn--primary" type="submit">${esc(t('admin.add'))}</button></div></form>
      <ul class="cat-list cat-list--wide">${data.map((r: Json) => `<li data-id="${esc(r.id)}"><span><strong>${esc(label(r))}</strong></span><button class="btn btn--ghost" data-del>${esc(t('common.delete'))}</button></li>`).join('')}</ul>`;
    onSubmit(panel.querySelector('form')!, async (d) => {
      await api('POST', '/admin/' + resource, Object.fromEntries(Object.entries(d).map(([k, v]) => [k, v === '' ? null : v])));
      await open(resource);
    });
    panel.onclick = async (ev) => {
      const b = (ev.target as HTMLElement).closest<HTMLElement>('[data-del]');
      if (!b || !confirm(t('admin.confirmDelete'))) return;
      try { await api('DELETE', `/admin/${resource}/${encodeURIComponent(b.closest<HTMLElement>('li')!.dataset.id!)}`); await open(resource); }
      catch (e) { panel.insertAdjacentHTML('afterbegin', note('error', messageOf(e))); }
    };
  }

  async function breedsTab() {
    await crudTab('breeds', [{ name: 'code', label: t('admin.code'), required: true }, { name: 'name', label: t('admin.name') }], (r) => [r.code, r.name].filter(Boolean).join(' · '));
  }

  async function emsTab() {
    const breeds = (await api('GET', '/breeds')).data as Json[];
    const names = Object.fromEntries(breeds.map((b) => [b.id, b.code]));
    await crudTab('ems', [
      { name: 'code', label: t('admin.code'), required: true }, { name: 'english_name', label: t('admin.englishName') },
      { name: 'breed_id', label: t('admin.breed'), options: [['', t('admin.noBreed')], ...breeds.map((b): [string, string] => [b.id, b.code])] },
    ], (r) => [names[r.breed_id], r.code, r.english_name].filter(Boolean).join(' · '));
  }

  async function usersTab() {
    panel.innerHTML = `<div class="field"><label for="user-q">${esc(t('admin.searchUsers'))}</label><input id="user-q" type="search"></div><ul class="cat-list cat-list--wide" id="users"></ul>`;
    const list = panel.querySelector<HTMLElement>('#users')!;
    const load = async (q: string) => {
      const { data } = await api('GET', '/admin/users' + query({ q }));
      list.innerHTML = data.map((u: Json) => `<li data-id="${esc(u.id)}"><span><strong>${esc(u.name)}</strong> <small>${esc(u.email)}</small></span>
        <label class="check"><input type="checkbox" ${u.is_admin ? 'checked' : ''}${u.id === user.id ? ' disabled' : ''}> ${esc(t('admin.isAdmin'))}</label></li>`).join('');
    };
    let timer: number;
    panel.querySelector('#user-q')!.addEventListener('input', (ev) => {
      clearTimeout(timer);
      timer = window.setTimeout(() => load((ev.target as HTMLInputElement).value.trim()).catch(() => {}), 250);
    });
    list.addEventListener('change', async (ev) => {
      const box = ev.target as HTMLInputElement;
      try { await api('PATCH', `/admin/users/${encodeURIComponent(box.closest<HTMLElement>('li')!.dataset.id!)}`, { is_admin: box.checked }); }
      catch (e) { box.checked = !box.checked; panel.insertAdjacentHTML('afterbegin', note('error', messageOf(e))); }
    });
    await load('');
  }

  async function backupTab() {
    panel.innerHTML = `<p class="lead">${esc(t('admin.backupLead'))}</p>`
      + TABLES.map((tb) => `<section class="backup" data-table="${tb}"><h2>${esc(tb === 'cats' ? t('admin.cats') : t('admin.tab.' + tb))}</h2>
        <p class="row"><a class="btn btn--ghost" href="/api/admin/backup/${tb}" download>${esc(t('admin.export'))}</a>${tb === 'cats' ? `<button class="btn btn--ghost" type="button" data-seed>${esc(t('admin.seed'))}</button>` : ''}${tb === 'breeds' ? `<button class="btn btn--ghost" type="button" data-reference>${esc(t('admin.loadReference'))}</button>` : ''}</p>
        ${tb === 'cats' ? `<div class="import-out" data-seed-out aria-live="polite"></div>` : ''}${tb === 'breeds' ? `<div class="import-out" data-reference-out aria-live="polite"></div>` : ''}
        <form class="stack" data-import novalidate><div class="field"><label for="file-${tb}">${esc(t('admin.file'))}</label><input id="file-${tb}" name="file" type="file" accept=".csv,text/csv" required><small class="field-error" data-error-for="file" role="alert"></small></div>
          <p class="form-error" role="alert"></p><div class="import-out" aria-live="polite"></div><div><button class="btn btn--primary" type="submit">${esc(t('admin.import'))}</button></div></form></section>`).join('')
      + `<section class="danger"><h2>${esc(t('admin.danger'))}</h2><p>${esc(t('admin.clearLead'))}</p>
        ${TABLES.map((tb) => `<form class="row" data-clear="${tb}" novalidate><input name="confirm" aria-label="DELETE" placeholder="DELETE" autocomplete="off"><button class="btn btn--ghost" type="submit">${esc(t('admin.clear', { table: tb }))}</button><small class="field-error" data-error-for="confirm" role="alert"></small><span class="form-error" role="alert"></span></form>`).join('')}</section>`;

    const seed = panel.querySelector<HTMLButtonElement>('[data-seed]');
    seed?.addEventListener('click', async () => {
      if (!confirm(t('admin.seedConfirm'))) return;
      seed.disabled = true;
      try {
        const { data } = await api('POST', '/admin/seed/cats');
        panel.querySelector<HTMLElement>('[data-seed-out]')!.innerHTML = note('ok', t('admin.seeded', { cats: data.cats }));
      } catch (e) {
        panel.insertAdjacentHTML('afterbegin', note('error', messageOf(e)));
      } finally {
        seed.disabled = false;
      }
    });

    const reference = panel.querySelector<HTMLButtonElement>('[data-reference]');
    reference?.addEventListener('click', async () => {
      reference.disabled = true;
      try {
        const { data } = await api('POST', '/admin/seed/reference');
        panel.querySelector<HTMLElement>('[data-reference-out]')!.innerHTML = note('ok', t('admin.referenceLoaded', { breeds: data.breeds, ems: data.ems }));
      } catch (e) {
        panel.insertAdjacentHTML('afterbegin', note('error', messageOf(e)));
      } finally {
        reference.disabled = false;
      }
    });

    panel.querySelectorAll<HTMLFormElement>('form[data-import]').forEach((form) => {
      const tb = form.closest<HTMLElement>('[data-table]')!.dataset.table!;
      onSubmit(form, async () => {
        const body = new FormData(form);
        const { data } = await upload(`/admin/backup/${tb}`, body);
        const summary = Object.entries(data.counts).map(([k, v]) => `${k} ${v}`).join(', ');
        form.querySelector('.import-out')!.innerHTML = note('ok', t('admin.imported', { summary }))
          + (data.warnings.length ? `<details><summary>${esc(t('admin.warnings'))} (${data.warnings.length})</summary><ul>${data.warnings.map((w: string) => `<li>${esc(w)}</li>`).join('')}</ul></details>` : '');
      });
    });
    panel.querySelectorAll<HTMLFormElement>('form[data-clear]').forEach((form) => {
      onSubmit(form, async (d) => {
        await api('DELETE', `/admin/backup/${form.dataset.clear}`, { confirm: d.confirm });
        form.reset();
        form.querySelector('.form-error')!.textContent = t('admin.cleared');
      });
    });
  }
}

const pages: Record<string, () => void | Promise<void>> = {
  cats: catsPage, ems: () => catalogPage('ems'), breeds: () => catalogPage('breeds'), cat: catPage, 'test-mating': matingPage, birthdays: birthdaysPage,
  login: loginPage, register: registerPage, 'forgot-password': forgotPage, 'reset-password': resetPage,
  account: accountPage, 'account-edit': accountEditPage, 'cat-edit': catEditPage, admin: adminPage,
};
pages[page]?.();
