// Shared site configuration: languages, navigation, contact.

import tourTranslationsData from './data/tour-translations.json';

export const LANGS = ['en', 'fr', 'es', 'it', 'pt-br'] as const;
export type Lang = (typeof LANGS)[number];

export const LANG_LABELS: Record<Lang, string> = {
  en: 'English',
  fr: 'Français',
  es: 'Español',
  it: 'Italiano',
  'pt-br': 'Português',
};

// Short trigger-button codes (the header's language switcher shows these
// instead of the full name — the dropdown panel still lists the full name
// from LANG_LABELS above).
export const LANG_CODES: Record<Lang, string> = {
  en: 'EN',
  fr: 'FR',
  es: 'ES',
  it: 'IT',
  'pt-br': 'PT',
};

export const CONTACT = {
  phone: '+212 673 55 5408',
  phoneHref: 'tel:+212673555408',
  whatsappHref: 'https://wa.me/212673555408',
  company: 'Morocco Excursions',
};

// Home path for a language
export function homeHref(lang: Lang): string {
  return lang === 'en' ? '/' : `/${lang}/`;
}

// Base prefix ('' for en, '/fr' for fr, etc.)
export function base(lang: Lang): string {
  return lang === 'en' ? '' : `/${lang}`;
}

// "pages" section entries that genuinely exist in more than one language,
// grouped by real topic (verified against src/content/site — most info
// pages besides these, like the day-trip hubs, are English-only and have
// no translated equivalent).
const PAGE_TRANSLATIONS: Partial<Record<Lang, string>>[] = [
  { en: 'reviews', fr: 'reviews', es: 'reviews', it: 'reviews', 'pt-br': 'reviews' },
  { en: 'travel-agency', fr: 'agence', es: 'agencia', it: 'agenzia', 'pt-br': 'agencia' },
  { en: 'morocco-rental-cars', es: 'alquiler-de-coches', fr: 'location-de-voitures', it: 'noleggio-auto', 'pt-br': 'aluguel-de-carro' },
  { en: 'morocco-desert-tours', fr: 'circuit-desert-maroc', es: 'viaje-desierto-marruecos', it: 'tour-deserto-marocco', 'pt-br': 'excursoes-deserto-marrocos' },
  { en: 'dmc-morocco', fr: 'dmc-maroc', es: 'dmc-marruecos', it: 'dmc-marocco', 'pt-br': 'dmc-marrocos' },
  { en: 'camel-trekking-morocco', fr: 'randonnee-dromadaires', es: 'excursion-camellos', it: 'tour-cammello-marocco', 'pt-br': 'excursoes-camelos' },
  { en: 'contact', fr: 'contact', es: 'contacto', it: 'contatto', 'pt-br': 'contacto' },
  { en: 'faqs', fr: 'faqs', es: 'faqs', it: 'faqs', 'pt-br': 'faqs' },
  { en: 'prices', fr: 'prix', es: 'precios', it: 'prezzi', 'pt-br': 'precos' },
  { en: 'sitemap', fr: 'sitemap', es: 'sitemap', it: 'sitemap', 'pt-br': 'sitemap' },
  { en: 'diversity-inclusion', es: 'diversidad-inclusion', fr: 'diversite-inclusion', it: 'diversita-inclusione', 'pt-br': 'diversidade-inclusao' },
  { en: 'collaboration', es: 'colaboracion', fr: 'collaboration', it: 'collaborazione', 'pt-br': 'colaboracao' },
  { en: 'eligibility', es: 'elegibilidad', fr: 'eligibilite', it: 'idoneita', 'pt-br': 'elegibilidade' },
  { en: 'christmas-new-years-eve-in-morocco', es: 'navidad-y-ano-nuevo-en-marruecos', fr: 'noel-et-nouvel-an-au-maroc', it: 'natale-e-capodanno-in-marocco', 'pt-br': 'natal-e-ano-novo-no-marrocos' },
  { en: 'code-of-conduct', es: 'codigo-de-conducta', fr: 'code-de-conduite', it: 'codice-di-condotta', 'pt-br': 'codigo-de-conduta' },
  { en: 'safety-risk-management', es: 'gestion-de-riesgos', fr: 'gestion-des-risques', it: 'gestione-dei-rischi', 'pt-br': 'gestao-de-riscos' },
  { en: 'morocco-students-tours', es: 'tours-estudiantes-marruecos', fr: 'circuits-etudiants-maroc', it: 'tour-studenti-marocco', 'pt-br': 'tours-estudantes-marrocos' },
  { en: 'semester-at-sea-morocco', es: 'semestre-en-el-mar-marruecos', fr: 'semestre-en-mer-maroc', it: 'semestre-in-mare-marocco', 'pt-br': 'semestre-no-mar-marrocos' },
  { en: 'morocco-2-day-trips', es: 'marruecos-2-dias', fr: 'maroc-2-jours', it: 'marocco-2-giorni', 'pt-br': 'marrocos-2-dias' },
  { en: 'morocco-3-day-trips', es: 'marruecos-3-dias', fr: 'maroc-3-jours', it: 'marocco-3-giorni', 'pt-br': 'marrocos-3-dias' },
  { en: 'morocco-4-day-trips', es: 'marruecos-4-dias', fr: 'maroc-4-jours', it: 'marocco-4-giorni', 'pt-br': 'marrocos-4-dias' },
  { en: 'morocco-5-day-trips', es: 'marruecos-5-dias', fr: 'maroc-5-jours', it: 'marocco-5-giorni', 'pt-br': 'marrocos-5-dias' },
  { en: 'morocco-6-day-trips', es: 'marruecos-6-dias', fr: 'maroc-6-jours', it: 'marocco-6-giorni', 'pt-br': 'marrocos-6-dias' },
  { en: 'morocco-7-day-trips', es: 'marruecos-7-dias', fr: 'maroc-7-jours', it: 'marocco-7-giorni', 'pt-br': 'marrocos-7-dias' },
  { en: 'morocco-8-day-trips', es: 'marruecos-8-dias', fr: 'maroc-8-jours', it: 'marocco-8-giorni', 'pt-br': 'marrocos-8-dias' },
  { en: 'morocco-9-day-trips', es: 'marruecos-9-dias', fr: 'maroc-9-jours', it: 'marocco-9-giorni', 'pt-br': 'marrocos-9-dias' },
  { en: 'morocco-10-day-trips', es: 'marruecos-10-dias', fr: 'maroc-10-jours', it: 'marocco-10-giorni', 'pt-br': 'marrocos-10-dias' },
  { en: 'morocco-11-day-trips', es: 'marruecos-11-dias', fr: 'maroc-11-jours', it: 'marocco-11-giorni', 'pt-br': 'marrocos-11-dias' },
  { en: 'morocco-12-day-trips', es: 'marruecos-12-dias', fr: 'maroc-12-jours', it: 'marocco-12-giorni', 'pt-br': 'marrocos-12-dias' },
  { en: 'morocco-13-day-trips', es: 'marruecos-13-dias', fr: 'maroc-13-jours', it: 'marocco-13-giorni', 'pt-br': 'marrocos-13-dias' },
  { en: 'morocco-14-day-trips', es: 'marruecos-14-dias', fr: 'maroc-14-jours', it: 'marocco-14-giorni', 'pt-br': 'marrocos-14-dias' },
  { en: 'morocco-15-day-trips', es: 'marruecos-15-dias', fr: 'maroc-15-jours', it: 'marocco-15-giorni', 'pt-br': 'marrocos-15-dias' },
  { en: 'morocco-16-day-trips', es: 'marruecos-16-dias', fr: 'maroc-16-jours', it: 'marocco-16-giorni', 'pt-br': 'marrocos-16-dias' },
  { en: 'morocco-17-day-trips', es: 'marruecos-17-dias', fr: 'maroc-17-jours', it: 'marocco-17-giorni', 'pt-br': 'marrocos-17-dias' },
  { en: 'morocco-18-day-trips', es: 'marruecos-18-dias', fr: 'maroc-18-jours', it: 'marocco-18-giorni', 'pt-br': 'marrocos-18-dias' },
  { en: 'morocco-19-day-trips', es: 'marruecos-19-dias', fr: 'maroc-19-jours', it: 'marocco-19-giorni', 'pt-br': 'marrocos-19-dias' },
];

