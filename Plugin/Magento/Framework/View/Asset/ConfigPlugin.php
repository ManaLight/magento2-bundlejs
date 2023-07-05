<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Plugin\Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\Config as AssetConfig;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class ConfigPlugin
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * After Is Merge Js Files.
     *
     * @param AssetConfig  $subject
     * @param mixed        $result
     * @return false|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMergeJsFiles(AssetConfig $subject, $result)
    {
        return $this->configHelper->canBundleJsInStorefront()
            || $this->configHelper->canCollectBundleJs() ? false : $result;
    }
}
