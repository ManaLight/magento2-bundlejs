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
    private $actionSaveBundleByPage;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var ActionSaveContextsConfig
     */
    private $actionSaveContextsConfig;

    /**
     * @var GenerateCriticalJsAssets
     */
    private $generateCriticalJsAssets;

    /**
     * SaveBundleByPage constructor.
     * @param ActionSaveBundleByPage $actionSaveBundleByPage
     * @param ConfigHelper $configHelper
     * @param ActionSaveContextsConfig $actionSaveContextsConfig
     * @param GenerateCriticalJsAssets $generateCriticalJsAssets
     */
    public function __construct(
        ActionSaveBundleByPage   $actionSaveBundleByPage,
        ConfigHelper             $configHelper,
        ActionSaveContextsConfig $actionSaveContextsConfig,
        GenerateCriticalJsAssets $generateCriticalJsAssets
    ) {
        $this->actionSaveBundleByPage = $actionSaveBundleByPage;
        $this->configHelper = $configHelper;
        $this->actionSaveContextsConfig = $actionSaveContextsConfig;
        $this->generateCriticalJsAssets = $generateCriticalJsAssets;
    }

    /**
     * @inheritdoc
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
            $this->actionSaveContextsConfig->execute($config);
            $this->generateCriticalJsAssets->execute($area, $theme, $locale, true);
        }

        return $this->actionSaveBundleByPage->execute($fullActionName, $pathInfo, $bundle, $critical);
    }
}
