# Bookings & Messages Setup

Today, the booking widget on tour/car pages and the contact form both just
build a `mailto:` link — nothing is stored anywhere except your inbox. This
doc adds real storage: submissions land as JSON files in
`src/content/bookings/` or `src/content/messages/`, which show up as two
more collections in the same `/admin` panel you already use for content —
**no second login, no separate dashboard.**

This is entirely optional. Skip it and the site works exactly as it does
today (mailto: only). Do it and every submission is also logged where you
can see, sort, and mark it handled.

## Why a Cloudflare Worker

The public site is static (built by GitHub Actions, FTP'd to GoDaddy) — the
GoDaddy side can't run code to receive form submissions. Cloudflare Workers
has a generous free tier (100,000 requests/day) and needs no server to
maintain. The Worker's only job: take a form POST, and use a GitHub token to
commit a new JSON file to your repo. GitHub Actions is already configured
(`.github/workflows/deploy.yml`) to *not* rebuild the site for those commits
— they're invisible to the public site, only visible in `/admin`.

## Setup

### 1. Create a free Cloudflare account

cloudflare.com → sign up. No credit card needed for the Workers free tier.

### 2. Create a GitHub token scoped to just this repo

GitHub → Settings → Developer settings → **Fine-grained tokens** → Generate
new token:
- Repository access: **Only select repositories** → pick this repo.
- Permissions: **Contents: Read and write**. Nothing else.
- Copy the token — you won't see it again.

### 3. Deploy the Worker

```bash
cd worker
npm install
npx wrangler login          # opens a browser, log into Cloudflare
npx wrangler secret put GITHUB_TOKEN
# paste the token from step 2

npx wrangler secret put GITHUB_REPO
# e.g. your-username/your-repo-name

npx wrangler secret put ALLOWED_ORIGIN
# e.g. https://morocco-excursion.com
# (add more, comma-separated, if you also test from a Netlify preview URL etc.)

npx wrangler deploy
```

Wrangler prints the deployed URL, something like:
`https://morocco-excursions-submit.YOUR-SUBDOMAIN.workers.dev`

### 4. Point the site at it

In `/admin` → **Site Settings → General Settings** → "Bookings/Messages
Endpoint" → paste the URL from step 3 with `/submit` on the end:

```
https://morocco-excursions-submit.YOUR-SUBDOMAIN.workers.dev/submit
```

Save. Next deploy, the booking widget and contact form both start logging
submissions here (in addition to still opening the visitor's email client —
mailto: is never removed, this is additive).

## Using it

Open `/admin` → **Bookings** or **Messages**. Each entry has a `status`
field (`new` / `contacted` / `booked` / `archived`) you can update and save
like any other content — that's the whole "mark as handled" workflow.
Delete old ones from there too when you're done with them.

## Local testing

```bash
cd worker
npx wrangler dev            # runs the Worker locally
```

Set `ALLOWED_ORIGIN` to include `http://localhost:4321` (either as a second
value in the secret, comma-separated, or temporarily) and set the site's
"Bookings/Messages Endpoint" setting to the local Worker URL Wrangler prints
while testing.
