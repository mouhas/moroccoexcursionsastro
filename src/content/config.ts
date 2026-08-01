import { defineCollection, z } from 'astro:content';

const priceRow = z.array(z.string());
const itineraryDay = z.object({ title: z.string(), html: z.string() });
const faq = z.object({ q: z.string(), aHtml: z.string() });
const review = z.object({
  name: z.string(),
  date: z.string(),
  likes: z.number().default(0),
  stars: z.number().default(5),
  title: z.string().default(''),
  text: z.string(),
});
const priceTier = z.object({ people: z.number(), perPerson: z.number() });
const ratingBreakdown = z.object({
  hotels: z.number(),
  guides: z.number(),
  transport: z.number(),
  activities: z.number(),
});
const autoPricing = z.object({
  days: z.number(),
  nights: z.number(),
  hotelPerNight: z.number().default(40),
  superiorSurcharge: z.number().default(50),
  transportPerDay: z.number().default(300),
});
// 'group' tours where accommodation itself has a Standard/Luxury per-person choice
// (desert camp overnights), instead of a single flat price.
const campTier = z.object({ label: z.string(), perPerson: z.number() });
// 'activity' tours priced by vehicle option × duration (quad biking, dune buggy),
// where the listed price is a flat rate for that vehicle/duration (not per traveler).
const activityOption = z.object({
  label: z.string(),
  prices: z.array(z.object({ durationLabel: z.string(), price: z.number() })),
});

const site = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string(),
    lang: z.enum(['en', 'fr', 'es', 'it', 'pt-br']),
    section: z.enum(['pages', 'tours', 'cars']),
    pslug: z.string(),
    urlPath: z.string(), // exact path, '' for the English home page
    price: z.number().nullable().default(null),
    duration: z.string().nullable().default(null),
    tag: z.string().nullable().default(null),
    source: z.string().optional(),

    // Structured tour/car data scraped from the live site (Traveler WP theme).
    rating: z.number().nullable().default(null),
    reviewCount: z.number().nullable().default(null),
    ratingBreakdown: ratingBreakdown.nullable().default(null),
    tourType: z.string().nullable().default(null),
    cancellation: z.string().nullable().default(null),
    languagesSpoken: z.string().nullable().default(null),
    overviewHtml: z.string().default(''),
    priceHeading: z.string().nullable().default(null),
    priceRows: z.array(priceRow).default([]),
    notesHeading: z.string().nullable().default(null),
    notes: z.array(z.string()).default([]),
    highlights: z.array(z.string()).default([]),
    itinerary: z.array(itineraryDay).default([]),
    included: z.array(z.string()).default([]),
    excluded: z.array(z.string()).default([]),
    faqs: z.array(faq).default([]),
    reviews: z.array(review).default([]),
    mapUrl: z.string().nullable().default(null),
    tourCode: z.string().nullable().default(null),

    // Pricing engine: 'group' = flat per-person price × travelers (optionally with
    // campTiers offering a Standard/Luxury per-person choice), 'tiers' = manual
    // price-per-group-size table, 'auto' = formula
    // (hotel/night × people × nights + transport/day) ÷ people, 'activity' = flat
    // rate chosen from vehicle option × duration (quad biking, dune buggy).
    pricingMode: z.enum(['group', 'tiers', 'auto', 'activity']).default('group'),
    priceTiers: z.array(priceTier).default([]),
    autoPricing: autoPricing.nullable().default(null),
    campTiers: z.array(campTier).default([]),
    activityOptions: z.array(activityOption).default([]),
    hasDesertExtras: z.boolean().default(false),

    // Car-specific
    carBadges: z.array(z.string()).default([]),
    carCategory: z.string().nullable().default(null),
    carFeatures: z.array(z.string()).default([]),

    // Real, already-authored structured content that was previously silently
    // dropped because it wasn't declared here — see en__pages__travel-agency.md
    // (iconGrid) and en__pages__morocco-rental-cars.md (carListings).
    iconGrid: z.array(z.object({ icon: z.string(), title: z.string(), text: z.string() })).default([]),
    carListings: z.array(z.object({
      icon: z.string(), title: z.string(), text: z.string(),
      specs: z.array(z.string()).default([]),
      price: z.string().nullable().default(null),
      href: z.string().nullable().default(null),
      bookHref: z.string().nullable().default(null),
    })).default([]),

    // Optional per-page SEO overrides, editable from the admin panel — when
    // unset, [...path].astro falls back to its own auto-generated title/
    // description (see metaDescriptionFor()).
    metaTitle: z.string().nullable().default(null),
    metaDescription: z.string().nullable().default(null),
  }),
});

// Single-entry site-wide settings (PayPal, contact details) — edited as one
// file from the admin panel instead of being hardcoded across components.
const settings = defineCollection({
  type: 'data',
  schema: z.object({
    paypalClientId: z.string().default('sb'),
    paypalMode: z.enum(['sandbox', 'live']).default('sandbox'),
    phone: z.string().default('+212 673 55 5408'),
    phoneHref: z.string().default('tel:+212673555408'),
    whatsappHref: z.string().default('https://wa.me/212673555408'),
    email: z.string().default('MoroccoExcursions@Gmail.com'),
    defaultMetaDescription: z.string().optional(),
    // The deployed Cloudflare Worker's URL (see worker/), e.g.
    // "https://morocco-excursions-submit.YOUR-SUBDOMAIN.workers.dev/submit".
    // Left blank, bookings/messages just aren't logged — mailto: still works.
    submitEndpoint: z.string().optional(),
    // Core theme — read by Base.astro to override the hand-picked defaults
    // in src/styles/newtheme/overrides.css. accentColor/accentColor2 are
    // expanded into a full 100-900 tint/shade ramp at build time (see
    // src/lib/color.ts) so one color picker each is enough. headingFont/
    // bodyFont must be one of src/lib/fonts.ts's curated Google Fonts
    // options, not free text — anything else silently falls back there.
    accentColor: z.string().default('#ef7c45'),
    accentColor2: z.string().default('#2f83b5'),
    headingFont: z.string().default('Roboto'),
    bodyFont: z.string().default('Open Sans'),
    baseFontSize: z.number().default(16),
  }),
});

// Bookings/messages — written by the Cloudflare Worker (see worker/) when a
// visitor submits the booking widget or contact form, one JSON file per
// submission. Not rendered as public pages; browsed and triaged from the
// admin panel (status field is editable there).
const submission = z.object({
  name: z.string(),
  email: z.string(),
  phone: z.string().optional(),
  subject: z.string().optional(),
  message: z.string().optional(),
  submittedAt: z.string(),
  status: z.enum(['new', 'contacted', 'booked', 'archived']).default('new'),
  raw: z.string().optional(),
});
const bookings = defineCollection({ type: 'data', schema: submission });
const messages = defineCollection({ type: 'data', schema: submission });

export const collections = { site, settings, bookings, messages };
