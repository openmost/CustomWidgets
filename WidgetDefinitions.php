<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CustomWidgets;

use Piwik\Option;
use Piwik\Piwik;

/**
 * Stores the custom widgets in an option.
 *
 * A widget is an array ['id' => int, 'title' => string, 'content' => string, 'idSites' => int[]],
 * an empty idSites list meaning the widget is available on all websites.
 */
class WidgetDefinitions
{
    public const OPTION_WIDGETS = 'CustomWidgets_widgets';

    /**
     * Highest id ever given to a widget, ids are never reused so a deleted widget left on a dashboard is not
     * replaced by another widget
     */
    public const OPTION_LAST_ID = 'CustomWidgets_lastWidgetId';

    /**
     * Id given to the single widget of CustomWidgets 1.x, so dashboards displaying it keep working
     */
    public const LEGACY_WIDGET_ID = 1;

    public const DEFAULT_TITLE = 'Custom Widget';

    public const DEFAULT_CONTENT = '<p>Edit the content of your custom widgets in <a href="index.php?module=CustomWidgets&action=manage">Administration &gt; System &gt; Custom Widgets</a>.</p>';

    /**
     * Default contents of CustomWidgets 1.x, replaced by the current default content when upgrading
     */
    public const LEGACY_DEFAULT_CONTENTS = [
        '<p>Edit the content of your custom widget in the <a href="/index.php?module=CoreAdminHome&action=generalSettings#/CustomWidgets">general settings</a>.</p>',
        '<p>Edit the content of your custom widget in the <a href="index.php?module=CoreAdminHome&action=generalSettings#/CustomWidgets">general settings</a>.</p>',
    ];

    public const TITLE_MAX_LENGTH = 255;

    /**
     * Keep both patterns in sync with vue/src/WidgetEdit/formatContent.ts (preview)
     */
    private const INVISIBLE_ELEMENTS_PATTERN = '~<(script|style|template)\b.*?</\1\s*>~is';

    private const BLOCK_TAG_PATTERN = '~<(address|article|aside|audio|blockquote|canvas|details|div|dl|fieldset|figure|footer|form|h[1-6]|header|hr|iframe|main|nav|ol|p|pre|section|svg|table|ul|video)[\s>/]~i';

    public static function getDefaultWidgets(): array
    {
        return [
            [
                'id' => self::LEGACY_WIDGET_ID,
                'title' => self::DEFAULT_TITLE,
                'content' => self::DEFAULT_CONTENT,
                'idSites' => [],
            ],
        ];
    }

    public static function getWidgets(): array
    {
        $stored = Option::get(self::OPTION_WIDGETS);
        if ($stored === false || $stored === null) {
            return self::getDefaultWidgets();
        }

        return self::normalize(json_decode((string) $stored, true));
    }

    public static function findWidget(int $idWidget): ?array
    {
        foreach (self::getWidgets() as $widget) {
            if ($widget['id'] === $idWidget) {
                return $widget;
            }
        }

        return null;
    }

    public static function isAvailableForSite(array $widget, int $idSite): bool
    {
        return empty($widget['idSites']) || in_array($idSite, $widget['idSites'], true);
    }

    /**
     * @param int[] $idSites
     * @return int id of the new widget
     */
    public static function addWidget(string $title, string $content, array $idSites): int
    {
        self::checkTitle($title);

        $widgets = self::getWidgets();
        $idWidget = max(self::getLastId($widgets), 0) + 1;
        $widgets[] = [
            'id' => $idWidget,
            'title' => $title,
            'content' => $content,
            'idSites' => $idSites,
        ];
        self::saveWidgets($widgets);

        return $idWidget;
    }

    /**
     * @param int[] $idSites
     */
    public static function updateWidget(int $idWidget, string $title, string $content, array $idSites): void
    {
        self::checkTitle($title);

        $widgets = self::getWidgets();
        $index = self::getIndex($widgets, $idWidget);
        $widgets[$index] = [
            'id' => $idWidget,
            'title' => $title,
            'content' => $content,
            'idSites' => $idSites,
        ];
        self::saveWidgets($widgets);
    }

