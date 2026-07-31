import { existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import tourImages from '../data/tour-images.json';

export interface GalleryImage {
  file: string; // relative to public/images/, e.g. 'tours/marrakech-1.webp'
  alt: string;
  src: string; // '/images/<file>'
  exists: boolean;
}

const PUBLIC_IMAGES_DIR = fileURLToPath(new URL('../../public/images/', import.meta.url));

const manifest: Record<string, { file: string; alt: string }[]> = tourImages;

// Real photos are dropped into public/images/<file> by hand (see IMAGE-CHECKLIST.md).
// Until a given file exists, callers fall back to the illustrated <Thumb> placeholder —
// no code change needed once photos land, the build just starts using them.
export function galleryFor(urlPath: string): GalleryImage[] {
  const entries = manifest[urlPath] ?? [];
  return entries.map((e) => ({
    file: e.file,
    alt: e.alt,
    src: '/images/' + e.file,
    exists: existsSync(PUBLIC_IMAGES_DIR + e.file),
  }));
}
