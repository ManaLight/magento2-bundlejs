<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ContextsConfig extends AbstractDb
{
    public const TABLE_BUNDLE_CONTEXTS_CONFIG = 'mashiro_bundle_contexts_config';

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_BUNDLE_CONTEXTS_CONFIG, 'entity_id');
    }
}
