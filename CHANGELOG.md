## Changelog

### 6.1.0

- "Apply to" website selector, like Custom Reports: all websites, one or more websites, or every website matching a search
- The widget preview uses the dashboard widget design and runs the scripts of the content, like the dashboards
- Plain text content (without HTML blocks) is displayed as paragraphs
- Custom widgets are sorted alphabetically in the dashboard widget selector
- Remove the widget reordering (move up / move down buttons and `CustomWidgets.reorderWidgets` API), it had no effect on the widget selector
- The code editor uses a light and dark palette that follows the Matomo theme
- Interface translated into 12 languages
- Shorter Marketplace description that fits the plugin cards, and campaign parameters on the Openmost links of the README.

### 6.0.0

- Compatibility with Matomo 6.x (`>=6.0.0-b1,<7.0.0-b1`)
- Requires PHP 8.1+ (and MySQL 8.0+ or MariaDB 10.6+, like Matomo 6)
- Dedicated management page in Administration > System > Custom Widgets (Vue), replacing the general settings section
- Unlimited widgets: create, edit, reorder and delete as many widgets as needed, each with its own title and content
- HTML code editor with syntax highlighting (CodeMirror) and live preview of the widget
- Display a widget on all websites or only on selected websites
- Allowed external domains to allow iframes, images, videos and audio from other domains in the Content Security Policy (YouTube allowed by default)
- HTTP API to manage the widgets and the allowed domains (super user)
- Fix the default content link when Matomo is installed in a sub directory
- French translation
- Automatic upgrade: the widget of version 1.x becomes the first widget of the list and stays on the dashboards
- Update plugin homepage URL and support email

### 1.0.4

update: Default widget content link

### 1.0.3

update: Documentation

### 1.0.2

update: Iframe, image, svg and canvas with fitting.

### 1.0.1

publication on Matomo Marketplace

### 1.0.0

setup: Plugin initial upload
