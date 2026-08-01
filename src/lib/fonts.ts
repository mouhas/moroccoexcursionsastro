// Curated Google Fonts choices for the admin panel's Design settings — kept
// to a fixed list (not free text) so every choice has a known, correct
// Google Fonts URL and weight set. Picking a font that isn't loaded would
// otherwise silently fall back to the generic stack with no visible error.

export interface FontOption {
  label: string;
  family: string; // exact Google Fonts family name
  googleParam: string; // family=Name:wght@... segment for the CSS2 API
  fallback: string; // generic fallback stack
}

export const FONT_OPTIONS: Record<string, FontOption> = {
  Roboto: { label: 'Roboto', family: 'Roboto', googleParam: 'Roboto:ital,wght@0,400;0,500;0,600;0,700;0,900;1,400', fallback: 'system-ui, sans-serif' },
  'Open Sans': { label: 'Open Sans', family: 'Open Sans', googleParam: 'Open+Sans:wght@300;400;500;600;700;800', fallback: 'system-ui, sans-serif' },
  Montserrat: { label: 'Montserrat', family: 'Montserrat', googleParam: 'Montserrat:wght@400;500;600;700;800', fallback: 'system-ui, sans-serif' },
  Poppins: { label: 'Poppins', family: 'Poppins', googleParam: 'Poppins:wght@400;500;600;700', fallback: 'system-ui, sans-serif' },
  Lato: { label: 'Lato', family: 'Lato', googleParam: 'Lato:wght@400;700;900', fallback: 'system-ui, sans-serif' },
  Nunito: { label: 'Nunito', family: 'Nunito', googleParam: 'Nunito:wght@400;600;700;800', fallback: 'system-ui, sans-serif' },
  Raleway: { label: 'Raleway', family: 'Raleway', googleParam: 'Raleway:wght@400;500;600;700', fallback: 'system-ui, sans-serif' },
  Inter: { label: 'Inter', family: 'Inter', googleParam: 'Inter:wght@400;500;600;700', fallback: 'system-ui, sans-serif' },
  'Playfair Display': { label: 'Playfair Display', family: 'Playfair Display', googleParam: 'Playfair+Display:wght@400;600;700', fallback: 'Georgia, serif' },
  Merriweather: { label: 'Merriweather', family: 'Merriweather', googleParam: 'Merriweather:wght@400;700', fallback: 'Georgia, serif' },
};

export const DEFAULT_HEADING_FONT = 'Roboto';
export const DEFAULT_BODY_FONT = 'Open Sans';

export function fontOption(name: string): FontOption {
  return FONT_OPTIONS[name] || FONT_OPTIONS[DEFAULT_BODY_FONT];
}

// Builds the Google Fonts CSS2 stylesheet URL for whichever heading/body
// fonts are selected — deduped in case both are set to the same font.
export function googleFontsUrl(headingFont: string, bodyFont: string): string {
  const families = new Set([fontOption(headingFont).googleParam, fontOption(bodyFont).googleParam]);
  const params = [...families].map((f) => 'family=' + f).join('&');
  return `https://fonts.googleapis.com/css2?${params}&display=swap`;
}