    public static function deleteWidget(int $idWidget): void
    {
        $widgets = self::getWidgets();
        array_splice($widgets, self::getIndex($widgets, $idWidget), 1);
        self::saveWidgets($widgets);
    }

    public static function saveWidgets(array $widgets): array
    {
        $widgets = self::normalize($widgets);

        Option::set(self::OPTION_WIDGETS, json_encode($widgets));
        Option::set(self::OPTION_LAST_ID, (string) self::getLastId($widgets));

        return $widgets;
    }

    /**
     * Casts the values, drops invalid widgets and gives an id to widgets without one (or with a duplicated id)
     *
     * @param mixed $widgets
     */
    public static function normalize($widgets): array
    {
        if (!is_array($widgets)) {
            return [];
        }

        $normalized = [];
        foreach ($widgets as $widget) {
            if (!is_array($widget)) {
                continue;
            }

            $title = trim(self::getString($widget, 'title'));
            if ($title === '') {
                continue;
            }

            $normalized[] = [
                'id' => is_numeric($widget['id'] ?? null) ? (int) $widget['id'] : 0,
                'title' => $title,
                'content' => self::getString($widget, 'content'),
                'idSites' => self::toIdList($widget['idSites'] ?? []),
            ];
        }

        $maxId = 0;
        foreach ($normalized as $widget) {
            $maxId = max($maxId, $widget['id']);
        }

        $usedIds = [];
        foreach ($normalized as $index => $widget) {
            if ($widget['id'] <= 0 || isset($usedIds[$widget['id']])) {
                $normalized[$index]['id'] = ++$maxId;
            }
            $usedIds[$normalized[$index]['id']] = true;
        }

        return $normalized;
    }

    /**
     * @param mixed $values array or comma separated list of ids
     * @return int[] positive unique ids
     */
    public static function toIdList($values): array
    {
        if (is_string($values)) {
            $values = $values === '' ? [] : explode(',', $values);
        } elseif (!is_array($values)) {
            $values = [$values];
        }

        $ids = [];
        foreach ($values as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @throws \Exception
     */
    public static function checkTitle(string $title): void
    {
        if (trim($title) === '') {
            throw new \Exception(Piwik::translate('CustomWidgets_WidgetTitleRequired'));
        }

        if (mb_strlen(trim($title)) > self::TITLE_MAX_LENGTH) {
            throw new \Exception(Piwik::translate('CustomWidgets_WidgetTitleTooLong', [self::TITLE_MAX_LENGTH]));
        }
    }

    /**
     * @throws \Exception
     */
    private static function getIndex(array $widgets, int $idWidget): int
    {
        foreach ($widgets as $index => $widget) {
            if ($widget['id'] === $idWidget) {
                return $index;
            }
        }

        throw new \Exception(Piwik::translate('CustomWidgets_WidgetNotFound'));
    }

    private static function getLastId(array $widgets): int
    {
        $lastId = (int) Option::get(self::OPTION_LAST_ID);
        foreach ($widgets as $widget) {
            $lastId = max($lastId, (int) $widget['id']);
        }

        return $lastId;
    }

    /**
     * Text written without block elements is wrapped in paragraphs (a blank line starts a new one, a line break becomes
     * <br>), so it gets the paragraph spacing of the widgets instead of sticking to the bottom edge
     */
    public static function formatContent(string $content): string
    {
        $content = trim($content);
        $visibleText = trim(strip_tags((string) preg_replace(self::INVISIBLE_ELEMENTS_PATTERN, '', $content)));
        if ($visibleText === '' || preg_match(self::BLOCK_TAG_PATTERN, $content)) {
            return $content;
        }

        $paragraphs = preg_split('~\R\s*\R~u', $content) ?: [$content];

        return implode('', array_map(static function (string $paragraph): string {
            return '<p>' . preg_replace('~\R~u', '<br>', trim($paragraph)) . '</p>';
        }, $paragraphs));
    }

    private static function getString(array $widget, string $key): string
    {
        $value = $widget[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }
}
