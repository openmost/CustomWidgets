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
Choose __Specific websites__ when editing a widget to display it only on some websites.

__Where are the settings of version 1.x ?__

They moved from the general settings to the dedicated __Custom Widgets__ page. Your existing widget is kept and still displayed on your dashboards.

__Why is my iframe, image or video not displayed ?__

Content loaded from another domain is blocked by the Matomo Content Security Policy. Add the domain in the __Allowed external domains__ section of the Custom Widgets page, then reload the dashboard.

__Why does my script not run in the preview ?__

For safety, scripts are not executed in the preview. They run when the widget is displayed on a dashboard.

__Is the content of the widgets filtered ?__

No, the HTML is displayed as it is written, so only add content you trust.

__How can I contribute to this plugin ?__

You can help me develop this plugin by contacting me. You can also create the project and request an integration. Any way you consider legitimate to contribute is welcome.

__How long this plugin will be maintained ?__

As long as possible, I have many project to maintain, I'm the first user of this plugin and I use Matomo on many project, if I see errors, I'll patch this plugin faster as possible !
