<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use PureMashiro\BundleJs\Action\GenerateCriticalJsAssets;
use PureMashiro\BundleJs\Action\SaveBundleByPage as ActionSaveBundleByPage;
use PureMashiro\BundleJs\Action\SaveContextsConfig as ActionSaveContextsConfig;
use PureMashiro\BundleJs\Api\SaveBundleByPageInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class SaveBundleByPage implements SaveBundleByPageInterface
{
    /**
     * @var ActionSaveBundleByPage
     */
    private $saveBundleByPage;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var ActionSaveContextsConfig
     */
    private $saveContextsConfig;

    /**
     * @var GenerateCriticalJsAssets
     */
    private $genCritJsAssets;

    /**
     * SaveBundleByPage constructor.
     * @param ActionSaveBundleByPage $saveBundleByPage
     * @param ConfigHelper $configHelper
     * @param ActionSaveContextsConfig $saveContextsConfig
     * @param GenerateCriticalJsAssets $genCritJsAssets
     */
    public function __construct(
        ActionSaveBundleByPage   $saveBundleByPage,
        ConfigHelper             $configHelper,
        ActionSaveContextsConfig $saveContextsConfig,
        GenerateCriticalJsAssets $genCritJsAssets
    ) {
        $this->saveBundleByPage = $saveBundleByPage;
        $this->configHelper = $configHelper;
        $this->saveContextsConfig = $saveContextsConfig;
        $this->genCritJsAssets = $genCritJsAssets;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD)
     */
    public function execute(
        $fullActionName,
        $pathInfo,
        $bundle,
        $critical = false,
        $config = null,
        $area = null,
        $theme = null,
        $locale = null
    ) {
        if (!$this->configHelper->canCollectBundleJsInAction()) {
            return [
                'success' => false
            ];
        }

        if ($config !== '-1' && $area && $theme && $locale) {
            $this->saveContextsConfig->execute($config);
            $this->genCritJsAssets->execute($area, $theme, $locale, true);
        }

        return $this->saveBundleByPage->execute($fullActionName, $pathInfo, $bundle, $critical);
    }
}
