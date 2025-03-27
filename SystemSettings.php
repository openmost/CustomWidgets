<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomWidgets;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;

/**
 * Defines Settings for CustomWidgets.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $widgetTitle;

    /** @var Setting */
    public $widgetContent;

    protected function init()
    {
        $this->widgetTitle = $this->createWidgetTitleSetting();
        $this->widgetContent = $this->createWidgetContentSetting();
    }

    private function createWidgetTitleSetting()
    {
        return $this->makeSetting('widgetTitle', $default = 'Custom Widget', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'The widget title';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Define the widget title.';
            $field->validators[] = new NotEmpty();
        });
    }

    private function createWidgetContentSetting()
    {
        return $this->makeSetting('widgetContent', $default = '<p>Edit the content of your custom widget in the <a href="/index.php?module=CoreAdminHome&action=generalSettings#/CustomWidgets">general settings</a>.</p>', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Content to display';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->description = 'You can add text and custom HTML.';
            $field->validators[] = new NotEmpty();
        });
    }
}
