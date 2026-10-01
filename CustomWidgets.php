<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomWidgets;

use Piwik\Common;
use Piwik\Option;
use Piwik\Widget\WidgetConfig;

class CustomWidgets extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Template.afterEventsReport' => 'renderOpenmostCommunicationAfterEvents',
            'Widget.filterWidgets' => 'addOpenmostCommunicationWidgets',
            'Template.beforeContent' => 'renderOpenmostCommunication',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Widget.addWidgetConfigs' => 'addWidgetConfigs',
            'API.API.getWidgetMetadata.end' => 'sortWidgetMetadata',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function uninstall()
    {
        Option::delete(WidgetDefinitions::OPTION_WIDGETS);
        Option::delete(WidgetDefinitions::OPTION_LAST_ID);
        Option::delete(AllowedDomains::OPTION_NAME);
    }

    public function shouldLoadUmdOnDemand()
    {
        // The UMD bundles the HTML code editor of the management page, keep it out of the global Matomo assets
        return true;
    }

    public function getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/CustomWidgets/stylesheets/theme.less';
    }

    public function addWidgetConfigs(&$configs)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        foreach (WidgetDefinitions::getWidgets() as $widget) {
            if ($idSite > 0 && !WidgetDefinitions::isAvailableForSite($widget, $idSite)) {
                continue;
            }

            $config = new WidgetConfig();
            $config->setCategoryId('CustomWidgets_CustomWidget');
            $config->setModule('CustomWidgets');
            $config->setAction('getCustomWidget');
            $config->setParameters(['idWidget' => $widget['id']]);
            $config->setName($widget['title']);
            $config->setIsWidgetizable();
            $configs[] = $config;
        }
    }

    /**
     * Core sorts the widgets of a category by unique id, which would list "Widget 10" before "Widget 2",
     * so the custom widgets are sorted by title in the slots they already occupy
     */
    public function sortWidgetMetadata(&$widgets): void
    {
        if (!is_array($widgets)) {
            return;
        }

        $slots = [];
        $customWidgets = [];
        foreach ($widgets as $index => $widget) {
            if (($widget['module'] ?? null) === 'CustomWidgets') {
                $slots[] = $index;
                $customWidgets[] = $widget;
            }
        }

        usort($customWidgets, static function (array $a, array $b): int {
            return strnatcasecmp((string) $a['name'], (string) $b['name']);
        });

        foreach ($slots as $position => $index) {
            $widgets[$index] = $customWidgets[$position];
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CustomWidgets_AllWebsites';
        $translationKeys[] = 'CustomWidgets_ApplyTo';
        $translationKeys[] = 'CustomWidgets_FindWebsites';
        $translationKeys[] = 'CustomWidgets_NoWebsiteMatching';
        $translationKeys[] = 'CustomWidgets_SelectWebsitesMatchingSearch';
        $translationKeys[] = 'CustomWidgets_WebsitesAdded';
        $translationKeys[] = 'CustomWidgets_AllowedDomainsDescription';
        $translationKeys[] = 'CustomWidgets_AllowedDomainsSaved';
        $translationKeys[] = 'CustomWidgets_AllowedDomainsTitle';
        $translationKeys[] = 'CustomWidgets_CreateWidget';
        $translationKeys[] = 'CustomWidgets_CustomWidgets';
        $translationKeys[] = 'CustomWidgets_DeleteWidgetConfirm';
        $translationKeys[] = 'CustomWidgets_DisplayOn';
        $translationKeys[] = 'CustomWidgets_DomainsOnePerLine';
        $translationKeys[] = 'CustomWidgets_EditWidget';
        $translationKeys[] = 'CustomWidgets_ManageIntro';
        $translationKeys[] = 'CustomWidgets_NoWidgets';
        $translationKeys[] = 'CustomWidgets_Preview';
        $translationKeys[] = 'CustomWidgets_UntitledWidget';
        $translationKeys[] = 'CustomWidgets_WidgetContent';
        $translationKeys[] = 'CustomWidgets_WidgetContentHelp';
        $translationKeys[] = 'CustomWidgets_WidgetCreated';
        $translationKeys[] = 'CustomWidgets_WidgetDeleted';
        $translationKeys[] = 'CustomWidgets_WidgetNotFound';
        $translationKeys[] = 'CustomWidgets_Widgets';
        $translationKeys[] = 'CustomWidgets_WidgetTitle';
        $translationKeys[] = 'CustomWidgets_WidgetTitleHelp';
        $translationKeys[] = 'CustomWidgets_WidgetUpdated';
        $translationKeys[] = 'General_Actions';
        $translationKeys[] = 'General_Cancel';
        $translationKeys[] = 'General_Create';
        $translationKeys[] = 'General_Delete';
        $translationKeys[] = 'General_Edit';
        $translationKeys[] = 'General_Id';
        $translationKeys[] = 'General_LoadingData';
        $translationKeys[] = 'General_Name';
        $translationKeys[] = 'General_Remove';
        $translationKeys[] = 'General_Search';
        $translationKeys[] = 'General_Website';
        $translationKeys[] = 'General_Save';
        $translationKeys[] = 'General_Update';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'General_No';
    }

    public function renderOpenmostCommunication(&$out, $layout, $module = '', $action = '')
    {
        OpenmostCommunication::beforeContent($out, (string) $layout, (string) $module, (string) $action, $this->getPluginName());
    }

    public function addOpenmostCommunicationWidgets($list)
    {
        OpenmostCommunication::filterWidgets($list, $this->getPluginName());
    }

    public function renderOpenmostCommunicationAfterEvents(&$out, $dataTable = null)
    {
        OpenmostCommunication::afterEventsReport($out, $this->getPluginName());
    }
}
