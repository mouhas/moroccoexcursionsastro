# Admin Panel & Deployment Setup

The site now has an admin panel (Decap CMS) at `/admin` that edits every
tour/page field — title, price, itinerary, FAQs, reviews, everything — and
saves it as a real commit to your content files. This doc gets you from
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
map link, etc.) is editable. Saving writes directly to the `.md` files in
`src/content/site/`, the same files the site reads.

## Going live: 3 things you need to do

### 1. Create a GitHub repo and push this project

```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git push -u origin main
```

(The project is already a git repo with an initial commit — see `git log`.)

### 2. Turn on login for the real admin panel

Decap needs to authenticate editors against GitHub. Two ways to do that,
pick one:

**Option A — Netlify Identity (recommended, easiest, free)**
Even though your *public site* stays on GoDaddy, you can deploy this same
GitHub repo to a free Netlify site *just* to host the `/admin` login:
1. netlify.com → "Add new site" → pick your GitHub repo (build command
   `npm run build`, publish dir `dist`).
2. Site settings → Identity → Enable Identity, then enable **Git Gateway**.
3. In `public/admin/config.yml`, change the `backend:` block to:
   ```yaml
   backend:
     name: git-gateway
     branch: main
   ```
4. Invite yourself as an Identity user (Identity tab → Invite users).
5. Admin panel now lives at `https://your-netlify-site.netlify.app/admin`
   (or point a subdomain like `admin.morocco-excursion.com` at it).

**Option B — GitHub OAuth app (no Netlify at all)**
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

The PayPal checkout on tour pages currently uses PayPal's public sandbox
mode (`client-id=sb`) — fully functional for testing, never charges real
money. To accept real payments, replace `client-id=sb` in
`src/components/BookingWidget.astro` with your live Client ID from
developer.paypal.com (Apps & Credentials → your app → Live).
