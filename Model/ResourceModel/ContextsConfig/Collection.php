<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PureMashiro\BundleJs\Model\ContextsConfig as ModelContextsConfig;
use PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig as ResourceContextsConfig;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ModelContextsConfig::class, ResourceContextsConfig::class);
    }
}
