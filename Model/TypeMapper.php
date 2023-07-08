<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

use PureMashiro\BundleJs\Helper\Config as ConfigHelper;

class TypeMapper
{
    public const CMS_INDEX_INDEX = 'cms_index_index';
    public const CATALOG_CATEGORY_VIEW = 'catalog_category_view';
    public const CATALOG_PRODUCT_VIEW = 'catalog_product_view';
    public const CHECKOUT_CART_INDEX = 'checkout_cart_index';
    public const CHECKOUT_INDEX_INDEX = 'checkout_index_index';

    public const MAPPER = [
        self::CMS_INDEX_INDEX => BundleByType::TYPE_CMS,
        self::CATALOG_CATEGORY_VIEW => BundleByType::TYPE_CATEGORY,
        self::CATALOG_PRODUCT_VIEW => BundleByType::TYPE_PRODUCT,
        self::CHECKOUT_CART_INDEX => BundleByType::TYPE_CART,
        self::CHECKOUT_INDEX_INDEX => BundleByType::TYPE_CHECKOUT
    ];

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * TypeMapper constructor.
     *
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Map.
     *
     * @param string $fullActionName
     * @param string $pathInfo
     * @param bool   $full
     * @return mixed|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD)
     * @TODO: refactor into new functions to reduce the complexity of this one.
     */
    public function map($fullActionName, $pathInfo, $full = false)
    {
        $mapper = $this->getMapper();
        $pathInfo = trim($pathInfo, ' /');

        if (!empty($pathInfo)) {
            if (isset(self::MAPPER[$fullActionName]) && self::MAPPER[$fullActionName] === $pathInfo) {
                $type = $pathInfo;
                return $full ? [$type, $fullActionName] : $type;
            }

            foreach ($mapper as $path => $type) {
                if (strpos($pathInfo, $path) !== false || strpos($path, $pathInfo) !== false) {
                    return $full ? [$type, $path] : $type;
                }
            }
        }

        $type = self::MAPPER[$fullActionName] ?? null;
        return $full ? [$type, $fullActionName] : $type;
    }

    /**
     * Get Mapper.
     *
     * @return array
     */
    public function getMapper()
    {
        $mapper = [];
        $types = array_values(self::MAPPER);

        foreach ($types as $type) {
            $configPath = $this->configHelper->getAutoCollectPath($type);
            if (!empty($configPath)) {
                $mapper[$configPath] = $type;
            }

            if (empty($configPath)) {
                $key = array_search($type, self::MAPPER);
                if (empty($key)) {
                    continue;
                }

                $mapper[$key] = $type;
            }
        }

        return $mapper;
    }
}
