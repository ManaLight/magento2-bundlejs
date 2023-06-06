<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use Magento\Framework\Serialize\SerializerInterface;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\BundleByTypeFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\CollectionFactory as BundleByPageCollectionFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class PopulateBundleType
{
    /**
     * @var BundleByPageCollectionFactory
     */
    private $bundleByPageCollectionFactory;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByTypeCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ResourceBundleByType
     */
    private $resourceBundleByType;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var BundleByTypeFactory
     */
    private $bundleByTypeFactory;

    private $allBundleByTypes = null;

    /**
     * PopulateBundleType constructor.
     * @param BundleByPageCollectionFactory $bundleByPageCollectionFactory
     * @param BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
     * @param SerializerInterface $serializer
     * @param ResourceBundleByType $resourceBundleByType
     * @param ConfigHelper $configHelper
     * @param BundleByTypeFactory $bundleByTypeFactory
     */
    public function __construct(
        BundleByPageCollectionFactory $bundleByPageCollectionFactory,
        BundleByTypeCollectionFactory $bundleByTypeCollectionFactory,
        SerializerInterface           $serializer,
        ResourceBundleByType          $resourceBundleByType,
        ConfigHelper                  $configHelper,
        BundleByTypeFactory           $bundleByTypeFactory
    ) {
        $this->bundleByPageCollectionFactory = $bundleByPageCollectionFactory;
        $this->bundleByTypeCollectionFactory = $bundleByTypeCollectionFactory;
        $this->serializer = $serializer;
        $this->resourceBundleByType = $resourceBundleByType;
        $this->configHelper = $configHelper;
        $this->bundleByTypeFactory = $bundleByTypeFactory;
    }

    /**
     * @param false $critical
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($critical = false)
    {
        $allTypeIds = $this->getAllBundleTypeIds($critical);

        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPageCollectionFactory->create();
        $collection
            ->addFieldToFilter('type_id', ['in' => $allTypeIds])
            ->addFieldToFilter('use_in_common', 1)
            ->addFieldToFilter('enable', 1);

        $collection->getSelect()->where('main_table.bundle IS NOT NULL');

        $collection->getSelect()->join(
            ['type' => $collection->getTable(ResourceBundleByType::TABLE_NAME_BUNDLE_BY_TYPE)],
            'main_table.type_id = type.entity_id',
            'type.type'
        )->group('main_table.entity_id');

        if (!$collection->getSize()) {
            return false;
        }

        $bundles = [];
        foreach ($collection as $pageBundle) {
            $bundleContent = $pageBundle->getBundle();
            $bundle = $this->serializer->unserialize($bundleContent);
            $bundles[] = $bundle;
        }
        $common = count($bundles) > 1 ? array_intersect(...$bundles) : $bundles[0];

        $bundleByTypes = [];
        $bundleByTypes[$critical ? 'critical_common' : 'common'] = $this->serializer->serialize($common);

        foreach ($collection as $pageBundle) {
            $bundleContent = $pageBundle->getBundle();
            $bundle = $this->serializer->unserialize($bundleContent);
            $type = $pageBundle->getType();
            $bundleByTypes[$type] = $this->serializer->serialize(array_diff($bundle, $common));
        }

        $this->saveBundleTypes($bundleByTypes);
        if ($critical) {
            $this->populateNonCriticalBundles();
        }

        return true;
    }

    /**
     * @param false $critical
     * @return array
     */
    public function getAllBundleTypeIds($critical = false)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByTypeCollectionFactory->create();

        if ($critical) {
            $collection->addFieldToFilter('type', ['like' => 'critical_%']);
        } else {
            $collection->addFieldToFilter('type', ['nlike' => 'critical_%']);
        }

        return $collection->getAllIds();
    }

    /**
     * @param $bundles
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveBundleTypes($bundles)
    {
        foreach ($bundles as $type => $bundle) {
            $bundleType = $this->getBundleTypeByName($type);
            if (!empty($bundleType)) {
                $bundleType->setBundle($bundle);
                $this->resourceBundleByType->save($bundleType);
            }
        }
    }

    /**
     * @param $typeName
     * @return \Magento\Framework\DataObject|mixed|null
     */
    public function getBundleTypeByName($typeName)
    {
        $bundleTypes = $this->getAllBundleByTypes();
        foreach ($bundleTypes as $bundleType) {
            if ($bundleType->getType() === $typeName) {
                return $bundleType;
            }
        }

        $bundleType = $this->bundleByTypeFactory->create();
        $bundleType->setType($typeName);

        return $bundleType;
    }

    /**
     * @return \Magento\Framework\DataObject[]|null
     */
    public function getAllBundleByTypes()
    {
        if ($this->allBundleByTypes !== null) {
            return $this->allBundleByTypes;
        }

        $collection = $this->bundleByTypeCollectionFactory->create();
        return $this->allBundleByTypes = $collection->getItems();
    }

    /**
     * @return false|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function populateNonCriticalBundles()
    {
        $nonCriticalBundleTypes = $this->getNonCriticalBundleTypes();

        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPageCollectionFactory->create();
        $collection->addFieldToFilter('enable', 1);
        $collection->getSelect()->join(
            ['type' => $collection->getTable(ResourceBundleByType::TABLE_NAME_BUNDLE_BY_TYPE)],
            'main_table.type_id = type.entity_id',
            'type.type'
        )->group('main_table.entity_id');

        if (!$collection->getSize()) {
            return false;
        }

        $bundleByTypes = [];
        foreach ($collection as $pageBundle) {
            $bundleContent = $pageBundle->getBundle();
            $bundle = empty($bundleContent) ? [] : $this->serializer->unserialize($bundleContent);
            $type = $pageBundle->getType();
            $bundleByTypes[$type] = $bundle;
        }

        foreach ($nonCriticalBundleTypes as $type) {
            $criticalType = 'critical_' . $type;
            $nonCriticalType = 'noncritical_' . $type;

            if (!isset($bundleByTypes[$type]) || !isset($bundleByTypes[$criticalType])) {
                continue;
            }

            $bundleByTypes[$nonCriticalType] = array_diff($bundleByTypes[$type], $bundleByTypes[$criticalType]);

            // save non critical bundle type
            $bundleType = $this->bundleByTypeFactory->create();
            $this->resourceBundleByType->load($bundleType, $nonCriticalType, 'type');
            $bundleType->setType($nonCriticalType);
            $bundleType->setBundle($this->serializer->serialize($bundleByTypes[$nonCriticalType]));
            $this->resourceBundleByType->save($bundleType);
        }
    }

    /**
     * @return array
     */
    public function getNonCriticalBundleTypes()
    {
        return BundleByType::CRITICAL_TYPES;
    }
}
