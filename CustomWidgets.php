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
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Widget.addWidgetConfigs' => 'addWidgetConfigs',
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

        foreach (WidgetDefinitions::getWidgets() as $position => $widget) {
            if ($idSite > 0 && !WidgetDefinitions::isAvailableForSite($widget, $idSite)) {
                continue;
            }

            $config = new WidgetConfig();
            $config->setCategoryId('CustomWidgets_CustomWidget');
            $config->setModule('CustomWidgets');
            $config->setAction('getCustomWidget');
            $config->setParameters(['idWidget' => $widget['id']]);
            $config->setName($widget['title']);
            $config->setOrder(100 + $position);
            $config->setIsWidgetizable();
            $configs[] = $config;
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CustomWidgets_AllWebsites';
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
        $translationKeys[] = 'CustomWidgets_MoveDown';
        $translationKeys[] = 'CustomWidgets_MoveUp';
        $translationKeys[] = 'CustomWidgets_NoWebsiteFound';
        $translationKeys[] = 'CustomWidgets_NoWebsiteSelected';
        $translationKeys[] = 'CustomWidgets_NoWidgets';
        $translationKeys[] = 'CustomWidgets_Preview';
        $translationKeys[] = 'CustomWidgets_PreviewScriptsNotice';
        $translationKeys[] = 'CustomWidgets_SearchWebsites';
        $translationKeys[] = 'CustomWidgets_SelectedWebsites';
        $translationKeys[] = 'CustomWidgets_SpecificWebsites';
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
        $translationKeys[] = 'General_Save';
        $translationKeys[] = 'General_Update';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'General_No';
    }
}
