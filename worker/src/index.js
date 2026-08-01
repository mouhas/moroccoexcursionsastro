// Receives booking-widget / contact-form submissions from the static site
// and commits each one as a JSON file into src/content/bookings/ or
// src/content/messages/ in the GitHub repo, via the GitHub Contents API.
// Decap CMS (public/admin) then shows these as two more collections in the
// same admin panel used for content — no separate login, no database.
//
// Required secrets (wrangler secret put <NAME>):
//   GITHUB_TOKEN   — fine-grained PAT, "Contents: Read and write" on this repo only
//   GITHUB_REPO    — "your-username/your-repo-name"
//   ALLOWED_ORIGIN — comma-separated list of origins allowed to POST here,
//                    e.g. "https://morocco-excursion.com,http://localhost:4321"

const MAX_LEN = { name: 200, email: 200, phone: 60, subject: 300, message: 8000 };

function corsHeaders(origin, allowed) {
  const ok = allowed.includes(origin);
  return {
    'Access-Control-Allow-Origin': ok ? origin : allowed[0] || '',
    'Access-Control-Allow-Methods': 'POST, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Vary': 'Origin',
  };
}

function json(body, status, headers) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json', ...headers },
  });
}

function toBase64Utf8(str) {
  const bytes = new TextEncoder().encode(str);
  let binary = '';
  for (const b of bytes) binary += String.fromCharCode(b);
  return btoa(binary);
}

function clean(value, maxLen) {
  if (typeof value !== 'string') return '';
  return value.trim().slice(0, maxLen);
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Filesystem-safe, sortable-by-time filename. Not a security boundary —
// just needs to not collide and not break as a path segment.
function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '').slice(0, 40) || 'submission';
}

export default {
  async fetch(request, env) {
    const allowed = (env.ALLOWED_ORIGIN || '').split(',').map((s) => s.trim()).filter(Boolean);
    const origin = request.headers.get('Origin') || '';
    const headers = corsHeaders(origin, allowed);

    if (request.method === 'OPTIONS') {
      return new Response(null, { status: 204, headers });
    }
    if (!allowed.includes(origin)) {
      return json({ ok: false, error: 'origin not allowed' }, 403, headers);
    }
    if (request.method !== 'POST') {
      return json({ ok: false, error: 'method not allowed' }, 405, headers);
    }

    const url = new URL(request.url);
    if (url.pathname !== '/submit') {
      return json({ ok: false, error: 'not found' }, 404, headers);
    }

    let body;
    try {
      body = await request.json();
    } catch {
      return json({ ok: false, error: 'invalid JSON' }, 400, headers);
    }

    // Honeypot: a real visitor never fills this hidden field in. Pretend
    // success so bots don't know to try harder, but store nothing.
    if (body.website) {
      return json({ ok: true }, 200, headers);
    }

    const type = body.type === 'booking' ? 'bookings' : 'messages';
    const name = clean(body.name, MAX_LEN.name);
    const email = clean(body.email, MAX_LEN.email);
    if (!name || !isValidEmail(email)) {
      return json({ ok: false, error: 'name and a valid email are required' }, 400, headers);
    }

    const entry = {
      name,
      email,
      phone: clean(body.phone, MAX_LEN.phone),
      subject: clean(body.subject, MAX_LEN.subject),
      message: clean(body.message, MAX_LEN.message),
      submittedAt: new Date().toISOString(),
      status: 'new',
      raw: clean(body.raw, MAX_LEN.message),
    };

    const filename = `${entry.submittedAt.replace(/[:.]/g, '-')}-${slugify(name)}.json`;
    const path = `src/content/${type}/${filename}`;

    const ghResponse = await fetch(
      `https://api.github.com/repos/${env.GITHUB_REPO}/contents/${path}`,
      {
        method: 'PUT',
        headers: {
          Authorization: `Bearer ${env.GITHUB_TOKEN}`,
          Accept: 'application/vnd.github+json',
          'X-GitHub-Api-Version': '2022-11-28',
          'User-Agent': 'morocco-excursions-submit-worker',
        },
        body: JSON.stringify({
          message: `New ${type === 'bookings' ? 'booking' : 'message'} from ${name}`,
          content: toBase64Utf8(JSON.stringify(entry, null, 2)),
          branch: env.GITHUB_BRANCH || 'main',
        }),
      }
    );

    if (!ghResponse.ok) {
      const detail = await ghResponse.text().catch(() => '');
      console.error('GitHub commit failed', ghResponse.status, detail);
      return json({ ok: false, error: 'could not save submission' }, 502, headers);
    }

    return json({ ok: true }, 200, headers);
  },
};
