# Admin Panel & Deployment Setup

The site has an admin panel (Decap CMS) at `/admin` covering:
- **All tour/car/page content** — title, price, itinerary, FAQs, reviews,
  icon grids, fleet listings, per-page SEO title/description overrides.
- **Site Settings** — PayPal mode/client ID, phone, WhatsApp, email, default
  SEO description, and the bookings/messages endpoint (see
  `BOOKINGS-SETUP.md`).
- **Bookings** and **Messages** — every booking-widget/contact-form
  submission, once `worker/` is deployed (separate doc, optional — the site
  works fine without it, submissions just aren't logged anywhere but email).
- **Images** — drag-and-drop uploads to `public/uploads`.

Every save is a real commit to your content files. This doc gets you from
"works on my laptop" to "works at morocco-excursion.com/admin" on your
GoDaddy hosting.

## Right now, with zero setup (local editing)

This already works today:

```bash
npm run dev              # terminal 1 — the site
npx decap-server         # terminal 2 — local admin backend
```

Open **http://localhost:4321/admin/index.html** — no login needed locally.
Every field from the schema (price, duration, itinerary days, FAQs, reviews,
map link, etc.) is editable. Saving writes directly to the `.md`/`.yml`/
`.json` files in `src/content/`, the same files the site reads.

## Going live: 3 things you need to do

### 1. Create a GitHub repo and push this project

```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git push -u origin main
```

(The project is already a git repo with an initial commit — see `git log`.)

### 2. Turn on login for the real admin panel

Decap needs to authenticate editors. **Recommended: Netlify Identity** —
it's the option that gives you an actual email+password account (not "log
in with GitHub"), which is what most people mean by "admin login."

**Option A — Netlify Identity (recommended — real email+password login)**
Even though your *public site* stays on GoDaddy, you can deploy this same
GitHub repo to a free Netlify site *just* to host the `/admin` login:
1. netlify.com → "Add new site" → pick your GitHub repo (build command
   `npm run build`, publish dir `dist`).
2. Site settings → Identity → Enable Identity, then enable **Git Gateway**.
3. Site settings → Identity → Registration → set to **Invite only** (so
   random visitors can't sign themselves up as editors).
4. In `public/admin/config.yml`, change the `backend:` block to:
   ```yaml
   backend:
     name: git-gateway
     branch: main
   ```
5. Identity tab → Invite users → invite your own email. You'll get an email
   to set a password — that's your login.
6. Admin panel now lives at `https://your-netlify-site.netlify.app/admin`
   (or point a subdomain like `admin.morocco-excursion.com` at it).

**Option B — GitHub OAuth app (no Netlify, but login = "Sign in with GitHub")**
Needs a small OAuth proxy server (a few free ones exist, e.g. search
"decap-cms-oauth-provider" — most deploy free on Vercel/Cloudflare Workers
in one click). Once you have its URL:
1. GitHub → Settings → Developer settings → OAuth Apps → New OAuth App.
   Callback URL: `https://YOUR-PROXY-URL/callback`.
2. In `public/admin/config.yml`, fill in:
   ```yaml
   backend:
     name: github
     repo: YOUR_USERNAME/YOUR_REPO
     branch: main
     base_url: https://YOUR-PROXY-URL
   ```

### 3. Auto-deploy to GoDaddy on every save

`.github/workflows/deploy.yml` is already set up: on every push to `main`
(which is exactly what happens when someone saves in the CMS) it runs
`npm run build` and FTP-uploads `dist/` to your GoDaddy hosting.

Add these secrets in **GitHub repo → Settings → Secrets and variables →
Actions**:

| Secret | Value |
|---|---|
| `GODADDY_FTP_SERVER` | Your FTP host (cPanel → FTP Accounts) |
| `GODADDY_FTP_USERNAME` | Your FTP username |
| `GODADDY_FTP_PASSWORD` | Your FTP password |
| `GODADDY_FTP_SERVER_DIR` | Usually `/public_html/` |

Never put these in a file that gets committed — GitHub secrets are the
right place for them.

## The loop, once set up

1. You (or anyone you invite) log into `/admin`.
2. Edit a tour's price, add an FAQ, tweak the itinerary — click Publish.
3. Decap commits the change to GitHub.
4. GitHub Actions builds the site and uploads it to GoDaddy.
5. Live in ~1–2 minutes, no manual FTP, no code required.

## Payments note

The PayPal checkout on tour pages uses PayPal's public sandbox mode
(`client-id=sb`) by default — fully functional for testing, never charges
real money. This is now controlled from the admin panel: open **Site
Settings → General Settings** in `/admin`, and:
1. Paste your live Client ID (from developer.paypal.com → Apps &
   Credentials → your app → Live) into "PayPal Live Client ID".
2. Change "PayPal Mode" from Sandbox to Live.
3. Save — the next deploy uses your real PayPal account.

No code edit needed. Sandbox mode always uses PayPal's test id regardless of
what's saved in the Client ID field, so a half-filled-in live migration can
never accidentally go live with a broken id.

## Bookings, messages & other settings

See `BOOKINGS-SETUP.md` for wiring up the "Bookings" and "Messages"
collections in `/admin` (requires deploying a small free Cloudflare Worker —
optional, the site works fine without it).
