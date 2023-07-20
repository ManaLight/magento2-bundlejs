<?php
/*
 * Copyright Pure Mashiro. All rights reserved.
 * @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use PureMashiro\BundleJs\Model\BundleByPageFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;
use PureMashiro\BundleJs\Model\TypeMapper;

class UpdateDefaultBundleByTypes implements DataPatchInterface
{
    /**
     * @var BundleByPageFactory
     */
    private $bundleByPageFactory;

    /**
     * @var ResourceBundleByPage
     */
    private $resourceBundleByPage;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByTypeCollectionFactory;

    /**
     * @param BundleByPageFactory $bundleByPageFactory
     * @param ResourceBundleByPage $resourceBundleByPage
     * @param BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
     */
    public function __construct(
        BundleByPageFactory           $bundleByPageFactory,
        ResourceBundleByPage          $resourceBundleByPage,
        BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
    ) {
        $this->bundleByPageFactory = $bundleByPageFactory;
        $this->resourceBundleByPage = $resourceBundleByPage;
        $this->bundleByTypeCollectionFactory = $bundleByTypeCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            AddDefaultBundleByTypes::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Apply
     *
     * @return bool|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply()
    {
        $mapper = TypeMapper::MAPPER;
        foreach ($mapper as $fullActionName => $type) {
            $typeId = $this->getTypeId($type);
            if (empty($typeId)) {
                return false;
            }

            $pageBunde = $this->bundleByPageFactory->create();
            $pageBunde->setTypeId($typeId)
                ->setFullActionName($fullActionName)
                ->setUseInCommon(1)
                ->setEnable(1);

            $this->resourceBundleByPage->save($pageBunde);
        }
    }

    /**
     * Get Type Id.
     *
     * @param string $type
     * @return int|null
     */
    private function getTypeId($type)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByTypeCollectionFactory->create();
        $collection->addFieldToFilter('type', $type);
        return $collection->getSize() ? $collection->getFirstItem()->getEntityId() : null;
    }
}