// Cross-language tour slug groups, generated by matching real tours across
// languages on (day count + set of cities in the itinerary) — see
// scratchpad tour_translations.py. Only unambiguous matches are included;
// tours with no confident equivalent in a language simply fall back to
// that language's tours listing below.
const TOUR_TRANSLATIONS: Record<string, Partial<Record<Lang, string>>> = tourTranslationsData;
const TOUR_TRANSLATION_LOOKUP: Map<string, Partial<Record<Lang, string>>> = new Map();
for (const group of Object.values(TOUR_TRANSLATIONS)) {
  for (const [lang, slug] of Object.entries(group)) {
    TOUR_TRANSLATION_LOOKUP.set(`${lang}:${slug}`, group);
  }
}

// Only 10 cars exist, all fully translated into every language, so a small
// hardcoded table is simplest — mirrors admin-panel/lib/translations.php's
// car_translation_groups(), which MUST be kept in sync with this one.
const CAR_TRANSLATIONS: Partial<Record<Lang, string>>[] = [
  { en: 'suv', es: 'suv', fr: 'suv', it: 'suv', 'pt-br': 'suv' },
  { en: 'minivan', es: 'minivan', fr: 'minivan', it: 'minivan', 'pt-br': 'minivan' },
  { en: 'rental-bus', es: 'autobus-alquiler', fr: 'bus-location', it: 'bus-noleggio', 'pt-br': 'onibus-aluguel' },
  { en: 'bus-hire', es: 'alquiler-autobus', fr: 'location-bus', it: 'noleggio-bus', 'pt-br': 'aluguel-onibus' },
  { en: 'taxi', es: 'taxi', fr: 'taxi', it: 'taxi', 'pt-br': 'taxi' },
  { en: 'normal-car', es: 'coche-normal', fr: 'voiture-standard', it: 'auto-normale', 'pt-br': 'carro-normal' },
];
const CAR_TRANSLATION_LOOKUP: Map<string, Partial<Record<Lang, string>>> = new Map();
for (const group of CAR_TRANSLATIONS) {
  for (const [lang, slug] of Object.entries(group)) {
    CAR_TRANSLATION_LOOKUP.set(`${lang}:${slug}`, group);
  }
}

function translationGroupFor(current: { section: 'home' | 'pages' | 'tours' | 'cars'; pslug?: string; lang: Lang }): Partial<Record<Lang, string>> | undefined {
  if (!current.pslug) return undefined;
  if (current.section === 'pages') return PAGE_TRANSLATIONS.find((g) => g[current.lang] === current.pslug);
  if (current.section === 'tours') return TOUR_TRANSLATION_LOOKUP.get(`${current.lang}:${current.pslug}`);
  if (current.section === 'cars') return CAR_TRANSLATION_LOOKUP.get(`${current.lang}:${current.pslug}`);
  return undefined;
}

function pathPrefixFor(section: 'pages' | 'tours' | 'cars'): string {
  return section === 'tours' ? '/tours/' : section === 'cars' ? '/car/' : '/';
}

/**
 * Where the language switcher should send you for the *current* page.
 * - Homepage stays homepage.
 * - "pages"/"tours"/"cars" with a known translated equivalent go straight
 *   to it.
 * - Everything else (untranslated tours/info pages) falls back to that
 *   language's tours listing, which is far more useful than dumping the
 *   visitor on the homepage.
 */
