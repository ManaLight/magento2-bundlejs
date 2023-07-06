<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use Magento\Framework\Model\AbstractModel;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;

class BundleByType extends AbstractModel
{
    public const TYPE_COMMON = 'common';
    public const TYPE_CMS = 'cms';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_CART = 'cart';
    public const TYPE_CHECKOUT = 'checkout';

    public const DEFAULT_TYPES = [
        self::TYPE_COMMON,
        self::TYPE_CMS,
        self::TYPE_CATEGORY,
        self::TYPE_PRODUCT,
        self::TYPE_CART,
        self::TYPE_CHECKOUT
    ];

    public const CRITICAL_TYPES = [
        self::TYPE_COMMON,
        self::TYPE_CMS,
        self::TYPE_CATEGORY,
        self::TYPE_PRODUCT
    ];

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceBundleByType::class);
    }

    /**
     * Get Default Types.
     *
     * @return string[]
     */
    public function getDefaultTypes()
    {
        return self::DEFAULT_TYPES;
    }
}
