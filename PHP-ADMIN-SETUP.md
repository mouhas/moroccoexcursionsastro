# PHP Admin Panel Setup

A custom admin dashboard at `admin-panel/` — your own login, your own
branding, hosted entirely on your GoDaddy/HostGator account. No Decap CMS,
no Netlify account. It edits:

- **Site Content** — every tour/page/car: title, price, itinerary, FAQs,
  reviews, icon grids, fleet listings, SEO overrides.
- **Settings** — PayPal mode/client ID, phone, WhatsApp, email, default SEO,
  the bookings/messages endpoint.
- **Bookings** / **Messages** — view and triage submissions (needs
  `worker/` deployed too — see `BOOKINGS-SETUP.md`, optional).
- **Media** — image uploads.

It has no database — every save is a commit straight to the site's GitHub
repo (via the GitHub API, using a token stored in a config file only this
server can read), which is what the existing GitHub Actions workflow
(`.github/workflows/deploy.yml`) is already watching to rebuild and
republish to GoDaddy. You never see GitHub — you just see your dashboard.

## 1. GitHub repo + token (same one used for auto-deploy)

If you haven't already:
1. Create an empty repo at [github.com/new](https://github.com/new) and
   push this project to it.
2. Create a fine-grained token at
   [github.com/settings/personal-access-tokens/new](https://github.com/settings/personal-access-tokens/new):
   repository access → only this repo; permissions → **Contents: Read and
   write**.

This is the same token/repo used for the GitHub Actions FTP deploy secrets —
you don't need two.

## 2. Configure the admin panel

```bash
cd admin-panel
cp config.example.php config.php     # config.php is gitignored, never committed
php generate-password-hash.php       # choose your admin password, copy the hash it prints
```

Edit `config.php`:
```php
define('ADMIN_EMAIL', 'you@example.com');
define('ADMIN_PASSWORD_HASH', '...paste the hash here...');
define('GITHUB_TOKEN', 'github_pat_...');
define('GITHUB_REPO', 'your-username/your-repo-name');
define('GITHUB_BRANCH', 'main');
define('SITE_URL', 'https://morocco-excursion.com');
```

## 3. Test locally

```bash
cd admin-panel
php -S localhost:8899
```

Open **http://localhost:8899/login.php**, log in with the email/password
you just set. Content/Settings/Bookings pages will show a "couldn't load
from GitHub" error until `config.php` has your real token and the repo
exists — that's expected, everything else (forms, navigation) works before
that.

## 4. Upload to GoDaddy/HostGator

Upload the **`admin-panel/` folder** to your hosting — e.g. as
`public_html/admin-panel/`, so it ends up at `yoursite.com/admin-panel/`.
Use cPanel's File Manager (upload a zip, then extract) or FTP/FileZilla with
the same credentials used for the GitHub Actions deploy secrets.

**Important:** this is a one-time manual upload, separate from the
automatic site deploy. The GitHub Actions workflow only uploads `dist/`
(the built Astro site) — it never touches `admin-panel/`, so your admin
panel won't be overwritten or deleted by future site deploys. If you change
the PHP code later, re-upload just that folder.

Rename the folder to something less guessable than `admin-panel` if you
want (e.g. `mx-panel`) — it works from any path, nothing is hardcoded.

## 5. Log in for real

`https://yoursite.com/admin-panel/login.php` (or whatever you renamed the
folder to) — same email/password from step 2.

## Security notes

- `config.php` holds your GitHub token — it's gitignored and only readable
  by your own server, never sent to the browser. Still, treat the token
  like a password: if you ever suspect it leaked, revoke it on GitHub
  (Settings → Developer settings → your token → Delete) and issue a new one.
- The token only has `Contents: Read and write` on this one repo — it can't
  touch your other repos or account settings.
- `.htaccess` files are included to block direct access to `lib/`,
  `vendor/`, and `config.example.php`. If your host doesn't honor
  `.htaccess` (rare on GoDaddy/HostGator cPanel, but possible on some
  Nginx-based plans), ask their support how to restrict access to those
  folders, or move `config.php` outside the web-servable directory
  entirely and adjust the `require` paths in `config.php`'s callers.
- Change your password any time by re-running
  `php generate-password-hash.php` and updating `config.php`.

## What about the Decap CMS setup (`public/admin`, `ADMIN-SETUP.md`)?

Still there, still works, but no longer needed — this PHP panel replaces
it. Safe to ignore or delete `public/admin/` if you want one less thing
around; it won't affect anything either way since nothing links to it.
