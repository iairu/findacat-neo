import { api, ApiError, query, upload } from './api';

type Dict = Record<string, string>;
type Json = any;

const app = document.getElementById('app')!;
const page = app.dataset.page!;
const locale = app.dataset.locale!;
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

function input(name: string, label: string, opts: { type?: string; value?: unknown; required?: boolean; hint?: string; autocomplete?: string } = {}) {
  const id = 'f-' + name;
  return `<div class="field"><label for="${id}">${esc(label)}</label>
    <input id="${id}" name="${name}" type="${opts.type ?? 'text'}" value="${esc(opts.value)}"${opts.required ? ' required' : ''}${opts.autocomplete ? ` autocomplete="${opts.autocomplete}"` : ''}>
    ${opts.hint ? `<small>${esc(opts.hint)}</small>` : ''}<small class="field-error" data-error-for="${name}" role="alert"></small></div>`;
}

function select(name: string, label: string, options: [string, string][], value = '') {
  return `<div class="field"><label for="f-${name}">${esc(label)}</label><select id="f-${name}" name="${name}">
    ${options.map(([v, l]) => `<option value="${esc(v)}"${v === value ? ' selected' : ''}>${esc(l)}</option>`).join('')}</select>
    <small class="field-error" data-error-for="${name}" role="alert"></small></div>`;
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
  const meta = [n.ems_code, fmtDate(n.born_on)].filter(Boolean).join(' · ');
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
          ? res.data.map((c: Json) => `<li><button type="button" data-id="${esc(c.id)}" data-name="${esc(c.name)}">${esc(catName(c))}<small>${esc([c.ems_code, fmtDate(c.born_on)].filter(Boolean).join(' · '))}</small></button></li>`).join('')
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

async function catsPage() {
  const sexOptions: [string, string][] = [['', t('common.any')], ['male', t('common.male')], ['female', t('common.female')]];
  app.innerHTML = `<form class="search-form" role="search">
      ${input('q', t('cats.name'), { value: params.get('q') })}
      ${select('sex', t('cat.fields.sex'), sexOptions, params.get('sex') ?? '')}
      ${input('ems_code', t('cat.fields.ems'), { value: params.get('ems_code') })}
      ${input('born', t('cats.born'), { value: params.get('born'), hint: 'YYYY, YYYY-MM, YYYY-MM-DD' })}
      ${input('reg_number', t('cats.reg'), { value: params.get('reg_number') })}
      <div class="field field--check"><label><input type="checkbox" name="exact" value="1"${params.get('exact') ? ' checked' : ''}> ${esc(t('cats.exact'))}</label></div>
      <div class="field field--actions"><button class="btn btn--primary" type="submit">${esc(t('common.search'))}</button></div>
      <p class="form-error" role="alert"></p>
    </form><div id="results" aria-live="polite"></div>`;
  const form = app.querySelector<HTMLFormElement>('form')!;
  const results = app.querySelector<HTMLElement>('#results')!;

  async function run(pageNo: number) {
    const f = Object.fromEntries(new FormData(form)) as Record<string, string>;
    results.innerHTML = `<p>${esc(t('common.loading'))}</p>`;
    history.replaceState(null, '', query({ ...f, page: pageNo > 1 ? pageNo : undefined }) || location.pathname);
    try {
      const res = await api('GET', '/cats' + query({ ...f, page: pageNo }));
      if (!res.data.length) { results.innerHTML = `<p class="empty">${esc(t('common.empty'))}</p>`; return; }
      results.innerHTML = `<p class="count">${esc(t('cats.count', { n: res.total }))}</p>
        <ul class="cat-list">${res.data.map((c: Json) => `<li><a href="${catUrl(c.id)}"><strong>${esc(catName(c))}</strong>
          <span>${esc([sexLabel(c.sex), c.ems_code, fmtDate(c.born_on)].filter(Boolean).join(' · '))}</span></a></li>`).join('')}</ul>
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
  if ([...params.keys()].some((k) => k !== 'page')) run(Number(params.get('page') ?? 1));
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
    const row = (label: string, value: unknown) => (value ? `<div><dt>${esc(label)}</dt><dd>${value}</dd></div>` : '');
    const parent = (p: Json | null, fallbackId: string | null) => (p ? `<a href="${catUrl(p.id)}">${esc(p.name)}</a>` : fallbackId ? esc(t('common.unknown')) : '');
    app.innerHTML = `
      ${c.moderation_status === 'pending' ? note('info', t('cat.pendingNote')) : ''}${c.moderation_status === 'rejected' ? note('error', t('cat.rejectedNote')) : ''}
      ${c.can_edit ? `<p><a class="btn btn--ghost" href="${href('/cat-edit/')}?id=${encodeURIComponent(c.id)}">${esc(t('common.edit'))}</a></p>` : ''}
      <div class="cat-grid">
        <dl class="facts">
          ${row(t('cat.fields.sex'), esc(sexLabel(c.sex)))}${row(t('cat.fields.born'), esc(fmtDate(c.born_on)))}${row(t('cat.fields.died'), esc(fmtDate(c.died_on)))}
          ${row(t('cat.fields.breed'), esc(c.breed_code))}${row(t('cat.fields.ems'), esc(c.ems_code))}${row(t('cat.fields.reg'), esc(c.reg_number))}
          ${row(t('cat.fields.sire'), parent(c.sire, c.sire_id))}${row(t('cat.fields.dam'), parent(c.dam, c.dam_id))}
          ${row(t('cat.fields.breeder'), esc(c.breeder))}${row(t('cat.fields.owner'), esc(c.owner_name))}${row(t('cat.fields.country'), esc(c.country))}
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
  app.innerHTML = `<p class="lead">${esc(t('mating.lead'))}</p>
    <form class="mating-form"><div id="sire-host" data-name="sire"></div><div id="dam-host" data-name="dam"></div>
      <div class="field field--actions"><button class="btn btn--primary" type="submit">${esc(t('mating.run'))}</button></div>
      <p class="form-error" role="alert"></p></form><div id="out" aria-live="polite"></div>`;
  const sire = picker(app.querySelector('#sire-host')!, t('mating.sire'), 'male');
  const dam = picker(app.querySelector('#dam-host')!, t('mating.dam'), 'female');
  const out = app.querySelector<HTMLElement>('#out')!;
  const form = app.querySelector<HTMLFormElement>('form')!;
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
  app.innerHTML = `<p class="lead">${esc(t('birthdays.lead'))}</p><div id="out" aria-live="polite"><p>${esc(t('common.loading'))}</p></div>`;
  const out = app.querySelector<HTMLElement>('#out')!;
  try {
    const { data } = await api('GET', '/birthdays');
    out.innerHTML = data.length
      ? `<ul class="cat-list">${data.map((b: Json) => `<li><a href="${catUrl(b.id)}"><strong>${esc(b.name)}</strong><span>${esc(fmtDate(b.birthday))} · ${esc(t('birthdays.turns', { n: b.turns }))}</span></a></li>`).join('')}</ul>`
      : `<p class="empty">${esc(t('common.empty'))}</p>`;
  } catch (e) { out.innerHTML = note('error', messageOf(e)); }
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

function registerPage() {
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
  app.innerHTML = `${params.get('verified') ? note('ok', t('auth.verifiedNow')) : ''}
    ${user.verified ? '' : `<div class="notice notice--info">${esc(t('auth.verifyNotice'))} <button class="btn btn--ghost" id="resend" type="button">${esc(t('auth.verifyResend'))}</button><span id="resend-out"></span></div>`}
    <p class="lead">${esc(user.name)} · ${esc(user.email)}</p>
    <form class="stack" id="profile" novalidate>${input('name', t('auth.name'), { value: user.name, required: true })}
      ${select('locale', t('auth.language'), [['en', 'English'], ['sk', 'Slovenčina']], user.locale)}
      <p class="form-error" role="alert"></p><div class="row"><button class="btn btn--primary" type="submit">${esc(t('common.save'))}</button><span id="saved"></span></div></form>
    <p class="row">${user.is_admin ? `<a class="btn btn--ghost" href="${href('/admin/')}">${esc(t('nav.admin'))}</a>` : ''}<button class="btn btn--ghost" id="logout" type="button">${esc(t('auth.logout'))}</button></p>
    <section id="mine"></section>`;
  app.querySelector('#resend')?.addEventListener('click', async () => {
    try { await api('POST', '/email/resend'); app.querySelector('#resend-out')!.textContent = ' ' + t('auth.verifySent'); } catch (e) { app.querySelector('#resend-out')!.textContent = ' ' + messageOf(e); }
  });
  app.querySelector('#logout')!.addEventListener('click', async () => {
    await api('POST', '/logout').catch(() => {});
    setHint(null);
    location.href = href('/');
  });
  onSubmit(app.querySelector('#profile')!, async (d) => {
    await api('PATCH', '/user', d);
    app.querySelector('#saved')!.textContent = t('account.saved');
  });
  const mine = app.querySelector<HTMLElement>('#mine')!;
  if (!user.verified) return;
  try {
    const { data } = await api('GET', '/my/cats');
    mine.innerHTML = `<div class="section-bar"><h2>${esc(t('account.myCats'))}</h2><a class="btn btn--primary" href="${href('/cat-edit/')}">${esc(t('account.addCat'))}</a></div>`
      + (data.length ? `<ul class="cat-list">${data.map((c: Json) => `<li><a href="${catUrl(c.id)}"><strong>${esc(catName(c))}</strong><span>${esc(statusLabel(c.moderation_status))}</span></a></li>`).join('')}</ul>` : `<p class="empty">${esc(t('account.noCats'))}</p>`);
  } catch (e) { mine.innerHTML = note('error', messageOf(e)); }
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
  const text = (name: string, label: string, type = 'text') => input(name, label, { value: cat[name], type });
  app.innerHTML = `<form class="stack cat-form" novalidate>
      ${input('name', t('cat.fields.name'), { value: cat.name, required: true })}
      ${select('sex', t('cat.fields.sex'), [['male', t('common.male')], ['female', t('common.female')]], cat.sex ?? 'female')}
      <div id="sire-host" data-name="sire_id"></div><div id="dam-host" data-name="dam_id"></div>
      ${text('born_on', t('cat.fields.born'), 'date')}${text('died_on', t('cat.fields.died'), 'date')}
      ${text('title_prefix', t('cat.fields.titlePrefix'))}${text('title_suffix', t('cat.fields.titleSuffix'))}
      ${text('breed_code', t('cat.fields.breed'))}${text('ems_code', t('cat.fields.ems'))}${text('reg_number', t('cat.fields.reg'))}
      ${text('breeder', t('cat.fields.breeder'))}${text('owner_name', t('cat.fields.owner'))}${text('country', t('cat.fields.country'))}
      ${text('chip_number', t('cat.fields.chip'))}${text('photo', t('cat.fields.photo'), 'url')}
      <div class="field"><label for="f-notes">${esc(t('cat.fields.notes'))}</label><textarea id="f-notes" name="notes" rows="4">${esc(cat.notes)}</textarea><small class="field-error" data-error-for="notes" role="alert"></small></div>
      <p class="form-error" role="alert"></p>
      <div class="row"><button class="btn btn--primary" type="submit">${esc(t('common.save'))}</button>
        ${id ? `<button class="btn btn--ghost" type="button" id="delete">${esc(t('common.delete'))}</button>` : ''}</div></form>`;
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
  app.innerHTML = `<div class="tabs" role="tablist">${Object.keys(tabs).map((k) => `<button class="btn btn--ghost" role="tab" data-tab="${k}">${esc(t('admin.tab.' + k))}</button>`).join('')}</div><div id="panel"></div>`;
  const panel = app.querySelector<HTMLElement>('#panel')!;
  const open = async (key: string) => {
    app.querySelectorAll<HTMLElement>('[data-tab]').forEach((b) => b.setAttribute('aria-selected', String(b.dataset.tab === key)));
    panel.innerHTML = '';
    try { await tabs[key](); } catch (e) { panel.innerHTML = note('error', messageOf(e)); }
  };
  app.querySelector('.tabs')!.addEventListener('click', (ev) => {
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
        <p><a class="btn btn--ghost" href="/api/admin/backup/${tb}" download>${esc(t('admin.export'))}</a></p>
        <form class="stack" data-import novalidate><div class="field"><label for="file-${tb}">${esc(t('admin.file'))}</label><input id="file-${tb}" name="file" type="file" accept=".csv,text/csv" required><small class="field-error" data-error-for="file" role="alert"></small></div>
          <p class="form-error" role="alert"></p><div class="import-out" aria-live="polite"></div><div><button class="btn btn--primary" type="submit">${esc(t('admin.import'))}</button></div></form></section>`).join('')
      + `<section class="danger"><h2>${esc(t('admin.danger'))}</h2><p>${esc(t('admin.clearLead'))}</p>
        ${TABLES.map((tb) => `<form class="row" data-clear="${tb}" novalidate><input name="confirm" aria-label="DELETE" placeholder="DELETE" autocomplete="off"><button class="btn btn--ghost" type="submit">${esc(t('admin.clear', { table: tb }))}</button><small class="field-error" data-error-for="confirm" role="alert"></small><span class="form-error" role="alert"></span></form>`).join('')}</section>`;

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
  cats: catsPage, cat: catPage, 'test-mating': matingPage, birthdays: birthdaysPage,
  login: loginPage, register: registerPage, 'forgot-password': forgotPage, 'reset-password': resetPage,
  account: accountPage, 'cat-edit': catEditPage, admin: adminPage,
};
pages[page]?.();
