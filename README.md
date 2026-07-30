# Morocco Excursions — New Website (Astro)

A fast, SEO-friendly rebuild of morocco-excursion.com. All 216 content pages
(tours, cars, info pages) across 5 languages are migrated from the original site,
wrapped in a professional orange/blue travel-agency design.

## What's inside

- **216 content pages** + 5 localized homepages + 5 tour listing pages = **226 pages**
- Languages: English, Français, Español, Italiano, Português-BR
- Sections: Tours (145), Info pages (61), Rental cars (10)
- Design: responsive, mobile-friendly, line icons (no emoji), built-in graphics
  that load instantly (real photos drop in later)
- SEO: semantic HTML, per-page `<title>`/meta description, JSON-LD structured data
  (TouristTrip + AggregateRating) on tour/car pages

## Run it on your computer (localhost)

You need **Node.js 18+** installed. Get it from https://nodejs.org (LTS version).

Then, in a terminal:

```bash
cd morocco-excursion-site
npm install          # first time only — downloads dependencies
npm run dev          # starts the local server
```

Open the URL it prints, usually **http://localhost:4321**

To stop the server: press `Ctrl + C` in the terminal.

## Build the final static site (for hosting)

```bash
npm run build        # outputs the finished site into  dist/
npm run preview      # preview the built site locally
```

The `dist/` folder is a plain static website you can host anywhere
(Netlify, Vercel, Cloudflare Pages, or any web host).

## Project structure

```
src/
  pages/
    index.astro            English homepage
    [lang]/index.astro     Localized homepages (/fr/, /es/, /it/, /pt-br/)
    tours/index.astro      English tours listing
    [lang]/tours/index.astro   Localized tour listings
    [...path].astro        Renders every tour / car / info page at its original URL
  content/
    site/                  All 216 pages as markdown (title, price, duration in frontmatter)
    config.ts              Content schema
  components/              Header, Footer, TourCard, Icon, Thumb, Home
  layouts/Base.astro       Page shell (head, header, footer)
  styles/global.css        The orange/blue theme
  site.ts                  Navigation, languages, labels
```

## Editing content

Content lives as markdown files in `src/content/site/`. Each file's
frontmatter holds the title, price, duration, itinerary, FAQs, reviews, etc.;
the body is used for the free-form info pages. Edit a file, save, and the
dev server refreshes automatically.

## Admin panel

There's a full admin panel (Decap CMS) at `/admin` for editing every tour/page
field — no code required. It already works locally:

```bash
npm run dev          # terminal 1
npx decap-server      # terminal 2 — local CMS backend
```

Then open http://localhost:4321/admin/index.html. See **ADMIN-SETUP.md** for
how to turn this into a real login at yoursite.com/admin and auto-deploy to
GoDaddy on every save.

## Payments

Tour pages have a working PayPal checkout (10% deposit or pay-in-full) using
PayPal's sandbox mode — safe to test, no real charges. Swap in your live
PayPal Client ID when ready (see ADMIN-SETUP.md).

## Notes

- Placeholder graphics stand in for real photos. Swap them by adding images and
  replacing the `<Thumb />` component usage with real `<img>` tags.
- The homepage marketing copy is translated into all 5 languages; individual
  page bodies are in their original language from the source site.
- A few source pages (e.g. 12/16-day Tangier tours) were thin/JS-rendered on the
  old site and show "On request" pricing — fill those in once confirmed.
