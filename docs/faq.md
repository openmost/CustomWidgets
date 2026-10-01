## FAQ

__How to install this plugin__

This plugin is available in the official marketplace of Matomo. You have to install the same way as other plugins

- Go to the administration panel
- Look for the Marketplace section and select "Plugins" in the dropdown
- Then search for "**Custom Widgets**", install and activate the plugin.
- Go to __Administration > System > Custom Widgets__ to create your widgets.

__Who can create and edit the widgets ?__

Only super users can manage the widgets, from __Administration > System > Custom Widgets__. Every user can add the widgets to their dashboards.

__Who can see a widget ?__

All users having at least a view access to a website can display the widgets available for this website.
In __Apply to__, pick a website instead of __All websites__ to display the widget only on some websites. Each website you pick is added to the list, and __Add matching websites__ adds every website whose name contains the search.

__Where are the settings of version 1.x ?__

They moved from the general settings to the dedicated __Custom Widgets__ page. Your existing widget is kept and still displayed on your dashboards.

__Why is my iframe, image or video not displayed ?__

Content loaded from another domain is blocked by the Matomo Content Security Policy. Add the domain in the __Allowed external domains__ section of the Custom Widgets page, then reload the dashboard.

__When do the scripts run in the preview ?__

Scripts run in the preview like on a dashboard, once you stop typing in the code editor. They are run again after each change, so a script adding event listeners or timers adds them again: reload the page to start from a clean state.

__Do I need to write HTML ?__

No. Text written without HTML blocks (paragraphs, lists, tables, iframes...) is displayed as paragraphs: a blank line starts a new paragraph and a line break is kept.

__Is the content of the widgets filtered ?__

No, the HTML is displayed as it is written, so only add content you trust.

__How can I contribute to this plugin?__

Open an issue or a pull request on [GitHub](https://github.com/openmost/CustomWidgets), or contact us at ronan@openmost.com.

__How long will this plugin be maintained?__

As long as possible. We use Matomo and this plugin on many projects every day, so issues are fixed as fast as possible.
