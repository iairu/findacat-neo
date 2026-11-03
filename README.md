# Find A Cat

Cat pedigree database with inbreeding (COI) calculations for pedigrees and test matings. A static Astro site (`web/`: homepage, docs, portal) and a Laravel JSON API (`api/`).

## Local development

From the repository root:

```bash
npm run setup   # API dependencies, api/.env, APP_KEY, SQLite database, web dependencies
npm run dev     # Laravel on :8000 + Astro on :4321
```

`npm run dev` starts both servers; Astro proxies `/api` to `localhost:8000`, so the portal, sign-in and CSRF cookies work exactly as they do in production. PHP and Composer are used when installed, otherwise the commands run through the `composer:2` Docker image. Run only one side with `npm run dev:api` or `npm run dev:web`.

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Fiairu%2Ffindacat-neo&project-name=findacat&repository-name=findacat&env=APP_NAME%2CDISABLE_SIGNUPS%2CMAIL_MAILER&envDefaults=%7B%22APP_NAME%22%3A%22Find%20A%20Cat%22%2C%22DISABLE_SIGNUPS%22%3A%22false%22%2C%22MAIL_MAILER%22%3A%22log%22%7D&envDescription=The%20defaults%20work%20as%20they%20are%3B%20APP_KEY%20and%20the%20setup%20token%20are%20generated%20by%20the%20build.%20For%20real%20email%20set%20MAIL_MAILER%20to%20smtp%20later%20and%20add%20MAIL_HOST%2C%20MAIL_PORT%2C%20MAIL_USERNAME%2C%20MAIL_PASSWORD%20and%20MAIL_FROM_ADDRESS.&envLink=https%3A%2F%2Fgithub.com%2Fiairu%2Ffindacat-neo%2Fblob%2Fmain%2Fweb%2Fsrc%2Fcontent%2Fdocs%2Fen%2Fadmin%2Fvercel.mdx%23environment-variables&products=%5B%7B%22type%22%3A%22integration%22%2C%22integrationSlug%22%3A%22neon%22%2C%22productSlug%22%3A%22neon%22%2C%22protocol%22%3A%22storage%22%7D%5D)

The button copies the repository to your account, asks for a few settings with working defaults already filled in, creates a Neon Postgres database (preselected) and deploys. The build runs the migrations, generates `APP_KEY` and prints a setup link in the build log, where you create the first administrator. See the [Vercel guide](web/src/content/docs/en/admin/vercel.mdx) for mail settings and custom domains, and [installing on shared hosting](web/src/content/docs/en/admin/index.mdx) for FTP/cPanel.
