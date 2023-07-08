<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BundleByType extends AbstractDb
{
    public const TABLE_NAME_BUNDLE_BY_TYPE = 'mashiro_bundle_by_type';

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_BUNDLE_BY_TYPE, 'entity_id');
    }
}
