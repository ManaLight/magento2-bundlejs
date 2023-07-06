<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use PureMashiro\BundleJs\Model\AutoCollect;
use PureMashiro\BundleJs\Model\ManualCollect;

class Config
{
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE = 'bundle_js/general/enable';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_COLLECT_BUNDLE_JS = 'bundle_js/general/enable_collect_bundle_js';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_AUTO_COLLECT = 'bundle_js/general/enable_auto_collect';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_GENERATE_STATIC_BUNDLE_JS =
        'bundle_js/general/enable_generate_static_bundle_js';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_BUNDLE_JS_IN_STOREFRONT =
        'bundle_js/general/enable_bundle_js_in_storefront';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_DELAY_JS_EXECUTION = 'bundle_js/general/enable_delay_js_execution';
    public const XML_PATH_BUNDLE_JS_CUSTOM_PATH = 'bundle_js/custom_path';
    public const XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_INTERNAL_SCRIPTS = 'bundle_js/general/exclude_internal_scripts';
    public const XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_EXTERNAL_SCRIPTS = 'bundle_js/general/exclude_external_scripts';
    public const XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_JS_COMPONENTS = 'bundle_js/general/allowed_js_components';
    public const XML_PATH_BUNDLE_JS_GENERAL_DISABLE_BUNDLES_ON_STATIC_PAGES =
        'bundle_js/general/disable_bundles_on_static_pages';
    public const XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_STATIC_PAGES = 'bundle_js/general/allowed_static_pages';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var array
     */
    private $criticalDepsByTheme;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SessionManagerInterface $session
     * @param array $criticalDepsByTheme
     */
    public function __construct(
        ScopeConfigInterface    $scopeConfig,
        SessionManagerInterface $session,
        $criticalDepsByTheme = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->criticalDepsByTheme = $criticalDepsByTheme;
    }

    /**
     * Get Value.
     *
     * @param string $path
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Is Enable.
     *
     * @return bool
     */
    public function isEnable(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE);
    }

    /**
     * Is enable collect bundle js.
     *
     * @return bool
     */
    public function isEnableCollectBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_COLLECT_BUNDLE_JS);
    }

    /**
     * Is enable auto collect.
     *
     * @return bool
     */
    public function isEnableAutoCollect(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_AUTO_COLLECT);
    }

    /**
     * Is enable generate static bundle js.
     *
     * @return bool
     */
    public function isEnableGenerateStaticBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_GENERATE_STATIC_BUNDLE_JS);
    }

    /**
     * Is enable bundle Js in store front.
     *
     * @return bool
     */
    public function isEnableBundleJsInStorefront(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_BUNDLE_JS_IN_STOREFRONT);
    }

    /**
     * Can Defer Bundle Js.
     *
     * @return bool
     */
    public function canDeferBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableDeferBundleJs();
    }

    /**
     * Is enable defer bundle js.
     *
     * @return bool
     */
    public function isEnableDeferBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_DELAY_JS_EXECUTION);
    }

    /**
     * Get auto collect path.
     *
     * @param string $type
     * @return string
     */
    public function getAutoCollectPath($type): string
    {
        return trim((string)$this->getValue(self::XML_PATH_BUNDLE_JS_CUSTOM_PATH . '/' . $type), ' /');
    }

    /**
     * Can collect bundle js.
     *
     * @return bool
     */
    public function canCollectBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableCollectBundleJs();
    }

    /**
     * Can auto collect.
     *
     * @return bool
     */
    public function canAutoCollect(): bool
    {
        return $this->isEnable() && $this->isEnableAutoCollect();
    }

    /**
     * Can manual collect.
     *
     * @return bool
     */
    public function canManualCollect(): bool
    {
        return $this->isEnable() && !$this->isEnableAutoCollect();
    }

    /**
     * Can auto collect in action.
     *
     * @return bool
     */
    public function canAutoCollectInAction(): bool
    {
        return $this->canAutoCollect() && $this->session->getData(AutoCollect::IDENTIFIER);
    }

    /**
     * Can manual collect in action.
     *
     * @return bool
     */
    public function canManualCollectInAction(): bool
    {
        return $this->canManualCollect() && $this->session->getData(ManualCollect::IDENTIFIER);
    }

    /**
     * Can collect bundle js in action.
     *
     * @return bool
     */
    public function canCollectBundleJsInAction(): bool
    {
        return $this->canCollectBundleJs() && ($this->canAutoCollectInAction() || $this->canManualCollectInAction());
    }

    /**
     * Can bundle js in storefront.
     *
     * @return bool
     */
    public function canBundleJsInStorefront(): bool
    {
        return $this->isEnable() && $this->isEnableBundleJsInStorefront() && !$this->isEnableCollectBundleJs();
    }

    /**
     * Can generate static bundle js.
     *
     * @return bool
     */
    public function canGenerateStaticBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableGenerateStaticBundleJs() && !$this->isEnableCollectBundleJs();
    }

    /**
     * Get critical dependencies.
     *
     * @param string $type
     * @param string $theme
     * @return array
     */
    public function getCriticalDeps($type, $theme)
    {
        if (isset($this->criticalDepsByTheme[$theme][$type])) {
            $deps = $this->criticalDepsByTheme[$theme][$type];
        } else {
            $deps = (string)$this->getValue(sprintf('bundle_js/critical_js/deps_%s', $type));
        }

        return $deps ? array_unique(array_filter(array_map('trim', explode(',', $deps)))) : [];
    }

    /**
     * Get excluded internal scripts.
     *
     * @return array
     */
    public function getExcludeInternalScripts()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_INTERNAL_SCRIPTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * Get excluded external scripts.
     *
     * @return array
     */
    public function getExcludeExternalScripts()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_EXTERNAL_SCRIPTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * Get allowed JS components.
     *
     * @return array
     */
    public function getAllowedJsComponents()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_JS_COMPONENTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * Is disable bundles on static pages.
     *
     * @return bool
     */
    public function isDisableBundlesOnStaticPages()
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_DISABLE_BUNDLES_ON_STATIC_PAGES);
    }

    /**
     * Get Allowed Static Pages.
     *
     * @return bool
     */
    public function getAllowedStaticPages()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_STATIC_PAGES);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }
}
