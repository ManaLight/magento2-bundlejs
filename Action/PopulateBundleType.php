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
    private $bundleByPage;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByType;

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

    /**
     * @var string|null
     */
    private $allBundleByTypes = null;

    /**
     * PopulateBundleType constructor.
     * @param BundleByPageCollectionFactory $bundleByPage
     * @param BundleByTypeCollectionFactory $bundleByType
     * @param SerializerInterface $serializer
     * @param ResourceBundleByType $resourceBundleByType
     * @param ConfigHelper $configHelper
     * @param BundleByTypeFactory $bundleByTypeFactory
     */
    public function __construct(
        BundleByPageCollectionFactory $bundleByPage,
        BundleByTypeCollectionFactory $bundleByType,
        SerializerInterface           $serializer,
        ResourceBundleByType          $resourceBundleByType,
        ConfigHelper                  $configHelper,
        BundleByTypeFactory           $bundleByTypeFactory
    ) {
        $this->bundleByPage = $bundleByPage;
        $this->bundleByType = $bundleByType;
        $this->serializer = $serializer;
        $this->resourceBundleByType = $resourceBundleByType;
        $this->configHelper = $configHelper;
        $this->bundleByTypeFactory = $bundleByTypeFactory;
    }

    /**
     * Execute.
     *
     * @param bool $critical
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     *
     * @SuppressWarnings(PHPMD)
     */
    public function execute($critical = false)
    {
        $allTypeIds = $this->getAllBundleTypeIds($critical);

        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPage->create();
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
     * Get All Bundle Type Ids.
     *
     * @param bool $critical
     * @return array
     *
     * @SuppressWarnings(PHPMD)
     */
    public function getAllBundleTypeIds($critical = false)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByType->create();

        if ($critical) {
            $collection->addFieldToFilter('type', ['like' => 'critical_%']);
        }
        
        if (!$critical) {
            $collection->addFieldToFilter('type', ['nlike' => 'critical_%']);
        }

        return $collection->getAllIds();
    }

    /**
     * Save Bundle Types.
     *
     * @param array $bundles
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
     * Get Bundle Type By Name.
     *
     * @param string $typeName
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
     * Get All Bundle By Types.
     *
     * @return \Magento\Framework\DataObject[]|null
     */
    public function getAllBundleByTypes()
    {
        if ($this->allBundleByTypes !== null) {
            return $this->allBundleByTypes;
        }

        $collection = $this->bundleByType->create();
        return $this->allBundleByTypes = $collection->getItems();
    }

    /**
     * Populate Non Critical Bundles.
     *
     * @return bool|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function populateNonCriticalBundles()
    {
        $nonCritical = $this->getNonCriticalBundleTypes();

        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPage->create();
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

        foreach ($nonCritical as $type) {
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
     * Get Non Critical Bundle Types.
     *
     * @return array
     */
    public function getNonCriticalBundleTypes()
    {
        return BundleByType::CRITICAL_TYPES;
    }
}
