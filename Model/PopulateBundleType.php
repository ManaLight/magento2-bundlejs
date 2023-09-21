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
    private $populateBundleType;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * PopulateBundleType constructor.
     * @param ActionPopulateBundleType $populateBundleType
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ActionPopulateBundleType $populateBundleType,
        ConfigHelper $configHelper
    ) {
        $this->populateBundleType = $populateBundleType;
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

        $result = $this->populateBundleType->execute();

        return [
            'success' => !!$result
        ];
    }
}
