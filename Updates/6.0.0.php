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
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Dashboard\Model as DashboardModel;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;

/**
 * CustomWidgets 1.x had a single widget stored in system settings (widgetTitle and widgetContent), 6.0.0 manages a
 * list of widgets from a dedicated page: the existing widget becomes the first widget of the list and the dashboards
 * displaying it are updated.
 */
class Updates_6_0_0 extends PiwikUpdates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        $oldWidgets = [
            ['module' => 'CustomWidgets', 'action' => 'getCustomWidget', 'params' => []],
        ];
        $newWidgets = [
            ['module' => 'CustomWidgets', 'action' => 'getCustomWidget', 'params' => ['idWidget' => WidgetDefinitions::LEGACY_WIDGET_ID]],
        ];

        $table = Common::prefixTable('user_dashboard');
        $dashboards = Db::get()->fetchAll("SELECT * FROM `" . $table . "` WHERE layout LIKE ?", ['%CustomWidgets%']);
        $sql = "UPDATE `" . $table . "` SET layout = ? WHERE login = ? AND iddashboard = ?";

        foreach ($dashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);
            if (!is_object($dashboardLayout) || !isset($dashboardLayout->columns)) {
                continue;
            }

            $dashboardLayout = DashboardModel::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout !== $dashboard['layout']) {
                $migrations[] = $this->migration->db->boundSql($sql, [$newLayout, $dashboard['login'], $dashboard['iddashboard']]);
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $this->migrateSettings();
    }

    private function migrateSettings(): void
    {
        $storage = new PluginSettingsTable('CustomWidgets', '');
        $values = $storage->load();

        if (empty($values)) {
            return;
        }

        if (Option::get(WidgetDefinitions::OPTION_WIDGETS) === false) {
            if (isset($values['widgets']) && is_array($values['widgets'])) {
                // settings of the 6.0.0 development versions
                WidgetDefinitions::saveWidgets($values['widgets']);
            } elseif (array_key_exists('widgetTitle', $values) || array_key_exists('widgetContent', $values)) {
                $content = (string) ($values['widgetContent'] ?? WidgetDefinitions::DEFAULT_CONTENT);
                if (in_array($content, WidgetDefinitions::LEGACY_DEFAULT_CONTENTS, true)) {
                    $content = WidgetDefinitions::DEFAULT_CONTENT;
                }

                $title = trim((string) ($values['widgetTitle'] ?? ''));

                WidgetDefinitions::saveWidgets([
                    [
                        'id' => WidgetDefinitions::LEGACY_WIDGET_ID,
                        'title' => $title !== '' ? $title : WidgetDefinitions::DEFAULT_TITLE,
                        'content' => $content,
                        'idSites' => [],
                    ],
                ]);
            }
        }

        if (isset($values['allowedDomains']) && is_array($values['allowedDomains']) && Option::get(AllowedDomains::OPTION_NAME) === false) {
            Option::set(AllowedDomains::OPTION_NAME, json_encode(AllowedDomains::normalize($values['allowedDomains'])));
        }

        // the plugin has no system settings anymore
        $storage->delete();
    }
}
