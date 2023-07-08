<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Block\System\Config\Form\Field;

use PureMashiro\BundleJs\Model\AutoCollect as ModelAutoCollect;

class AutoCollect extends Collect
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setMode(ModelAutoCollect::MODE);
    }
}
