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

    // Car-specific
    carBadges: z.array(z.string()).default([]),
    carCategory: z.string().nullable().default(null),
    carFeatures: z.array(z.string()).default([]),
  }),
});

export const collections = { site };
