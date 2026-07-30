import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// https://astro.build
export default defineConfig({
  site: 'https://morocco-excursion.com',
  // Static output — fast, SEO-friendly, deployable anywhere.
  output: 'static',
  build: { format: 'directory' },
  integrations: [sitemap()],
});
