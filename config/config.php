<?php

return array(
    \Piwik\View\SecurityPolicy::class => \Piwik\DI::decorate(function ($previous) {
        /** @var \Piwik\View\SecurityPolicy $previous */

        if (!\Piwik\SettingsPiwik::isMatomoInstalled()) {
            return $previous;
        }

        $previous->addPolicy('frame-src', 'www.youtube.com');
        $previous->addPolicy('frame-src', 'youtube.com');
        $previous->addPolicy('frame-src', 'www.youtube-nocookie.com');
        $previous->addPolicy('frame-src', 'youtube-nocookie.com');
        return $previous;
    }),
);
