<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogImportExport\Model\Export\Product\Stock;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use PureMashiro\BundleJs\Model\AutoCollect;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage\CollectionFactory as BundleByPageCollectionFactory;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NextPage
{
    public const CMS_PATH = '';
    public const CATEGORY_PATH = 'catalog/category/view';
    public const PRODUCT_PATH = 'catalog/product/view';
    public const CART_PATH = 'checkout/cart/index';
    public const CHECKOUT_PATH = 'checkout/index/index';
    public const COLLECT_BUNDLEJS_PATH = 'bundlejs/collect/index';

    public const DEFAULT_PATHS = [
        BundleByType::TYPE_CMS => self::CMS_PATH,
        BundleByType::TYPE_CATEGORY => self::CATEGORY_PATH,
        BundleByType::TYPE_PRODUCT => self::PRODUCT_PATH,
        BundleByType::TYPE_CART => self::CART_PATH,
        BundleByType::TYPE_CHECKOUT => self::CHECKOUT_PATH
    ];

    /**
     * @var BundleByPageCollectionFactory
     */
    private $bundleByPage;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollection;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollection;

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerCart
     */
    private $cart;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * NextPage constructor.
     *
     * @param BundleByPageCollectionFactory $bundleByPage
     * @param CategoryCollectionFactory $categoryCollection
     * @param ProductCollectionFactory $productCollection
     * @param Stock $stockHelper
     * @param CheckoutSession $checkoutSession
     * @param CheckoutHelper $checkoutHelper
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param CustomerCart $cart
     * @param Config $configHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        BundleByPageCollectionFactory $bundleByPage,
        CategoryCollectionFactory $categoryCollection,
        ProductCollectionFactory $productCollection,
        Stock $stockHelper,
        CheckoutSession $checkoutSession,
        CheckoutHelper $checkoutHelper,
        CustomerSession $customerSession,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CustomerCart $cart,
        ConfigHelper $configHelper
    ) {
        $this->bundleByPage = $bundleByPage;
        $this->categoryCollection = $categoryCollection;
        $this->productCollection = $productCollection;
        $this->stockHelper = $stockHelper;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->configHelper = $configHelper;
    }

    /**
     * Get Next Type.
     *
     * @param string $type
     * @return bool|null
     */
    public function getNextType($type)
    {
        $collection = $this->bundleByPage->create();
        $collection->getSelect()->join(
            ['type' => $collection->getTable(ResourceBundleByType::TABLE_NAME_BUNDLE_BY_TYPE)],
            'main_table.type_id = type.entity_id AND type.type NOT LIKE "critical_%"',
            'type'
        );
        $bundles = $collection->getItems();
        if (empty($bundles)) {
            return null;
        }

        while (($bundle = isset($bundle) ? next($bundles) : current($bundles)) !== false) {
            if ($bundle->getType() === $type) {
                $bundle = next($bundles);
                return $bundle === false ? false : $bundle->getType();
            }
        }

        return null;
    }

    /**
     * Get default page.
     *
     * @param string $type
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDefaultPage($type)
    {
        switch ($type) {
            case BundleByType::TYPE_CMS:
                return self::DEFAULT_PATHS[$type];

            case BundleByType::TYPE_CATEGORY:
                $configPath = $this->configHelper->getAutoCollectPath($type);

                if (!empty($configPath)) {
                    return $configPath;
                }

                $entityId = $this->getActiveCategoryId();
                return empty($entityId) ? null : self::DEFAULT_PATHS[$type] . '/id/' . $entityId;

            case BundleByType::TYPE_PRODUCT:
                $configPath = $this->configHelper->getAutoCollectPath($type);

                if (!empty($configPath)) {
                    return $configPath;
                }

                $entityId = $this->getActiveProductId();
                return empty($entityId) ? null : self::DEFAULT_PATHS[$type] . '/id/' . $entityId;

            case BundleByType::TYPE_CART:
                $entityId = $this->getActiveProductId(true);
                $this->addProductToCart($entityId);
                return self::DEFAULT_PATHS[$type];

            case BundleByType::TYPE_CHECKOUT:
                if ($this->canCheckout()) {
                    $configPath = $this->configHelper->getAutoCollectPath($type);
                    if (!empty($configPath)) {
                        return $configPath;
                    }

                    return self::DEFAULT_PATHS[$type];
                }

                return self::COLLECT_BUNDLEJS_PATH . '/auto_collect/' . AutoCollect::STATE_CANCELED;
        }

        return null;
    }

    /**
     * Get complete page.
     *
     * @return string
     */
    public function getCompletePage()
    {
        return self::COLLECT_BUNDLEJS_PATH . '/auto_collect/' . AutoCollect::STATE_COMPLETE;
    }

    /**
     * Get phase 2 page.
     *
     * @return string
     */
    public function getPhase2Page()
    {
        return self::COLLECT_BUNDLEJS_PATH . '/auto_collect/' . AutoCollect::PHASE_VALUE_2;
    }

    /**
     * Empty customer's shopping cart
     *
     * @return void
     */
    public function emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
        } catch (\Exception $e) {
            $this->logger->critical(get_class($this) . '::' . __FUNCTION__ . ' ' . $e->getMessage());
        }
    }

    /**
     * Get active category id.
     *
     * @return mixed
     */
    public function getActiveCategoryId()
    {
        $categoryCollection = $this->categoryCollection->create();
        $categoryCollection->addFieldToSelect(['is_active', 'children_count']);
        $categoryCollection->addFieldToFilter('is_active', 1);
        $categoryCollection->addFieldToFilter('children_count', ['gt' => 0]);
        $category = $categoryCollection->getLastItem();
        return $category->getId();
    }

    /**
     * Get Active Product Id.
     *
     * @param bool $canAddToCart
     * @return mixed
     *
     * @SuppressWarnings(PHPMD)
     */
    public function getActiveProductId($canAddToCart = false)
    {
        $productCollection = $this->productCollection->create();
        $storeId = $this->storeManager->getStore()->getId();

        $productCollection
            ->addFieldToSelect(['status', 'visibility'])
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('visibility', ['in' => [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG]])
            ->addStoreFilter($storeId);
        $this->stockHelper->addInStockFilterToCollection($productCollection);

        if (!$canAddToCart) {
            $productId = $this->getActiveConfigurableProductId($productCollection);
            if (!empty($productId)) {
                return $productId;
            }
        }

        if ($canAddToCart) {
            $productCollection
                ->addFieldToFilter('type_id', [ProductType::DEFAULT_TYPE, ProductType::TYPE_VIRTUAL])
                ->addFieldToFilter('required_options', 0);
        }

        $product = $productCollection->getLastItem();
        return $product->getId();
    }

    /**
     * Get Active Configurable Product Id.
     *
     * @param ProductCollectionFactory $productCollection
     * @return mixed
     */
    public function getActiveConfigurableProductId($productCollection)
    {
        $collection = clone $productCollection;
        $collection->addFieldToFilter('type_id', Configurable::TYPE_CODE);
        $product = $collection->getLastItem();
        return $product->getId();
    }

    /**
     * Can Checkout.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canCheckout()
    {
        try {
            if (!$this->checkoutHelper->canOnepageCheckout()) {
                return false;
            }

            $quote = $this->checkoutSession->getQuote();
            if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
                return false;
            }

            if (!$this->customerSession->isLoggedIn() && !$this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->critical(get_class($this) . '::' . __FUNCTION__ . ' ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Add Product to cart.
     *
     * @param int $productId
     */
    public function addProductToCart($productId)
    {
        try {
            $this->cart->addProductsByIds([$productId]);
            $this->cart->save();
        } catch (\Exception $e) {
            $this->logger->critical(get_class($this) . '::' . __FUNCTION__ . ' ' . $e->getMessage());
        }
    }
}
