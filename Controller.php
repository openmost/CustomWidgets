<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CustomWidgets;

use Piwik\Common;
use Piwik\Piwik;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Management page of the widgets (Administration > System > Custom Widgets). The CustomWidgets.ManageWidgets
     * Vue component is rendered by the core client widget renderer, which loads the plugin UMD on demand.
     */
    public function manage(): string
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('manage', [
            'widget' => [
                'uniqueId' => 'customWidgetsManage',
                'name' => Piwik::translate('CustomWidgets_CustomWidgets'),
                'isWide' => true,
                'clientComponent' => [
                    'plugin' => 'CustomWidgets',
                    'name' => 'ManageWidgets',
                ],
            ],
        ]);
    }

    /**
     * Renders a custom widget. The content (HTML written by a super user) is output as is, so the dashboard executes
     * its scripts like CustomWidgets 1.x did. Without idWidget, the widget of CustomWidgets 1.x is rendered.
     */
    public function getCustomWidget(): string
    {
        $this->checkSitePermission();

        $idWidget = Common::getRequestVar('idWidget', WidgetDefinitions::LEGACY_WIDGET_ID, 'int');
        $widget = WidgetDefinitions::findWidget($idWidget);

        if (null === $widget || !WidgetDefinitions::isAvailableForSite($widget, (int) $this->idSite)) {
            $content = '<p class="custom-widget-unavailable">' . Piwik::translate('CustomWidgets_WidgetNotAvailable') . '</p>';
        } else {
            $content = $widget['content'];
        }

        return '<div class="widgetBody custom-widget-body">' . $content . '</div>';
    }
}
