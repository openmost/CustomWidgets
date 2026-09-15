<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CustomWidgets;

use Piwik\Piwik;

/**
 * Manage the custom widgets displayed on the dashboards and the external domains allowed in their content.
 * All methods require super user access.
 *
 * @method static \Piwik\Plugins\CustomWidgets\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns all the custom widgets, in their display order.
     *
     * @return array<int, array{id: int, title: string, content: string, idSites: int[]}>
     */
    public function getWidgets(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        return array_map([self::class, 'encodeForOutput'], WidgetDefinitions::getWidgets());
    }

    /**
     * Returns a custom widget.
     *
     * @param int $idWidget Id of the widget.
     * @return array{id: int, title: string, content: string, idSites: int[]}
     */
    public function getWidget(int $idWidget): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $widget = WidgetDefinitions::findWidget($idWidget);
        if (null === $widget) {
            throw new \Exception(Piwik::translate('CustomWidgets_WidgetNotFound'));
        }

        return self::encodeForOutput($widget);
    }

    /**
     * Creates a custom widget.
     *
     * @param string $title Title of the widget.
     * @param string $content Content of the widget, text and HTML.
     * @param int[]|string $idSites Websites the widget is displayed on, array or comma separated list. Empty for all websites.
     * @return int Id of the created widget.
     * @unsanitized
     */
    public function addWidget(string $title, string $content = '', $idSites = []): int
    {
        Piwik::checkUserHasSuperUserAccess();

        return WidgetDefinitions::addWidget($title, $content, WidgetDefinitions::toIdList($idSites));
    }

    /**
     * Updates a custom widget.
     *
     * @param int $idWidget Id of the widget.
     * @param string $title Title of the widget.
     * @param string $content Content of the widget, text and HTML.
     * @param int[]|string $idSites Websites the widget is displayed on, array or comma separated list. Empty for all websites.
     * @unsanitized
     */
    public function updateWidget(int $idWidget, string $title, string $content = '', $idSites = []): void
    {
        Piwik::checkUserHasSuperUserAccess();

        WidgetDefinitions::updateWidget($idWidget, $title, $content, WidgetDefinitions::toIdList($idSites));
    }

    /**
     * Deletes a custom widget.
     *
     * @param int $idWidget Id of the widget.
     */
    public function deleteWidget(int $idWidget): void
    {
        Piwik::checkUserHasSuperUserAccess();

        WidgetDefinitions::deleteWidget($idWidget);
    }

    /**
     * Changes the display order of the custom widgets.
     *
     * @param int[]|string $idWidgets Widget ids in the new order, array or comma separated list.
     */
    public function reorderWidgets($idWidgets): void
    {
        Piwik::checkUserHasSuperUserAccess();

        WidgetDefinitions::reorderWidgets(WidgetDefinitions::toIdList($idWidgets));
    }

    /**
     * Returns the external domains allowed to display iframes, images, videos and audio in the widgets.
     *
     * @return string[]
     */
    public function getAllowedDomains(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        return AllowedDomains::getDomains();
    }

    /**
     * Sets the external domains allowed to display iframes, images, videos and audio in the widgets.
     *
     * @param string[]|string $domains Domains such as www.example.com, *.example.com or https://cdn.example.com, array or comma separated list.
     * @return string[] The saved domains.
     */
    public function setAllowedDomains($domains = []): array
    {
        Piwik::checkUserHasSuperUserAccess();

        return AllowedDomains::saveDomains($domains);
    }

    /**
     * API renderers decode HTML entities of the returned strings (API data is stored HTML encoded in Matomo), the
     * widgets are stored as written: encode them so the rendered title and content are exactly the stored ones,
     * eg. "&lt;b&gt;" in a content is not turned into a tag.
     */
    private static function encodeForOutput(array $widget): array
    {
        $widget['title'] = htmlspecialchars($widget['title'], ENT_QUOTES, 'UTF-8');
        $widget['content'] = htmlspecialchars($widget['content'], ENT_QUOTES, 'UTF-8');

        return $widget;
    }
}
