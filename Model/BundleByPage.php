<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use Magento\Framework\Model\AbstractModel;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;

class BundleByPage extends AbstractModel
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ResourceBundleByPage::class);
    }
}
