<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

namespace PureMashiro\BundleJs\Action;

use Magento\Framework\Serialize\SerializerInterface;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Model\BundleByPage;
use PureMashiro\BundleJs\Model\BundleByPageFactory;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\CollectionFactory as BundleByPageCollectionFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;
use PureMashiro\BundleJs\Model\TypeMapper;

class SaveBundleByPage
{
    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByTypeCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var BundleByPageFactory
     */
    private $bundleByPageFactory;

    /**
     * @var BundleByPageCollectionFactory
     */
    private $bundleByPageCollectionFactory;

    /**
     * @var ResourceBundleByPage
     */
    private $resourceBundleByPage;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var GetNextPage
     */
    private $getNextPage;

    /**
     * @var ResourceBundleByType
     */
    private $resourceBundleByType;

    /**
     * Construct.
     *
     * @param TypeMapper $typeMapper
     * @param BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
     * @param SerializerInterface $serializer
     * @param BundleByPageFactory $bundleByPageFactory
     * @param BundleByPageCollectionFactory $bundleByPageCollectionFactory
     * @param ResourceBundleByPage $resourceBundleByPage
     * @param ConfigHelper $configHelper
     * @param GetNextPage $getNextPage
     * @param ResourceBundleByType $resourceBundleByType
     */
    public function __construct(
        TypeMapper $typeMapper,
        BundleByTypeCollectionFactory $bundleByTypeCollectionFactory,
        SerializerInterface $serializer,
        BundleByPageFactory $bundleByPageFactory,
        BundleByPageCollectionFactory $bundleByPageCollectionFactory,
        ResourceBundleByPage $resourceBundleByPage,
        ConfigHelper $configHelper,
        GetNextPage $getNextPage,
        ResourceBundleByType $resourceBundleByType
    ) {
        $this->typeMapper = $typeMapper;
        $this->bundleByTypeCollectionFactory = $bundleByTypeCollectionFactory;
        $this->serializer = $serializer;
        $this->bundleByPageFactory = $bundleByPageFactory;
        $this->bundleByPageCollectionFactory = $bundleByPageCollectionFactory;
        $this->resourceBundleByPage = $resourceBundleByPage;
        $this->configHelper = $configHelper;
        $this->getNextPage = $getNextPage;
        $this->resourceBundleByType = $resourceBundleByType;
    }

    /**
     * Execute.
     *
     * @param string $fullActionName
     * @param string $pathInfo
     * @param array  $bundle
     * @param bool   $critical
     * @param bool   $merge
     * @return array|bool[]|false
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($fullActionName, $pathInfo, $bundle, $critical = false, $merge = false)
    {
        [$type, $path] = $this->typeMapper->map($fullActionName, $pathInfo, true);
        if (empty($type)) {
            return false;
        }

        $this->createEmptyBasicBundlesIfNotExists();

        $typeId = $this->getTypeId($type, $critical);
        if (empty($typeId)) {
            return false;
        }

        $pageBundle = $this->getPageBundle($typeId);

        /** @var BundleByPage $pageBundle */
        if ($merge) {
            $currentBundle = $pageBundle->getBundle();
            if ($currentBundle) {
                $currentBundle = $this->serializer->unserialize($currentBundle);
                $bundle = array_replace($currentBundle, $bundle);
            }
        }

        $bundle = $this->serializer->serialize($bundle);
        $pageBundle->setTypeId($typeId);
        $pageBundle->setFullActionName($path);
        $pageBundle->setBundle($bundle);
        $pageBundle->setUseInCommon(1);
        $pageBundle->setEnable(1);
        $this->resourceBundleByPage->save($pageBundle);

        if ($this->configHelper->canAutoCollect()) {
            return [
                'success' => true,
                'nextPage' => $this->getNextPage->execute($type, $critical)
            ];
        } else {
            return [
                'success' => true
            ];
        }
    }

    /**
     * Get Type Id.
     *
     * @param string $type
     * @param bool $critical
     * @return mixed
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function getTypeId($type, $critical = false)
    {
        if ($critical) {
            $type = 'critical_' . $type;
        }

        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByTypeCollectionFactory->create();
        $collection->addFieldToFilter('type', $type);

        if ($collection->getSize()) {
            return $collection->getFirstItem()->getEntityId();
        }

        $bundleType = $collection->getFirstItem();
        $bundleType->setType($type);
        $this->resourceBundleByType->save($bundleType);

        return $bundleType->getEntityId();
    }

    /**
     * Create Empty Basic Bundles If Not Exists.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function createEmptyBasicBundlesIfNotExists()
    {
        $mapper = TypeMapper::MAPPER;
        foreach ($mapper as $fullActionName => $type) {
            $typeId = $this->getTypeId($type);
            $pageBunde = $this->bundleByPageFactory->create();
            $this->resourceBundleByPage->load($pageBunde, $typeId, 'type_id');

            if ($pageBunde->getId()) {
                continue;
            }

            $pageBunde->setTypeId($typeId)
                ->setFullActionName($fullActionName)
                ->setUseInCommon(1)
                ->setEnable(1);

            $this->resourceBundleByPage->save($pageBunde);
        }
    }

    /**
     * Get Page Bundle.
     *
     * @param string $typeId
     * @return \Magento\Framework\DataObject|BundleByPage
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function getPageBundle($typeId)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPageCollectionFactory->create();
        $collection->addFieldToFilter('type_id', $typeId);
        if ($collection->getSize()) {
            $pageBundle = $collection->getFirstItem();
        } else {
            $pageBundle = $this->bundleByPageFactory->create();
            $pageBundle->setTypeId($typeId);
        }

        return $pageBundle;
    }
}
