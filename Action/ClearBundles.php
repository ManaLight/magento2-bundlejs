<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\CollectionFactory as BundleByPageCollectionFactory;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;

class ClearBundles
{
    /**
     * @var ResourceBundleByPage
     */
    private $resourceBundleByPage;

    /**
     * @var ResourceBundleByType
     */
    private $resourceBundleByType;

    /**
     * @var BundleByPageCollectionFactory
     */
    private $bundleByPageCollectionFactory;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByTypeCollectionFactory;

    /**
     * ClearBundles constructor.
     *
     * @param ResourceBundleByPage $resourceBundleByPage
     * @param ResourceBundleByType $resourceBundleByType
     * @param BundleByPageCollectionFactory $bundleByPageCollectionFactory
     * @param BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
     */
    public function __construct(
        ResourceBundleByPage $resourceBundleByPage,
        ResourceBundleByType $resourceBundleByType,
        BundleByPageCollectionFactory $bundleByPageCollectionFactory,
        BundleByTypeCollectionFactory $bundleByTypeCollectionFactory
    ) {
        $this->resourceBundleByPage = $resourceBundleByPage;
        $this->resourceBundleByType = $resourceBundleByType;
        $this->bundleByPageCollectionFactory = $bundleByPageCollectionFactory;
        $this->bundleByTypeCollectionFactory = $bundleByTypeCollectionFactory;
    }

    /**
     * Execute.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $this->clearBundleByPages();
        $this->clearBundleByTypes();
        return true;
    }

    /**
     * Clear Bundle By Pages.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function clearBundleByPages()
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\Collection $collection */
        $collection = $this->bundleByPageCollectionFactory->create();
        if ($collection->getSize()) {
            foreach ($collection as $bundle) {
                $bundle->setBundle(null);
                $this->resourceBundleByPage->save($bundle);
            }
        }
    }

    /**
     * Clear Bundle By Types.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function clearBundleByTypes()
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByTypeCollectionFactory->create();
        if ($collection->getSize()) {
            foreach ($collection as $bundle) {
                $bundle->setBundle(null);
                $this->resourceBundleByType->save($bundle);
            }
        }
    }
}
