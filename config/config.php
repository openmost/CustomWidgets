<?php

return [
    \Piwik\View\SecurityPolicy::class => \Piwik\DI::decorate(function ($previous) {
        /** @var \Piwik\View\SecurityPolicy $previous */

        if (!\Piwik\SettingsPiwik::isMatomoInstalled()) {
            return $previous;
        }

        try {
            \Piwik\Plugins\CustomWidgets\AllowedDomains::extendSecurityPolicy($previous);
        } catch (\Throwable $e) {
            // never break the page when the settings cannot be read (eg. while Matomo is updating)
        }

        return $previous;
    }),
];
