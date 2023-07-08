<?php
/*
 * Copyright Pure Mashiro. All rights reserved.
 * @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Plugin\Magento\Framework\RequireJs;

use Magento\Framework\Code\Minifier\AdapterInterface as MinifyAdapter;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Minification;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Source\SourceJs;

class ConfigPlugin
{
    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var MinifyAdapter
     */
    private $minifyAdapter;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param Minification $minification
     * @param MinifyAdapter $minifyAdapter
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Minification  $minification,
        MinifyAdapter $minifyAdapter,
        ConfigHelper  $configHelper
    ) {
        $this->minification = $minification;
        $this->minifyAdapter = $minifyAdapter;
        $this->configHelper = $configHelper;
    }

    /**
     * After Get Config.
     *
     * @param RequireJsConfig $config
     * @param mixed $fullConfig
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(RequireJsConfig $config, $fullConfig)
    {
        if (!$this->configHelper->canDeferBundleJs()) {
            return $fullConfig;
        }

        $deferJs = $this->getDeferJs();

        if ($this->minification->isEnabled('js')) {
            $deferJs = $this->minifyAdapter->minify($deferJs);
        }

        return $fullConfig . $deferJs;
    }

    /**
     * Get Defer js.
     *
     * @return string
     */
    public function getDeferJs()
    {
        return SourceJs::DEFER_INNER_JS;
    }
}
