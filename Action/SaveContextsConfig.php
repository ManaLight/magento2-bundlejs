<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig as ResourceContextsConfig;
use PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig\CollectionFactory;

class SaveContextsConfig
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceContextsConfig
     */
    private $resourceContextsConfig;

    /**
     * Construct.
     *
     * @param CollectionFactory $collectionFactory
     * @param ResourceContextsConfig $resourceContextsConfig
     */
    public function __construct(
        CollectionFactory      $collectionFactory,
        ResourceContextsConfig $resourceContextsConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceContextsConfig = $resourceContextsConfig;
    }

    /**
     * Execute.
     *
     * @param string $config
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($config)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig\Collection $collection */
        $collection = $this->collectionFactory->create();
        $contextsConfig = $collection->getFirstItem();
        $contextsConfig->setConfig($config);
        $this->resourceContextsConfig->save($contextsConfig);
    }
}
