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
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_GENERATE_STATIC_BUNDLE_JS = 'bundle_js/general/enable_generate_static_bundle_js';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_BUNDLE_JS_IN_STOREFRONT = 'bundle_js/general/enable_bundle_js_in_storefront';
    public const XML_PATH_BUNDLE_JS_GENERAL_ENABLE_DELAY_JS_EXECUTION = 'bundle_js/general/enable_delay_js_execution';
    public const XML_PATH_BUNDLE_JS_CUSTOM_PATH = 'bundle_js/custom_path';
    public const XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_INTERNAL_SCRIPTS = 'bundle_js/general/exclude_internal_scripts';
    public const XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_EXTERNAL_SCRIPTS = 'bundle_js/general/exclude_external_scripts';
    public const XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_JS_COMPONENTS = 'bundle_js/general/allowed_js_components';
    public const XML_PATH_BUNDLE_JS_GENERAL_DISABLE_BUNDLES_ON_STATIC_PAGES = 'bundle_js/general/disable_bundles_on_static_pages';
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
     * @param $path
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE);
    }

    /**
     * @return bool
     */
    public function isEnableCollectBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_COLLECT_BUNDLE_JS);
    }

    /**
     * @return bool
     */
    public function isEnableAutoCollect(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_AUTO_COLLECT);
    }

    /**
     * @return bool
     */
    public function isEnableGenerateStaticBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_GENERATE_STATIC_BUNDLE_JS);
    }

    /**
     * @return bool
     */
    public function isEnableBundleJsInStorefront(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_BUNDLE_JS_IN_STOREFRONT);
    }

    /**
     * @return bool
     */
    public function canDeferBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableDeferBundleJs();
    }

    /**
     * @return bool
     */
    public function isEnableDeferBundleJs(): bool
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ENABLE_DELAY_JS_EXECUTION);
    }

    /**
     * @param $type
     * @return string
     */
    public function getAutoCollectPath($type): string
    {
        return trim((string)$this->getValue(self::XML_PATH_BUNDLE_JS_CUSTOM_PATH . '/' . $type), ' /');
    }

    /**
     * @return bool
     */
    public function canCollectBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableCollectBundleJs();
    }

    /**
     * @return bool
     */
    public function canAutoCollect(): bool
    {
        return $this->isEnable() && $this->isEnableAutoCollect();
    }

    /**
     * @return bool
     */
    public function canManualCollect(): bool
    {
        return $this->isEnable() && !$this->isEnableAutoCollect();
    }

    /**
     * @return bool
     */
    public function canAutoCollectInAction(): bool
    {
        return $this->canAutoCollect() && $this->session->getData(AutoCollect::IDENTIFIER);
    }

    /**
     * @return bool
     */
    public function canManualCollectInAction(): bool
    {
        return $this->canManualCollect() && $this->session->getData(ManualCollect::IDENTIFIER);
    }

    /**
     * @return bool
     */
    public function canCollectBundleJsInAction(): bool
    {
        return $this->canCollectBundleJs() && ($this->canAutoCollectInAction() || $this->canManualCollectInAction());
    }

    /**
     * @return bool
     */
    public function canBundleJsInStorefront(): bool
    {
        return $this->isEnable() && $this->isEnableBundleJsInStorefront() && !$this->isEnableCollectBundleJs();
    }

    /**
     * @return bool
     */
    public function canGenerateStaticBundleJs(): bool
    {
        return $this->isEnable() && $this->isEnableGenerateStaticBundleJs() && !$this->isEnableCollectBundleJs();
    }

    /**
     * @param $type
     * @param $theme
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
     * @return array
     */
    public function getExcludeInternalScripts()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_INTERNAL_SCRIPTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * @return array
     */
    public function getExcludeExternalScripts()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_EXCLUDE_EXTERNAL_SCRIPTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * @return array
     */
    public function getAllowedJsComponents()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_JS_COMPONENTS);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }

    /**
     * @return bool
     */
    public function isDisableBundlesOnStaticPages()
    {
        return (bool)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_DISABLE_BUNDLES_ON_STATIC_PAGES);
    }

    /**
     * @return array
     */
    public function getAllowedStaticPages()
    {
        $value = (string)$this->getValue(self::XML_PATH_BUNDLE_JS_GENERAL_ALLOWED_STATIC_PAGES);

        return $value ? array_unique(array_filter(array_map('trim', explode('|||', $value)))) : [];
    }
}
