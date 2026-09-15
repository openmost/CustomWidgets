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
use Piwik\Option;
use Piwik\Piwik;
use Piwik\View\SecurityPolicy;

/**
 * External domains the Content Security Policy allows to load iframes, images, videos and audio in the widgets
 */
class AllowedDomains
{
    public const OPTION_NAME = 'CustomWidgets_allowedDomains';

    public const DEFAULT_DOMAINS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'youtube-nocookie.com',
    ];

    /**
     * Pages displaying the widgets (dashboards, widgetized iframes) and the widgets preview of the management page.
     * The policy is not extended on other pages: declaring frame-src there would restrict pages relying on the
     * default-src fallback.
     */
    private const MODULES = ['CoreHome', 'Dashboard', 'Widgetize', 'CustomWidgets'];

    // host name with an optional scheme, wildcard sub domain and port, eg. https://*.example.com:8080
    private const DOMAIN_PATTERN = '~^(https?://)?(\*\.)?([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)*[a-z0-9]([a-z0-9-]*[a-z0-9])?(:[0-9]{1,5})?$~i';

    public static function getDomains(): array
    {
        $stored = Option::get(self::OPTION_NAME);
        if ($stored === false || $stored === null) {
            return self::DEFAULT_DOMAINS;
        }

        return self::normalize(json_decode((string) $stored, true));
    }

    /**
     * @param mixed $domains
     * @throws \Exception
     */
    public static function saveDomains($domains): array
    {
        self::validate($domains);
        $domains = self::normalize($domains);

        Option::set(self::OPTION_NAME, json_encode($domains));

        return $domains;
    }

    public static function extendSecurityPolicy(SecurityPolicy $policy): void
    {
        $module = Common::getRequestVar('module', 'CoreHome', 'string');
        if (!in_array($module, self::MODULES, true)) {
            return;
        }

        $domains = self::getDomains();
        if (empty($domains)) {
            return;
        }

        $sources = implode(' ', $domains);
        $policy->addPolicy('frame-src', "'self' " . $sources);
        $policy->addPolicy('media-src', "'self' " . $sources);
        $policy->addPolicy('img-src', $sources);
    }

    public static function isValid(string $domain): bool
    {
        return (bool) preg_match(self::DOMAIN_PATTERN, $domain);
    }

    /**
     * @param mixed $domains
     * @throws \Exception
     */
    public static function validate($domains): void
    {
        foreach (self::clean($domains) as $domain) {
            if (!self::isValid($domain)) {
                throw new \Exception(Piwik::translate('CustomWidgets_InvalidDomain', [$domain]));
            }
        }
    }

    /**
     * @param mixed $domains
     */
    public static function normalize($domains): array
    {
        return array_values(array_unique(array_filter(self::clean($domains), [self::class, 'isValid'])));
    }

    /**
     * @param mixed $domains array, or list separated by commas or new lines
     */
    private static function clean($domains): array
    {
        if (is_string($domains)) {
            $domains = preg_split('/[\s,]+/', $domains) ?: [];
        } elseif (!is_array($domains)) {
            return [];
        }

        $cleaned = [];
        foreach ($domains as $domain) {
            if (!is_scalar($domain)) {
                continue;
            }
            $domain = strtolower(rtrim(trim((string) $domain), '/'));
            if ($domain !== '') {
                $cleaned[] = $domain;
            }
        }

        return $cleaned;
    }
}
