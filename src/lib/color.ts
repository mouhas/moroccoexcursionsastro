// Derives the same kind of 100–900 tint/shade ramp the theme's CSS already
// hand-picks (see src/styles/newtheme/overrides.css) from a single base hex
// color, so the admin panel only needs to expose one color picker per
// accent instead of nine.

function hexToHsl(hex: string): [number, number, number] {
  const m = hex.replace('#', '');
  const r = parseInt(m.substring(0, 2), 16) / 255;
  const g = parseInt(m.substring(2, 4), 16) / 255;
  const b = parseInt(m.substring(4, 6), 16) / 255;
  const max = Math.max(r, g, b), min = Math.min(r, g, b);
  let h = 0, s = 0;
  const l = (max + min) / 2;
  const d = max - min;
  if (d !== 0) {
    s = d / (1 - Math.abs(2 * l - 1));
    switch (max) {
      case r: h = ((g - b) / d) % 6; break;
      case g: h = (b - r) / d + 2; break;
      case b: h = (r - g) / d + 4; break;
    }
    h *= 60;
    if (h < 0) h += 360;
  }
  return [h, s * 100, l * 100];
}

function hslToHex(h: number, s: number, l: number): string {
  s /= 100; l /= 100;
  const c = (1 - Math.abs(2 * l - 1)) * s;
  const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
  const m = l - c / 2;
  let [r, g, b] = [0, 0, 0];
  if (h < 60) [r, g, b] = [c, x, 0];
  else if (h < 120) [r, g, b] = [x, c, 0];
  else if (h < 180) [r, g, b] = [0, c, x];
  else if (h < 240) [r, g, b] = [0, x, c];
  else if (h < 300) [r, g, b] = [x, 0, c];
  else [r, g, b] = [c, 0, x];
  const toHex = (v: number) => Math.round((v + m) * 255).toString(16).padStart(2, '0');
  return '#' + toHex(r) + toHex(g) + toHex(b);
}

// Lightness targets roughly matching the existing hand-picked orange ramp
// (100 ≈ near-white tint, 900 ≈ near-black shade), independent of the base
// color's own lightness so any picked color produces a usable full ramp.
const LIGHTNESS: Record<number, number> = {
  100: 95, 200: 89, 300: 79, 400: 68, 500: 58, 600: 48, 700: 38, 800: 28, 900: 19,
};

export function shadeRamp(baseHex: string): Record<number, string> {
  const [h, s] = hexToHsl(baseHex);
  const ramp: Record<number, string> = {};
  for (const [step, l] of Object.entries(LIGHTNESS)) {
    ramp[Number(step)] = hslToHex(h, s, l);
  }
  // 500 is "the" color everywhere it's used directly — keep it exactly what
  // was picked rather than a resampled approximation at a fixed lightness.
  ramp[500] = baseHex;
  return ramp;
}
