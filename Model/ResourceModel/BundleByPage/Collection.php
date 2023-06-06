<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel\BundleByPage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PureMashiro\BundleJs\Model\BundleByPage as ModelBundleByPage;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ModelBundleByPage::class, ResourceBundleByPage::class);
    }
}
