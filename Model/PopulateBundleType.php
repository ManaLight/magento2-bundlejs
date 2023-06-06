<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use PureMashiro\BundleJs\Action\PopulateBundleType as ActionPopulateBundleType;
use PureMashiro\BundleJs\Api\PopulateBundleTypeInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class PopulateBundleType implements PopulateBundleTypeInterface
{
    /**
     * @var ActionPopulateBundleType
     */
    private $actionPopulateBundleType;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * PopulateBundleType constructor.
     * @param ActionPopulateBundleType $actionPopulateBundleType
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ActionPopulateBundleType $actionPopulateBundleType,
        ConfigHelper $configHelper
    ) {
        $this->actionPopulateBundleType = $actionPopulateBundleType;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->configHelper->canCollectBundleJsInAction()) {
            return [
                'success' => false
            ];
        }

        $result = $this->actionPopulateBundleType->execute();
        if ($result) {
            return [
                'success' => true
            ];
        } else {
            return [
                'success' => false
            ];
        }
    }
}
