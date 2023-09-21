<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model\ResourceModel\BundleByType;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PureMashiro\BundleJs\Model\BundleByType as ModelBundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ModelBundleByType::class, ResourceBundleByType::class);
    }
}
