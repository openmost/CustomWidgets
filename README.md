# Matomo CustomWidgets Plugin

Add a widget with your own content to Matomo dashboards: team news, links to internal resources, embedded videos, iframes, images or any HTML content.

## Features

- **One custom widget** with its own title and content (text and HTML)
- **Simple configuration** in the General settings, editable by super users
- **Any HTML content**: `<iframe>`, `<img>`, `<svg>` and `<canvas>` elements never overflow the widget width
- **YouTube ready**: YouTube and YouTube no-cookie iframes are allowed by the Matomo Content Security Policy
- Available on every website, in the **Custom Widget** category of the dashboard widget selector
- The widget can be embedded outside Matomo with the Widgetize feature

The Matomo 6 version of the plugin adds unlimited widgets, a dedicated management page with a code editor and live preview, per website display, allowed external domains and an HTTP API.

## Requirements

- Matomo 5.10.0 or higher, below 6.0.0

## Installation / Configuration

1. Install and activate the plugin from the Matomo Marketplace (**Administration > Platform > Marketplace**).
2. Go to **Administration > System > General settings**, section **CustomWidgets**.
3. Enter the widget title and the content to display, then save.
4. Open a dashboard, click **Dashboard > Widgets**, and pick your widget in the **Custom Widget** category.

## Privacy and data

The widget title and content are stored in the Matomo database as plugin system settings. The plugin sends nothing outside your Matomo instance. The HTML content is displayed as written and is not filtered, so only add content you trust.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. When you need widgets driven by your own data or business logic, we build [custom Matomo plugins](https://openmost.com/matomo/services/plugin-development?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=customwidgets) from a written spec, tested on the Matomo versions you run and maintained over time.

## Support

- Documentation: https://openmost.com/matomo/extensions/custom-widgets
- Email: ronan@openmost.com
- Issues: https://github.com/openmost/CustomWidgets/issues

## Screenshots

See the `screenshots/` folder for the settings, the widget picker of the dashboard and the widget displayed on a dashboard.
