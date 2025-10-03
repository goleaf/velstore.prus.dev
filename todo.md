# Dashboard Upgrade and UI Refactor - Priority Plan

1) Upgrade dashboards with aggregated analytics (admin and vendor)
- KPIs: revenue, orders, AOV, refunds, conversion, top products, low stock
- Trends: daily/weekly sales chart, order status breakdown
- Data freshness: cache expensive queries

2) Replace hardcoded strings with translations and add missing keys
- Use __('cms.*') and JSON translations as needed
- Fill gaps across `lang/*`

3) Remove Bootstrap/CDN usage; install Tailwind via npm/Vite
- Configure tailwind.config.js and PostCSS
- Replace layout styles and components with Tailwind

4) Unify to a single base layout and refactor blades into components
- Shared header/footer/sidebar components
- No inline CSS/JS in blades; move to resources

5) Create Form Request classes per controller action
- Centralize validation+messages; update controllers to use them

6) Asset pipeline cleanup
- Move inline scripts/styles to resources/js and resources/css (or scss)
- Build with Vite; no CDNs

7) Tests
- Feature tests for dashboards and controller actions
- Unit tests for services/helpers used by analytics

8) Formatting
- Run Pint to ensure code style

