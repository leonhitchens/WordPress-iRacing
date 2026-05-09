# iRacing Racing Stats — WordPress Plugin

A WordPress plugin that displays a full iRacing stats dashboard on your website, powered by the [Garage61 API](https://garage61.net/developer/endpoints). Embed it with a Gutenberg block or a shortcode.

Built for [leonhitchens.com](https://leonhitchens.com).

---

## Features

- **Gutenberg block** — drag the *iRacing Racing Dashboard* block onto any page or post
- **Shortcode fallback** — `[iracing_stats]` works in any Classic block or widget
- **Full dashboard** with five sections:
  - Driver header: name, iRating badge, license class + safety rating
  - Career stat cards: Starts, Wins, Top 5s, Laps, Win %, Avg Finish
  - iRating history line chart (Chart.js)
  - Current season: series, division, starts, wins, points
  - Recent races table: date, series, track, start/finish position, iRating delta
- **API response caching** via WordPress transients (default 60 min, configurable)
- **No secrets in the repo** — API key and driver ID are entered in wp-admin and stored in the WordPress database
- **No build step** — pure PHP + vanilla JS, drop in and activate

---

## Requirements

| Requirement | Version |
|---|---|
| WordPress | 6.1 or later |
| PHP | 8.0 or later |
| Garage61 account | Personal Access Token required |

---

## Installation

1. Download or clone this repository
2. Copy the `iracing-racing-stats/` folder into your site's `wp-content/plugins/` directory
3. In wp-admin, go to **Plugins → Installed Plugins** and activate **iRacing Racing Stats**
4. Go to **Settings → iRacing Stats** and enter your credentials (see Configuration below)

Alternatively, zip the `iracing-racing-stats/` folder and install via **Plugins → Add New → Upload Plugin**.

---

## Configuration

Navigate to **wp-admin → Settings → iRacing Stats**.

| Setting | Description |
|---|---|
| **Personal Access Token** | Your Garage61 API token. Generate one at garage61.net → Developer → Tokens. |
| **Driver ID** | Your iRacing / Garage61 numeric customer ID. |
| **Cache Duration** | How long API responses are cached (1–1440 minutes). Default: 60. |

Credentials are stored in `wp_options` and are never committed to the repository.

> Saving settings automatically clears the cache so new credentials take effect immediately. You can also manually clear the cache with the **Clear Cache** button on the settings page.

---

## Usage

### Gutenberg Block

1. Edit any page or post
2. Click **+** to add a block
3. Search for **iRacing Racing Dashboard**
4. Insert it — the dashboard renders on the front end using your configured credentials

### Shortcode

Place `[iracing_stats]` in any post, page, or text widget.

**Optional attribute** — override the driver ID for a specific embed:

```
[iracing_stats driver_id="123456"]
```

---

## File Structure

```
iracing-racing-stats/
├── iracing-racing-stats.php          # Plugin bootstrap, block registration, asset enqueueing
├── includes/
│   ├── class-garage61-api.php        # Garage61 API client with Bearer token auth + transient caching
│   ├── class-settings-page.php       # wp-admin settings page
│   └── class-shortcode.php           # [iracing_stats] shortcode handler
├── blocks/
│   └── racing-dashboard/
│       ├── block.json                # Block metadata (server-side rendered)
│       ├── index.js                  # Block registration for the Gutenberg editor
│       └── editor.css                # Editor placeholder styles
├── assets/
│   ├── css/
│   │   └── racing-stats.css          # Frontend dashboard styles
│   └── js/
│       └── racing-chart.js           # Chart.js initializer for the iRating history chart
└── templates/
    └── dashboard.php                 # Shared HTML template (used by both block and shortcode)
```

---

## API Integration

The plugin communicates with the [Garage61 API](https://garage61.net/developer/endpoints) using your Personal Access Token sent as a Bearer token:

```
Authorization: Bearer <your-token>
```

### Endpoints used

| Data | Method | Endpoint (placeholder — confirm from Garage61 docs) |
|---|---|---|
| Driver profile | `GET` | `/drivers/{id}` |
| Career stats | `GET` | `/drivers/{id}/career` |
| Current season | `GET` | `/drivers/{id}/seasons/current` |
| Recent races | `GET` | `/drivers/{id}/races` |
| iRating history | `GET` | `/drivers/{id}/irating` |

> **Note:** The exact endpoint paths are marked with `// TODO` comments in `includes/class-garage61-api.php` and need to be confirmed against the live Garage61 API documentation. Only the `BASE_URL` constant and the path strings in each method need updating — the auth, caching, and error handling are all in place.

---

## Caching

All API responses are cached together under a single transient key per driver:

```
_transient_iracing_stats_dashboard_{md5(driver_id)}
```

The TTL defaults to 60 minutes and is configurable in wp-admin. Cache is automatically cleared when settings are saved or when the **Clear Cache** button is clicked.

---

## Security

- API key stored in `wp_options` via the WordPress Settings API — never in source code
- Settings page requires the `manage_options` capability (admins only)
- Cache clear form uses WordPress nonce verification
- All API output is sanitized with `esc_html()`, `esc_attr()`, `intval()` before rendering
- Assets (CSS + JS) are only enqueued on pages that actually use the block or shortcode

---

## Styling

The dashboard uses a dark racing aesthetic with CSS custom properties, making it straightforward to retheme:

```css
:root {
  --ir-bg:         #0f1117;
  --ir-bg-card:    #1a1e27;
  --ir-border:     #2a2f3b;
  --ir-text:       #e8eaed;
  --ir-text-muted: #8b95a5;
  --ir-accent:     #4a9eff;
  --ir-green:      #2ecc71;
  --ir-red:        #e74c3c;
}
```

Override any of these in your theme's stylesheet to match your site's colours.

---

## License

GPL-2.0-or-later — see [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html).