export function langSwitchHref(
  targetLang: Lang,
  current: { section: 'home' | 'pages' | 'tours' | 'cars'; pslug?: string; lang: Lang }
): string {
  if (current.section === 'home') return homeHref(targetLang);
  const group = translationGroupFor(current);
  const targetSlug = group?.[targetLang];
  if (targetSlug) return base(targetLang) + pathPrefixFor(current.section as 'pages' | 'tours' | 'cars') + targetSlug;
  return base(targetLang) + '/tours';
}

/**
 * Precise cross-language alternates for <link rel="alternate" hreflang=...>
 * tags — unlike langSwitchHref (which always sends the visitor *somewhere*
 * useful), this returns null when a page has no confirmed equivalent in a
 * given language rather than guessing, since a wrong hreflang claim is worse
 * than none. Returns a map of every language that DOES have a real
 * equivalent, including the current one.
 */
export function langAlternates(
  current: { section: 'home' | 'pages' | 'tours' | 'cars'; pslug?: string; lang: Lang }
): Partial<Record<Lang, string>> | null {
  if (current.section === 'home') {
    const result: Partial<Record<Lang, string>> = {};
    for (const l of LANGS) result[l] = homeHref(l);
    return result;
  }
  const group = translationGroupFor(current);
  if (!group) return null;
  const prefix = pathPrefixFor(current.section as 'pages' | 'tours' | 'cars');
  const result: Partial<Record<Lang, string>> = {};
  for (const [l, slug] of Object.entries(group)) {
    result[l as Lang] = base(l as Lang) + prefix + slug;
  }
  return result;
}

// The car-rental hub page's real translated slug per language (matches the
// morocco-rental-cars group in PAGE_TRANSLATIONS) — shared by getNav() and
// the car-detail breadcrumb so the two can't silently drift apart the way
// they did before (breadcrumb was hardcoded to the English slug/label
// regardless of page language).
const CAR_RENTAL_SLUG: Record<Lang, string> = {
  en: 'morocco-rental-cars',
  fr: 'location-de-voitures',
  es: 'alquiler-de-coches',
  it: 'noleggio-auto',
  'pt-br': 'aluguel-de-carro',
};
export function carRentalHref(lang: Lang): string {
  return base(lang) + '/' + CAR_RENTAL_SLUG[lang];
}
export const CAR_RENTAL_LABEL: Record<Lang, string> = {
  en: 'Car Rental',
  fr: 'Location Voiture',
  es: 'Alquiler de Coches',
  it: 'Noleggio Auto',
  'pt-br': 'Aluguel de Carro',
};

// Primary navigation per language, using slugs that exist in the content.
export function getNav(lang: Lang): { label: string; href: string; icon: string }[] {
  const b = base(lang);
  const map: Record<Lang, { label: string; href: string; icon: string }[]> = {
    en: [
      { label: 'Tours', href: '/tours', icon: 'compass' },
      { label: 'Prices', href: '/prices', icon: 'tag' },
      { label: 'FAQs', href: '/faqs', icon: 'question' },
      { label: 'Reviews', href: '/reviews', icon: 'star' },
    ],
    fr: [
      { label: 'Circuits', href: '/fr/tours', icon: 'compass' },
      { label: 'Prix', href: '/fr/prix', icon: 'tag' },
      { label: 'FAQ', href: '/fr/faqs', icon: 'question' },
      { label: 'Avis', href: '/fr/reviews', icon: 'star' },
    ],
    es: [
      { label: 'Tours', href: '/es/tours', icon: 'compass' },
      { label: 'Precios', href: '/es/precios', icon: 'tag' },
      { label: 'FAQs', href: '/es/faqs', icon: 'question' },
      { label: 'Reseñas', href: '/es/reviews', icon: 'star' },
    ],
    it: [
      { label: 'Tour', href: '/it/tours', icon: 'compass' },
      { label: 'Prezzi', href: '/it/prezzi', icon: 'tag' },
      { label: 'FAQ', href: '/it/faqs', icon: 'question' },
      { label: 'Recensioni', href: '/it/reviews', icon: 'star' },
    ],
    'pt-br': [
      { label: 'Tours', href: '/pt-br/tours', icon: 'compass' },
      { label: 'Preços', href: '/pt-br/precos', icon: 'tag' },
      { label: 'FAQ', href: '/pt-br/faqs', icon: 'question' },
      { label: 'Avaliações', href: '/pt-br/reviews', icon: 'star' },
    ],
  };
  return map[lang] ?? map.en;
}

// Real testimonials from the company's homepage (used as a fallback on tours
// that don't have their own scraped review list — never invented).
export const GENERAL_REVIEWS: Record<Lang, { name: string; place: string; text: string }[]> = {
  en: [
    { name: 'Nora L.', place: 'United States', text: 'Our tour guide Ali was so kind, and I can’t say enough good things about him and his knowledge about Morocco.' },
    { name: 'Michael T.', place: 'United Kingdom', text: 'It was one of my most memorable vacations, and I would highly recommend this agency to everyone.' },
    { name: 'Olivier P.', place: 'Australia', text: 'Ali was gentle, patient, and flexible. Thank you Morocco Excursions for this amazing Morocco tour.' },
  ],
  fr: [
    { name: 'Josephine O.', place: 'Canada', text: 'Notre guide Ali était si gentil, et je ne peux pas dire assez de bien de lui et de ses connaissances sur le Maroc.' },
    { name: 'Gabriel Y.', place: 'France', text: 'Ce fut l’une de mes vacances les plus mémorables, et je recommanderais vivement cette agence à tout le monde.' },
    { name: 'Jules B.', place: 'France', text: 'Ali était doux, patient et flexible. Merci à Morocco Excursions pour cet incroyable voyage au Maroc.' },
  ],
  es: [
    { name: 'Fernanda P.', place: 'Argentina', text: 'Nuestro guía turístico, Ali, fue muy amable y no tengo palabras para describirlo y sus conocimientos sobre Marruecos.' },
    { name: 'Miguel O.', place: 'España', text: 'Fueron unas de mis vacaciones más memorables y recomendaría esta agencia a todo el mundo.' },
    { name: 'Oscar L.', place: 'Colombia', text: 'Ali fue amable, paciente y flexible. Gracias a Morocco Excursions por este increíble tour por Marruecos.' },
  ],
  it: [
    { name: 'Laura N.', place: '', text: 'La nostra guida turistica Ali è stata così gentile e non posso dire abbastanza cose positive su di lui e sulla sua conoscenza del Marocco.' },
    { name: 'Marco C.', place: '', text: 'È stata una delle mie vacanze più memorabili e consiglierei vivamente questa agenzia a tutti.' },
    { name: 'Giaco K.', place: '', text: 'Ali è stato gentile, paziente e flessibile. Grazie Morocco Excursions per questo fantastico tour in Marocco.' },
  ],
  'pt-br': [
    { name: 'Elena', place: 'Brasil', text: 'Nosso guia turístico Ali foi muito gentil, e não posso dizer coisas boas o suficiente sobre ele e seu conhecimento sobre o Marrocos.' },
    { name: 'Mauro J.', place: 'Portugal', text: 'Foi uma das minhas férias mais memoráveis, e eu recomendo fortemente esta agência a todos.' },
    { name: 'Bernardo K.', place: 'Brasil', text: 'Ali foi gentil, paciente e flexível. Obrigado Morocco Excursions por este tour incrível no Marrocos.' },
  ],
};

