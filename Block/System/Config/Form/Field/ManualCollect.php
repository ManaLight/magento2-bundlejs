<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Block\System\Config\Form\Field;

use PureMashiro\BundleJs\Model\ManualCollect as ModelManualCollect;

class ManualCollect extends Collect
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setMode(ModelManualCollect::MODE);
    }
}
