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
  }),
});

export const collections = { site };
