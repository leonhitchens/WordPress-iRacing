# iRacing Racing Stats — WordPress Plugin

A WordPress plugin that displays your iRacing driver statistics on any page or post using a Gutenberg block or shortcode. Built by **Leon Hitchens** using data from the [Garage61 API](https://garage61.net).

---

## Overview

This plugin connects to the Garage61 API to pull live iRacing data and render a full stats dashboard on your WordPress site. It shows your driver profile, career statistics, current season performance, recent race results, and an interactive iRating history chart.

---

## Features

- **Driver Profile** — Display name, license class (color-coded), iRating badge, and member-since date
- **Career Stats** — Starts, wins, top-5 finishes, total laps, win percentage, and average finish
- **iRating Chart** — Interactive Chart.js line chart showing your iRating history over time
- **Current Season** — Series name, division, starts, wins, and points
- **Recent Races** — Scrollable table of your last 10 races with date, series, track, start/finish position, and iRating delta (green for gains, red for losses)
- **Two Display Methods** — Gutenberg block or `[iracing_stats]` shortcode
- **Caching** — WordPress transient caching with configurable TTL (default 60 minutes)
- **Dark Theme UI** — Responsive, dark-themed dashboard with fluid typography

---

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- A [Garage61](https://garage61.net) account with a Personal Access Token
- Your iRacing Driver ID

---

## Installation

1. Upload the `iracing-racing-stats` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin from the **Plugins** screen in WordPress admin.
3. Go to **Settings → iRacing Stats** and enter your credentials.

---

## Configuration

Navigate to **Settings → iRacing Stats** in the WordPress admin panel.

| Setting | Description |
|---|---|
| **Garage61 API Key** | Your Personal Access Token from garage61.net |
| **Driver ID** | Your iRacing Driver ID |
| **Cache Duration** | How long to cache API responses (1–1440 minutes, default 60) |

The settings page also includes a **Clear Cache** button to manually flush cached data.

---

## Usage

### Gutenberg Block

In the WordPress block editor, search for **"iRacing Racing Dashboard"** and insert it anywhere on a page or post.

### Shortcode

Add `[iracing_stats]` to any page, post, or widget area.

To display stats for a different driver, pass a `driver_id` attribute:

```
[iracing_stats driver_id="123456"]
```

---

## How It Works

The plugin uses the **Garage61 API** as the data source for iRacing statistics. Garage61 provides a developer API that exposes iRacing member data including driver profiles, career stats, season data, and race history.

API responses are cached using WordPress transients to avoid excessive API calls on each page load. The cache is automatically cleared whenever you save new settings.

### Data Fetched

| Endpoint | Data |
|---|---|
| Driver Profile | Name, license class, iRating, safety rating, member since |
| Career Stats | Starts, wins, top 5s, laps, win %, avg finish |
| Current Season | Active series, division, starts, wins, points |
| Recent Races | Last 10 race results with positions and iRating change |
| iRating History | Historical iRating values for chart rendering |

---

## File Structure

```
iracing-racing-stats/
├── iracing-racing-stats.php          # Plugin entry point and hooks
├── includes/
│   ├── class-garage61-api.php        # Garage61 API client with caching
│   ├── class-settings-page.php       # Admin settings page
│   └── class-shortcode.php           # [iracing_stats] shortcode handler
├── templates/
│   └── dashboard.php                 # HTML/PHP dashboard template
├── blocks/
│   └── racing-dashboard/
│       ├── block.json                # Gutenberg block metadata
│       ├── index.js                  # Block editor registration
│       └── editor.css                # Editor placeholder styling
└── assets/
    ├── css/
    │   └── racing-stats.css          # Frontend dashboard styles
    └── js/
        └── racing-chart.js           # Chart.js iRating chart integration
```

---

## Security

- The API key is stored in the WordPress database and never exposed in front-end output.
- API errors and credential warnings are only shown to logged-in administrators.
- All output is sanitized and escaped using WordPress core functions.
- Cache clearing uses nonce verification to prevent CSRF.

---

## Credits

Built by **Leon Hitchens**.

Data provided by the [Garage61 API](https://garage61.net). Garage61 offers a developer API for accessing iRacing statistics — refer to the [Garage61 developer documentation](https://garage61.net/developer/endpoints) for endpoint references and authentication details used to build this plugin.

iRating chart rendered using [Chart.js v4](https://www.chartjs.org/).

---

## License

GPL-2.0-or-later — see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)
