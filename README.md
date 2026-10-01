# Matomo CustomWidgets Plugin

Add your own widgets to Matomo dashboards: team news, links to internal resources, embedded videos, iframes, images or any HTML content.

## Features

- **Unlimited widgets**: super users create, edit and delete as many widgets as they need, each with its own title and content (text and HTML)
- **Dedicated admin page**: widgets are managed in **Administration > System > Custom Widgets**
- **HTML code editor** with syntax highlighting (HTML, inline CSS and JavaScript), in a light or dark palette that follows the Matomo theme
- **Live preview** using the dashboard widget design, scripts of the content run in the preview like on a dashboard
- **Plain text friendly**: content written without HTML blocks is displayed as paragraphs
- **Per website display**: show a widget on all websites, on selected websites, or add every website matching a search
- **Allowed external domains**: iframes, images, videos and audio from the domains you list are allowed by the Matomo Content Security Policy (YouTube is allowed by default)
- **Sorted widget selector**: custom widgets are listed alphabetically in the **Custom Widget** category of the dashboard widget selector
- **HTTP API** for super users: `CustomWidgets.getWidgets`, `getWidget`, `addWidget`, `updateWidget`, `deleteWidget`, `getAllowedDomains` and `setAllowedDomains`
- Widgets can be embedded outside Matomo with the Widgetize feature
- Interface translated into 12 languages

## Requirements

- Matomo 6.0.0 or higher, below 7.0.0
- PHP 8.1 or higher

## Installation / Configuration

1. Install and activate the plugin from the Matomo Marketplace (**Administration > Platform > Marketplace**).
2. Go to **Administration > System > Custom Widgets** and click **Create a widget**.
3. Enter a title and the content, choose the websites in **Apply to**, then click **Create**.
4. Open a dashboard, click **Dashboard > Widgets**, and pick your widget in the **Custom Widget** category.

To embed content from another domain, add the domain in the **Allowed external domains** section of the same page (for example `www.example.com`, `*.example.com` or `https://cdn.example.com`).

Upgrading from version 1.x: the widget configured in the General settings becomes the first widget of the list, and the dashboards displaying it keep working.

## Privacy and data

Widgets and allowed domains are stored in the Matomo database. The plugin sends nothing outside your Matomo instance. The HTML content is displayed as written and is not filtered, so only add content and external domains you trust: embedded content from other domains is loaded by the browser of every user viewing the widget.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. When you need widgets driven by your own data or business logic, we build [custom Matomo plugins](https://openmost.com/matomo/services/plugin-development?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=customwidgets) from a written spec, tested on the Matomo versions you run and maintained over time.

## Support

- Documentation: https://openmost.com/matomo/extensions/custom-widgets
- Email: ronan@openmost.com
- Issues: https://github.com/openmost/CustomWidgets/issues

## Screenshots

See the `screenshots/` folder for the management page, the widget editor and widgets displayed on a dashboard.
