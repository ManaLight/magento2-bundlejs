<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use PureMashiro\BundleJs\Action\ClearBundles as ActionClearBundles;
use PureMashiro\BundleJs\Api\ClearBundlesInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class ClearBundles implements ClearBundlesInterface
{
    /**
     * @var ActionClearBundles
     */
    private $actionClearBundles;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * ClearBundles constructor.
     * @param ActionClearBundles $actionClearBundles
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ActionClearBundles $actionClearBundles,
        ConfigHelper $configHelper
    ) {
        $this->actionClearBundles = $actionClearBundles;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->configHelper->canCollectBundleJsInAction()) {
            return false;
        }

        return $this->actionClearBundles->execute();
    }
}
