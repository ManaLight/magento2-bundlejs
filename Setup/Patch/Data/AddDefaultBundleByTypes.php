<?php
/*
 * Copyright Pure Mashiro. All rights reserved.
 * @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\BundleByTypeFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;

class AddDefaultBundleByTypes implements DataPatchInterface
{
    /**
     * @var BundleByTypeFactory
     */
    private $bundleByTypeFactory;

    /**
     * @var ResourceBundleByType
     */
    private $resourceBundleByType;

    public function __construct(
        BundleByTypeFactory  $bundleByTypeFactory,
        ResourceBundleByType $resourceBundleByType
    ) {
        $this->bundleByTypeFactory = $bundleByTypeFactory;
        $this->resourceBundleByType = $resourceBundleByType;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddDefaultBundleByTypes|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply()
    {
        $this->addDefaultBundleByTypes();
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function addDefaultBundleByTypes()
    {
        /** @var BundleByType $bundle */
        $bundle = $this->bundleByTypeFactory->create();
        $types = $bundle->getDefaultTypes();

        foreach ($types as $type) {
            /** @var BundleByType $bundle */
            $bundle = $this->bundleByTypeFactory->create();
            $bundle->setType($type);
            $this->resourceBundleByType->save($bundle);
        }
    }
}
