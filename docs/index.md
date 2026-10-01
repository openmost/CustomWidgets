## Documentation

Add custom content, such as text and custom HTML code, to your dashboards with dedicated widgets.
Share internal news, useful links, videos, iframes or any content with your team.

### Create a widget

1. Go to __Administration > System > Custom Widgets__.
2. Click on __Create a widget__, then enter a title and the content of the widget (text and HTML). The preview next to the code editor shows the widget as it looks on a dashboard.
3. In __Apply to__, keep __All websites__ or pick one or more websites (one at a time, or all websites matching a search).
4. Click on __Create__.

The list of widgets lets you edit or delete your widgets. In the dashboard widget selector, the custom widgets are listed in alphabetical order.

### Add a widget to a dashboard

Open a dashboard, click on __Dashboard__, then __Widgets__ and select a widget in the __Custom Widget__ category.

A widget restricted to specific websites is only listed on the dashboards of these websites.

### Display external content

Matomo protects its pages with a Content Security Policy: iframes, images, videos and audio loaded from other domains are blocked by the browser.
Add the domains you trust in the __Allowed external domains__ section of the Custom Widgets page, one per line, for example:

- `www.example.com`
- `*.example.com` (all sub domains)
- `https://cdn.example.com` (HTTPS only)

YouTube domains are allowed by default, so you can embed a video right away:

```html
<iframe width="100%" height="315" src="https://www.youtube-nocookie.com/embed/VIDEO_ID" allowfullscreen></iframe>
```

### Scripts

Scripts included in the content are executed when the widget is displayed on a dashboard, and in the preview once you stop typing.

### HTTP API

Super users can also manage the widgets with the HTTP API: `CustomWidgets.getWidgets`, `CustomWidgets.getWidget`, `CustomWidgets.addWidget`, `CustomWidgets.updateWidget`, `CustomWidgets.deleteWidget`, `CustomWidgets.getAllowedDomains` and `CustomWidgets.setAllowedDomains`.

### Upgrade from version 1.x

The widget you created with the previous version becomes the first widget of the list, the dashboards displaying it keep working. Its settings moved from the general settings to the Custom Widgets page.
