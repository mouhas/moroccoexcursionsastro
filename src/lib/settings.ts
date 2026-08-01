// Reads the single-entry `settings` collection (src/content/settings/general.yml),
// edited from /admin. Falls back to these defaults if the entry is ever
// missing, so a broken/missing settings file can't break the build.
import { getEntry } from 'astro:content';

export interface SiteSettings {
  paypalClientId: string;
  paypalMode: 'sandbox' | 'live';
  phone: string;
  phoneHref: string;
  whatsappHref: string;
  email: string;
  defaultMetaDescription: string;
  submitEndpoint: string;
  accentColor: string;
  accentColor2: string;
  headingFont: string;
  bodyFont: string;
  baseFontSize: number;
}

const DEFAULTS: SiteSettings = {
  paypalClientId: 'sb',
  paypalMode: 'sandbox',
  phone: '+212 673 55 5408',
  phoneHref: 'tel:+212673555408',
  whatsappHref: 'https://wa.me/212673555408',
  email: 'MoroccoExcursions@Gmail.com',
  defaultMetaDescription: 'Private Morocco desert tours, camel treks in Merzouga and tailor-made journeys by a 100% local Berber team. Rated 5/5 from 820+ travellers.',
  submitEndpoint: '',
  accentColor: '#ef7c45',
  accentColor2: '#2f83b5',
  headingFont: 'Roboto',
  bodyFont: 'Open Sans',
  baseFontSize: 16,
};

export async function getSettings(): Promise<SiteSettings> {
  const entry = await getEntry('settings', 'general');
  if (!entry) return DEFAULTS;
  return {
    ...DEFAULTS,
    ...entry.data,
    defaultMetaDescription: entry.data.defaultMetaDescription ?? DEFAULTS.defaultMetaDescription,
    submitEndpoint: entry.data.submitEndpoint ?? DEFAULTS.submitEndpoint,
  };
}

// The actual PayPal SDK client id to load: sandbox mode always uses the
// public "sb" test id no matter what's saved, so a half-finished live
// migration can never accidentally leave a broken/placeholder client id
// live on the site.
export function paypalClientIdFor(settings: SiteSettings): string {
  return settings.paypalMode === 'live' ? settings.paypalClientId : 'sb';
}
