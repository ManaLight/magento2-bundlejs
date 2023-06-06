<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BundleByPage extends AbstractDb
{
    public const TABLE_NAME_BUNDLE_BY_PAGE = 'mashiro_bundle_by_page';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_BUNDLE_BY_PAGE, 'entity_id');
    }
}
