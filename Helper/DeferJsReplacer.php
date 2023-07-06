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
    private $addRequireJs;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var array|string[]
     */
    private $excludedInternal;

    /**
     * @var array|string[]
     */
    private $excludedExternal;

    /**
     * @param AddRequireJsContextsConfig $addRequireJs
     * @param Config $configHelper
     */
    public function __construct(
        AddRequireJsContextsConfig $addRequireJs,
        ConfigHelper               $configHelper
    ) {
        $this->addRequireJs = $addRequireJs;
        $this->configHelper = $configHelper;
    }

    /**
     * Get Add Require Js Contexts Config Action.
     *
     * @return AddRequireJsContextsConfig
     */
    public function getAddRequireJsContextsConfigAction()
    {
        return $this->addRequireJs;
    }

    /**
     * Get Excluded Internal Scripts.
     *
     * @return array|string[]
     */
    public function getExcludedInternalScripts()
    {
        if ($this->excludedInternal !== null) {
            return $this->excludedInternal;
        }

        return $this->excludedInternal = array_merge([
            'var BASE_URL =',
            'window.checkout =',
            'window.checkoutConfig ='
        ], $this->configHelper->getExcludeInternalScripts());
    }

    /**
     * Get Excluded External Scripts.
     *
     * @return array|string[]
     */
    public function getExcludedExternalScripts()
    {
        if ($this->excludedExternal !== null) {
            return $this->excludedExternal;
        }

        return $this->excludedExternal = array_merge([
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