export const UI = {
  en: {
    book: 'Book a Trip', viewAll: 'View All Tours', from: 'from', bookNow: 'Book This Trip', planTrip: 'Plan a Custom Trip',
    readReviews: 'Read All Reviews', similar: 'You Might Also Like', allTours: 'All Tours & Excursions', perDay: '/ day',
    tabOverview: 'Overview', tabItinerary: 'Itinerary', tabIncluded: "What's Included", tabReviews: 'Reviews', tabFaq: 'FAQ',
    highlights: 'Highlights', pricing: 'Pricing', included: 'Included', excluded: 'Not Included', dayItinerary: 'Day-by-Day Itinerary',
    goodToKnow: 'Good to Know', customerReviews: 'Customer Reviews', frequentlyAsked: 'Frequently Asked Questions',
    checkAvailability: 'Check Availability', requestBooking: 'Request to Book', fullName: 'Full Name', email: 'Email',
    phone: 'Phone / WhatsApp', preferredDate: 'Preferred Date', travelers: 'Travelers', message: 'Message (optional)',
    sendEmail: 'Send Booking Request', chatWhatsapp: 'Chat on WhatsApp', support: 'Support', bookSuccess: "Thanks! We've opened your email app — send it and our team will reply within a few hours.",
    freeCancellation: 'Free Cancellation', private: 'Private Tour', reviewsLabel: 'reviews', notRated: 'Not yet rated',
    category: 'Category', features: 'Features', rentalRequest: 'Rental Request', pickupDate: 'Pick-up Date', returnDate: 'Return Date',
    messagePlaceholder: "Tell us your dates, group size, or anything else we should know.",
    tabMap: 'Map', tourMap: 'Tour Route Map', tourCode: 'Tour Code', duration: 'Duration', tourType: 'Tour Type',
    loadMap: 'Show interactive map', openInMaps: 'Open route in Google Maps ↗',
    vehicleType: 'Vehicle', chooseDuration: 'Duration', home: 'Home',
    filters: 'Filters', sortBy: 'Sort by', sortRecommended: 'Recommended', sortPriceLow: 'Price: Low to High',
    sortPriceHigh: 'Price: High to Low', sortDuration: 'Duration: Shortest First', priceRange: 'Price', tourTypeFilter: 'Tour Type',
    ratingFilter: 'Rating', ratingAny: 'Any rating', ratingUp: '& up', clearFilters: 'Clear all', toursFound: 'tours found',
    noToursMatch: 'No tours match these filters.', durAny: 'Any', dur1: '1 day', dur2to3: '2–3 days', dur4to7: '4–7 days', dur8plus: '8+ days',
    priceUnder100: 'Under €100', price100to300: '€100 – €300', price300to600: '€300 – €600', price600plus: '€600+', groupTour: 'Group Tour',
    trustLocal: '100% Local & Independent', trustLocalSub: 'Berber-owned since 2008', trustBespoke: 'Fully Customisable', trustBespokeSub: '1 to 30 days, private or group',
    trustReply: 'Fast Reply', trustReplySub: 'Usually within a few hours', trustSecure: 'Secure Booking', trustSecureSub: 'PayPal buyer protection',
    bookableNow: 'Book These Tours Now', downloadPdf: 'Download PDF Itinerary',
    noReviewsYet: "This tour doesn't have dedicated reviews yet.", generalReviewsNote: 'Here’s what other travellers say about Morocco Excursions:',
    prevTour: 'Previous Tour', nextTour: 'Next Tour', payDeposit: 'Pay 10% Deposit', payFull: 'Pay in Full',
    payWithPaypal: 'Pay securely with PayPal', paypalSandboxNote: 'Test mode — no real charge (PayPal sandbox)',
    orRequestInstead: 'Prefer to just ask a question first?', paymentSuccess: 'Payment received! We’ll email your booking confirmation shortly.',
    totalDue: 'Total due now', perPerson: 'per person',
    accommodation: 'Accommodation', accStandard: 'Standard', accSuperior: 'Superior',
    extras: 'Desert Extras', extraQuadSingle: 'Quad Bike/Person', extraQuadSingleNote: '€50 / person',
    extraQuadShared: 'Quad Bike/2 People', extraQuadSharedNote: '€75 / pair',
    extraBuggy: 'Buggy/2 People', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'For groups this size, contact us for a custom quote.',
    ratingHotels: 'Hotels', ratingGuides: 'Guides', ratingTransport: 'Transport', ratingActivities: 'Activities', overallRating: 'Overall Rating',
    knowBeforeYouGo: 'Know Before You Go', pickupTimeLabel: 'Pickup Time', nightsIncluded: 'Nights Included', optionalActivities: 'Optional Activities',
    nightsIncludedNote: 'upgradeable to Superior', optionalActivitiesNote: 'Quad biking, dune buggy available', languagesLabel: 'Languages',
    seoLocalAgencyDesc: 'Morocco Excursions, a 100% local Berber-owned travel agency.', seoTailorMadeDesc: 'Private, local, tailor-made.', onRequest: 'On request',
  },
  fr: {
    book: 'Réserver', viewAll: 'Voir tous les circuits', from: 'à partir de', bookNow: 'Réserver ce circuit', planTrip: 'Circuit sur mesure',
    readReviews: 'Tous les avis', similar: 'Vous aimerez aussi', allTours: 'Tous les circuits', perDay: '/ jour',
    tabOverview: 'Aperçu', tabItinerary: 'Itinéraire', tabIncluded: 'Inclus', tabReviews: 'Avis', tabFaq: 'FAQ',
    highlights: 'Points forts', pricing: 'Tarifs', included: 'Inclus', excluded: 'Non inclus', dayItinerary: 'Itinéraire jour par jour',
    goodToKnow: 'Bon à savoir', customerReviews: 'Avis clients', frequentlyAsked: 'Questions fréquentes',
    checkAvailability: 'Vérifier la disponibilité', requestBooking: 'Demander une réservation', fullName: 'Nom complet', email: 'E-mail',
    phone: 'Téléphone / WhatsApp', preferredDate: 'Date souhaitée', travelers: 'Voyageurs', message: 'Message (facultatif)',
    sendEmail: 'Envoyer la demande', chatWhatsapp: 'Discuter sur WhatsApp', support: 'Assistance', bookSuccess: 'Merci ! Votre application e-mail est ouverte — envoyez-la et notre équipe répondra sous quelques heures.',
    freeCancellation: 'Annulation gratuite', private: 'Circuit privé', reviewsLabel: 'avis', notRated: 'Pas encore noté',
    category: 'Catégorie', features: 'Caractéristiques', rentalRequest: 'Demande de location', pickupDate: 'Date de prise en charge', returnDate: 'Date de retour',
    messagePlaceholder: 'Indiquez vos dates, la taille du groupe ou toute autre information utile.',
    tabMap: 'Carte', tourMap: 'Carte de l’itinéraire', tourCode: 'Code circuit', duration: 'Durée', tourType: 'Type de circuit',
    loadMap: 'Afficher la carte interactive', openInMaps: 'Ouvrir l’itinéraire dans Google Maps ↗',
    vehicleType: 'Véhicule', chooseDuration: 'Durée', home: 'Accueil',
    filters: 'Filtres', sortBy: 'Trier par', sortRecommended: 'Recommandé', sortPriceLow: 'Prix croissant',
    sortPriceHigh: 'Prix décroissant', sortDuration: 'Durée croissante', priceRange: 'Prix', tourTypeFilter: 'Type de circuit',
    ratingFilter: 'Note', ratingAny: 'Toutes notes', ratingUp: 'et plus', clearFilters: 'Tout effacer', toursFound: 'circuits trouvés',
    noToursMatch: 'Aucun circuit ne correspond à ces filtres.', durAny: 'Toutes', dur1: '1 jour', dur2to3: '2–3 jours', dur4to7: '4–7 jours', dur8plus: '8+ jours',
    priceUnder100: 'Moins de 100 €', price100to300: '100 € – 300 €', price300to600: '300 € – 600 €', price600plus: '600 €+', groupTour: 'Circuit en Groupe',
    trustLocal: '100 % local & indépendant', trustLocalSub: 'Propriété berbère depuis 2008', trustBespoke: 'Entièrement personnalisable', trustBespokeSub: '1 à 30 jours, privé ou groupe',
    trustReply: 'Réponse rapide', trustReplySub: 'Généralement en quelques heures', trustSecure: 'Réservation sécurisée', trustSecureSub: 'Protection acheteur PayPal',
    bookableNow: 'Réservez ces circuits maintenant', downloadPdf: 'Télécharger l’itinéraire (PDF)',
    noReviewsYet: "Ce circuit n'a pas encore d'avis spécifiques.", generalReviewsNote: 'Voici ce que d’autres voyageurs disent de Morocco Excursions :',
    prevTour: 'Circuit précédent', nextTour: 'Circuit suivant', payDeposit: 'Payer 10 % d’acompte', payFull: 'Payer en totalité',
    payWithPaypal: 'Payer en toute sécurité avec PayPal', paypalSandboxNote: 'Mode test — aucun paiement réel (PayPal sandbox)',
    orRequestInstead: 'Vous préférez juste poser une question ?', paymentSuccess: 'Paiement reçu ! Vous recevrez votre confirmation par e-mail sous peu.',
    totalDue: 'Total à payer maintenant', perPerson: 'par personne',
    accommodation: 'Hébergement', accStandard: 'Standard', accSuperior: 'Supérieur',
    extras: 'Extras désert', extraQuadSingle: 'Quad/Personne', extraQuadSingleNote: '50 € / personne',
    extraQuadShared: 'Quad/2 Personnes', extraQuadSharedNote: '75 € / paire',
    extraBuggy: 'Buggy/2 Personnes', extraBuggyNote: '130 € / buggy',
    contactForQuote: 'Pour ce nombre de personnes, contactez-nous pour un devis sur mesure.',
    ratingHotels: 'Hôtels', ratingGuides: 'Guides', ratingTransport: 'Transport', ratingActivities: 'Activités', overallRating: 'Note Globale',
    knowBeforeYouGo: 'À Savoir Avant de Partir', pickupTimeLabel: 'Heure de Prise en Charge', nightsIncluded: 'Nuits Incluses', optionalActivities: 'Activités en Option',
    nightsIncludedNote: 'surclassable en Supérieur', optionalActivitiesNote: 'Quad, buggy des dunes disponibles', languagesLabel: 'Langues',
    seoLocalAgencyDesc: 'Morocco Excursions, une agence de voyage 100% locale et berbère.', seoTailorMadeDesc: 'Privé, local, sur mesure.', onRequest: 'Sur demande',
  },
  es: {
    book: 'Reservar', viewAll: 'Ver todos los tours', from: 'desde', bookNow: 'Reservar este tour', planTrip: 'Viaje personalizado',
    readReviews: 'Ver todas las reseñas', similar: 'También te puede gustar', allTours: 'Todos los tours', perDay: '/ día',
    tabOverview: 'Resumen', tabItinerary: 'Itinerario', tabIncluded: 'Incluye', tabReviews: 'Reseñas', tabFaq: 'FAQ',
    highlights: 'Lo más destacado', pricing: 'Precios', included: 'Incluido', excluded: 'No incluido', dayItinerary: 'Itinerario día a día',
    goodToKnow: 'Bueno saber', customerReviews: 'Opiniones de clientes', frequentlyAsked: 'Preguntas frecuentes',
    checkAvailability: 'Comprobar disponibilidad', requestBooking: 'Solicitar reserva', fullName: 'Nombre completo', email: 'Correo electrónico',
    phone: 'Teléfono / WhatsApp', preferredDate: 'Fecha preferida', travelers: 'Viajeros', message: 'Mensaje (opcional)',
    sendEmail: 'Enviar solicitud', chatWhatsapp: 'Chatear por WhatsApp', support: 'Soporte', bookSuccess: 'Gracias. Hemos abierto tu app de correo — envíalo y nuestro equipo responderá en unas horas.',
    freeCancellation: 'Cancelación gratuita', private: 'Tour privado', reviewsLabel: 'reseñas', notRated: 'Sin valorar',
    category: 'Categoría', features: 'Características', rentalRequest: 'Solicitud de alquiler', pickupDate: 'Fecha de recogida', returnDate: 'Fecha de devolución',
    messagePlaceholder: 'Cuéntanos tus fechas, el tamaño del grupo o cualquier otra cosa que debamos saber.',
    tabMap: 'Mapa', tourMap: 'Mapa de la ruta', tourCode: 'Código del tour', duration: 'Duración', tourType: 'Tipo de tour',
    loadMap: 'Mostrar mapa interactivo', openInMaps: 'Abrir la ruta en Google Maps ↗',
    vehicleType: 'Vehículo', chooseDuration: 'Duración', home: 'Inicio',
    filters: 'Filtros', sortBy: 'Ordenar por', sortRecommended: 'Recomendado', sortPriceLow: 'Precio: menor a mayor',
    sortPriceHigh: 'Precio: mayor a menor', sortDuration: 'Duración: menor a mayor', priceRange: 'Precio', tourTypeFilter: 'Tipo de tour',
    ratingFilter: 'Valoración', ratingAny: 'Cualquier valoración', ratingUp: 'o más', clearFilters: 'Borrar todo', toursFound: 'tours encontrados',
    noToursMatch: 'Ningún tour coincide con estos filtros.', durAny: 'Cualquiera', dur1: '1 día', dur2to3: '2–3 días', dur4to7: '4–7 días', dur8plus: '8+ días',
    priceUnder100: 'Menos de 100 €', price100to300: '100 € – 300 €', price300to600: '300 € – 600 €', price600plus: '600 €+', groupTour: 'Tour en Grupo',
    trustLocal: '100% Local e Independiente', trustLocalSub: 'Propiedad bereber desde 2008', trustBespoke: 'Totalmente Personalizable', trustBespokeSub: 'De 1 a 30 días, privado o grupo',
    trustReply: 'Respuesta Rápida', trustReplySub: 'Normalmente en pocas horas', trustSecure: 'Reserva Segura', trustSecureSub: 'Protección al comprador de PayPal',
    bookableNow: 'Reserva Estos Tours Ahora', downloadPdf: 'Descargar Itinerario en PDF',
    noReviewsYet: 'Este tour todavía no tiene reseñas propias.', generalReviewsNote: 'Esto es lo que dicen otros viajeros sobre Morocco Excursions:',
    prevTour: 'Tour anterior', nextTour: 'Siguiente tour', payDeposit: 'Pagar 10% de depósito', payFull: 'Pagar el total',
    payWithPaypal: 'Paga de forma segura con PayPal', paypalSandboxNote: 'Modo de prueba — sin cargo real (PayPal sandbox)',
    orRequestInstead: '¿Prefieres solo hacer una pregunta primero?', paymentSuccess: '¡Pago recibido! Te enviaremos la confirmación por correo en breve.',
    totalDue: 'Total a pagar ahora', perPerson: 'por persona',
    accommodation: 'Alojamiento', accStandard: 'Estándar', accSuperior: 'Superior',
    extras: 'Extras del desierto', extraQuadSingle: 'Quad/Persona', extraQuadSingleNote: '€50 / persona',
    extraQuadShared: 'Quad/2 Personas', extraQuadSharedNote: '€75 / pareja',
    extraBuggy: 'Buggy/2 Personas', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Para grupos de este tamaño, contáctanos para una cotización personalizada.',
    ratingHotels: 'Hoteles', ratingGuides: 'Guías', ratingTransport: 'Transporte', ratingActivities: 'Actividades', overallRating: 'Valoración General',
    knowBeforeYouGo: 'Antes de Reservar', pickupTimeLabel: 'Hora de Recogida', nightsIncluded: 'Noches Incluidas', optionalActivities: 'Actividades Opcionales',
    nightsIncludedNote: 'mejorable a Superior', optionalActivitiesNote: 'Quad, buggy de dunas disponibles', languagesLabel: 'Idiomas',
    seoLocalAgencyDesc: 'Morocco Excursions, una agencia de viajes 100% local y bereber.', seoTailorMadeDesc: 'Privado, local, a medida.', onRequest: 'Bajo petición',
  },
  it: {
    book: 'Prenota', viewAll: 'Vedi tutti i tour', from: 'da', bookNow: 'Prenota questo tour', planTrip: 'Viaggio su misura',
    readReviews: 'Tutte le recensioni', similar: 'Potrebbe piacerti anche', allTours: 'Tutti i tour', perDay: '/ giorno',
    tabOverview: 'Panoramica', tabItinerary: 'Itinerario', tabIncluded: 'Incluso', tabReviews: 'Recensioni', tabFaq: 'FAQ',
    highlights: 'Punti salienti', pricing: 'Prezzi', included: 'Incluso', excluded: 'Non incluso', dayItinerary: 'Itinerario giorno per giorno',
    goodToKnow: 'Da sapere', customerReviews: 'Recensioni dei clienti', frequentlyAsked: 'Domande frequenti',
    checkAvailability: 'Verifica disponibilità', requestBooking: 'Richiedi prenotazione', fullName: 'Nome completo', email: 'E-mail',
    phone: 'Telefono / WhatsApp', preferredDate: 'Data preferita', travelers: 'Viaggiatori', message: 'Messaggio (facoltativo)',
    sendEmail: 'Invia richiesta', chatWhatsapp: 'Chatta su WhatsApp', support: 'Assistenza', bookSuccess: 'Grazie! Abbiamo aperto la tua app email — inviala e il nostro team risponderà entro poche ore.',
    freeCancellation: 'Cancellazione gratuita', private: 'Tour privato', reviewsLabel: 'recensioni', notRated: 'Non ancora valutato',
    category: 'Categoria', features: 'Caratteristiche', rentalRequest: 'Richiesta di noleggio', pickupDate: 'Data di ritiro', returnDate: 'Data di riconsegna',
    messagePlaceholder: 'Indicaci le tue date, la dimensione del gruppo o altro che dovremmo sapere.',
    tabMap: 'Mappa', tourMap: 'Mappa del percorso', tourCode: 'Codice tour', duration: 'Durata', tourType: 'Tipo di tour',
    loadMap: 'Mostra mappa interattiva', openInMaps: 'Apri il percorso in Google Maps ↗',
    vehicleType: 'Veicolo', chooseDuration: 'Durata', home: 'Home',
    filters: 'Filtri', sortBy: 'Ordina per', sortRecommended: 'Consigliati', sortPriceLow: 'Prezzo: dal più basso',
    sortPriceHigh: 'Prezzo: dal più alto', sortDuration: 'Durata: crescente', priceRange: 'Prezzo', tourTypeFilter: 'Tipo di tour',
    ratingFilter: 'Valutazione', ratingAny: 'Qualsiasi valutazione', ratingUp: 'e oltre', clearFilters: 'Cancella tutto', toursFound: 'tour trovati',
    noToursMatch: 'Nessun tour corrisponde a questi filtri.', durAny: 'Qualsiasi', dur1: '1 giorno', dur2to3: '2–3 giorni', dur4to7: '4–7 giorni', dur8plus: '8+ giorni',
    priceUnder100: 'Meno di 100 €', price100to300: '100 € – 300 €', price300to600: '300 € – 600 €', price600plus: '600 €+', groupTour: 'Tour di Gruppo',
    trustLocal: '100% Locale & Indipendente', trustLocalSub: 'Di proprietà berbera dal 2008', trustBespoke: 'Completamente Personalizzabile', trustBespokeSub: 'Da 1 a 30 giorni, privato o gruppo',
    trustReply: 'Risposta Rapida', trustReplySub: 'Di solito entro poche ore', trustSecure: 'Prenotazione Sicura', trustSecureSub: 'Protezione acquirenti PayPal',
    bookableNow: 'Prenota Questi Tour Ora', downloadPdf: 'Scarica Itinerario in PDF',
    noReviewsYet: 'Questo tour non ha ancora recensioni proprie.', generalReviewsNote: 'Ecco cosa dicono altri viaggiatori di Morocco Excursions:',
    prevTour: 'Tour precedente', nextTour: 'Tour successivo', payDeposit: 'Paga il 10% di acconto', payFull: 'Paga per intero',
    payWithPaypal: 'Paga in sicurezza con PayPal', paypalSandboxNote: 'Modalità test — nessun addebito reale (PayPal sandbox)',
    orRequestInstead: 'Preferisci prima solo fare una domanda?', paymentSuccess: 'Pagamento ricevuto! Riceverai la conferma via email a breve.',
    totalDue: 'Totale da pagare ora', perPerson: 'a persona',
    accommodation: 'Alloggio', accStandard: 'Standard', accSuperior: 'Superiore',
    extras: 'Extra deserto', extraQuadSingle: 'Quad/Persona', extraQuadSingleNote: '€50 / persona',
    extraQuadShared: 'Quad/2 Persone', extraQuadSharedNote: '€75 / coppia',
    extraBuggy: 'Buggy/2 Persone', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Per gruppi di queste dimensioni, contattaci per un preventivo personalizzato.',
    ratingHotels: 'Hotel', ratingGuides: 'Guide', ratingTransport: 'Trasporto', ratingActivities: 'Attività', overallRating: 'Valutazione Complessiva',
    knowBeforeYouGo: 'Da Sapere Prima Di Partire', pickupTimeLabel: 'Orario di Ritiro', nightsIncluded: 'Notti Incluse', optionalActivities: 'Attività Opzionali',
    nightsIncludedNote: 'aggiornabile a Superiore', optionalActivitiesNote: 'Quad, buggy delle dune disponibili', languagesLabel: 'Lingue',
    seoLocalAgencyDesc: 'Morocco Excursions, un\'agenzia di viaggi berbera 100% locale.', seoTailorMadeDesc: 'Privato, locale, su misura.', onRequest: 'Su richiesta',
  },
  'pt-br': {
    book: 'Reservar', viewAll: 'Ver todos os tours', from: 'a partir de', bookNow: 'Reservar este tour', planTrip: 'Viagem personalizada',
    readReviews: 'Ver todas as avaliações', similar: 'Você também pode gostar', allTours: 'Todos os tours', perDay: '/ dia',
    tabOverview: 'Visão Geral', tabItinerary: 'Roteiro', tabIncluded: 'O que está incluso', tabReviews: 'Avaliações', tabFaq: 'FAQ',
    highlights: 'Destaques', pricing: 'Preços', included: 'Incluso', excluded: 'Não incluso', dayItinerary: 'Roteiro dia a dia',
    goodToKnow: 'Bom saber', customerReviews: 'Avaliações de clientes', frequentlyAsked: 'Perguntas frequentes',
    checkAvailability: 'Verificar disponibilidade', requestBooking: 'Solicitar reserva', fullName: 'Nome completo', email: 'E-mail',
    phone: 'Telefone / WhatsApp', preferredDate: 'Data preferida', travelers: 'Viajantes', message: 'Mensagem (opcional)',
    sendEmail: 'Enviar pedido de reserva', chatWhatsapp: 'Conversar no WhatsApp', support: 'Suporte', bookSuccess: 'Obrigado! Abrimos seu app de e-mail — envie e nossa equipe responderá em poucas horas.',
    freeCancellation: 'Cancelamento grátis', private: 'Tour privado', reviewsLabel: 'avaliações', notRated: 'Ainda sem avaliação',
    category: 'Categoria', features: 'Recursos', rentalRequest: 'Pedido de aluguel', pickupDate: 'Data de retirada', returnDate: 'Data de devolução',
    messagePlaceholder: 'Conte-nos suas datas, tamanho do grupo ou qualquer outra coisa que devamos saber.',
    tabMap: 'Mapa', tourMap: 'Mapa da rota', tourCode: 'Código do tour', duration: 'Duração', tourType: 'Tipo de tour',
    loadMap: 'Mostrar mapa interativo', openInMaps: 'Abrir rota no Google Maps ↗',
    vehicleType: 'Veículo', chooseDuration: 'Duração', home: 'Início',
    filters: 'Filtros', sortBy: 'Ordenar por', sortRecommended: 'Recomendado', sortPriceLow: 'Preço: menor para maior',
    sortPriceHigh: 'Preço: maior para menor', sortDuration: 'Duração: menor primeiro', priceRange: 'Preço', tourTypeFilter: 'Tipo de tour',
    ratingFilter: 'Avaliação', ratingAny: 'Qualquer avaliação', ratingUp: 'ou mais', clearFilters: 'Limpar tudo', toursFound: 'tours encontrados',
    noToursMatch: 'Nenhum tour corresponde a estes filtros.', durAny: 'Qualquer', dur1: '1 dia', dur2to3: '2–3 dias', dur4to7: '4–7 dias', dur8plus: '8+ dias',
    priceUnder100: 'Menos de 100 €', price100to300: '100 € – 300 €', price300to600: '300 € – 600 €', price600plus: '600 €+', groupTour: 'Tour em Grupo',
    trustLocal: '100% Local e Independente', trustLocalSub: 'Propriedade berbere desde 2008', trustBespoke: 'Totalmente Personalizável', trustBespokeSub: 'De 1 a 30 dias, privado ou em grupo',
    trustReply: 'Resposta Rápida', trustReplySub: 'Normalmente em poucas horas', trustSecure: 'Reserva Segura', trustSecureSub: 'Proteção ao comprador PayPal',
    bookableNow: 'Reserve Estes Tours Agora', downloadPdf: 'Baixar Roteiro em PDF',
    noReviewsYet: 'Este tour ainda não tem avaliações próprias.', generalReviewsNote: 'Veja o que outros viajantes dizem sobre a Morocco Excursions:',
    prevTour: 'Tour anterior', nextTour: 'Próximo tour', payDeposit: 'Pagar 10% de sinal', payFull: 'Pagar o valor total',
    payWithPaypal: 'Pague com segurança via PayPal', paypalSandboxNote: 'Modo de teste — sem cobrança real (PayPal sandbox)',
    orRequestInstead: 'Prefere apenas fazer uma pergunta primeiro?', paymentSuccess: 'Pagamento recebido! Enviaremos a confirmação por e-mail em breve.',
    totalDue: 'Total a pagar agora', perPerson: 'por pessoa',
    accommodation: 'Acomodação', accStandard: 'Padrão', accSuperior: 'Superior',
    extras: 'Extras do deserto', extraQuadSingle: 'Quadriciclo/Pessoa', extraQuadSingleNote: '€50 / pessoa',
    extraQuadShared: 'Quadriciclo/2 Pessoas', extraQuadSharedNote: '€75 / dupla',
    extraBuggy: 'Buggy/2 Pessoas', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Para grupos deste tamanho, entre em contato para um orçamento personalizado.',
    ratingHotels: 'Hotéis', ratingGuides: 'Guias', ratingTransport: 'Transporte', ratingActivities: 'Atividades', overallRating: 'Avaliação Geral',
    knowBeforeYouGo: 'Saiba Antes de Reservar', pickupTimeLabel: 'Horário de Retirada', nightsIncluded: 'Noites Incluídas', optionalActivities: 'Atividades Opcionais',
    nightsIncludedNote: 'melhorável para Superior', optionalActivitiesNote: 'Quadriciclo, buggy nas dunas disponíveis', languagesLabel: 'Idiomas',
    seoLocalAgencyDesc: 'Morocco Excursions, uma agência de viagens berbere 100% local.', seoTailorMadeDesc: 'Privado, local, sob medida.', onRequest: 'Sob consulta',
  },
} as const;
