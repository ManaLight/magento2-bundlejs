<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Helper;

use PureMashiro\BundleJs\Action\Collect\AddRequireJsContextsConfig;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class DeferJsReplacer
{
    /**
     * @var AddRequireJsContextsConfig
     */
    private $addRequireJsContextsConfig;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var array|string[]
     */
    private $excludedInternalScripts;

    /**
     * @var array|string[]
     */
    private $excludedExternalScripts;

    /**
     * @param AddRequireJsContextsConfig $addRequireJsContextsConfig
     * @param Config $configHelper
     */
    public function __construct(
        AddRequireJsContextsConfig $addRequireJsContextsConfig,
        ConfigHelper               $configHelper
    ) {
        $this->addRequireJsContextsConfig = $addRequireJsContextsConfig;
        $this->configHelper = $configHelper;
    }

    /**
     * @return AddRequireJsContextsConfig
     */
    public function getAddRequireJsContextsConfigAction()
    {
        return $this->addRequireJsContextsConfig;
    }

    /**
     * @return array|string[]
     */
    public function getExcludedInternalScripts()
    {
        if ($this->excludedInternalScripts !== null) {
            return $this->excludedInternalScripts;
        }

        return $this->excludedInternalScripts = array_merge([
            'var BASE_URL =',
            'window.checkout =',
            'window.checkoutConfig ='
        ], $this->configHelper->getExcludeInternalScripts());
    }

    /**
     * @return array|string[]
     */
    public function getExcludedExternalScripts()
    {
        if ($this->excludedExternalScripts !== null) {
            return $this->excludedExternalScripts;
        }

        return $this->excludedExternalScripts = array_merge([
            'requirejs/require',
            'mage/requirejs/mixins',
            'mage/polyfill',
            'requirejs-min-resolver',
            'mage/requirejs/static',
            'mashiro/allowed_components',
            'mashiro/contexts_config',
            'mashiro/critical_',
        ], $this->configHelper->getExcludeExternalScripts());
    }
}
