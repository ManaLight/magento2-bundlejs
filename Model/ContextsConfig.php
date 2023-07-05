<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use Magento\Framework\Model\AbstractModel;
use PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig as ResourceContextsConfig;

class ContextsConfig extends AbstractModel
{
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ResourceContextsConfig::class);
    }
}
