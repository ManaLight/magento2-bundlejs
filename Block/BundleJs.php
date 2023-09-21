<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Block;

use Magento\Framework\View\Element\Template;
use Magento\Theme\Model\View\Design;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Helper\NextPage;
use PureMashiro\BundleJs\Model\AutoCollect;
use PureMashiro\BundleJs\Model\ManualCollect;
use PureMashiro\BundleJs\Model\BundleByType as ModelBundleByType;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByPage as ResourceBundleByPage;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType as ResourceBundleByType;
use PureMashiro\BundleJs\Model\TypeMapper;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;

class BundleJs extends Template
{
    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByType;

    /**
     * @var Design
     */
    private $design;

    /**
     * BundleJs constructor.
     * @param Template\Context $context
     * @param TypeMapper $typeMapper
     * @param ConfigHelper $configHelper
     * @param BundleByTypeCollectionFactory $bundleByType
     * @param Design $design
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        TypeMapper $typeMapper,
        ConfigHelper $configHelper,
        BundleByTypeCollectionFactory $bundleByType,
        Design $design,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->typeMapper = $typeMapper;
        $this->configHelper = $configHelper;
        $this->bundleByType = $bundleByType;
        $this->design = $design;
    }

    /**
     * Can Collect Bundle.
     *
     * @return bool
     */
    public function canCollectBundle()
    {
        if (!$this->configHelper->canCollectBundleJs()) {
            return false;
        }

        $fullActionName = $this->getRequest()->getFullActionName();
        $pathInfo = $this->getRequest()->getOriginalPathInfo();
        return !!$this->typeMapper->map($fullActionName, $pathInfo);
    }

    /**
     * Can Auto Collect.
     *
     * @return bool
     */
    public function canAutoCollect()
    {
        return $this->configHelper->canCollectBundleJs() && $this->configHelper->canAutoCollect();
    }

    /**
     * Get Auto Collect data
     *
     * @return array
     */
    public function getAutoCollectData()
    {
        return [
            'identifier'    => AutoCollect::IDENTIFIER,
            'phase'         => AutoCollect::PHASE,
            'phase_value_2' => AutoCollect::PHASE_VALUE_2
        ];
    }

    /**
     * Get Manual Collect data
     *
     * @return array
     */
    public function getManualCollectData()
    {
        return [
            'identifier'    => ManualCollect::IDENTIFIER
        ];
    }

    /**
     * Get All Bundle Types.
     *
     * @return ResourceBundleByType\Collection
     */
    public function getAllBundleTypes()
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByType->create();
        $collection->addFieldToFilter('type', ['neq' => ModelBundleByType::TYPE_COMMON]);
        $collection->getSelect()->joinLeft(
            ['page' => $collection->getTable(ResourceBundleByPage::TABLE_NAME_BUNDLE_BY_PAGE)],
            'main_table.entity_id = page.type_id',
            ['page_bundle' => 'page.bundle']
        );

        return $collection;
    }

    /**
     * Is Commoin Exist.
     *
     * @return bool
     */
    public function isCommonExist(): bool
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByType->create();
        $collection->addFieldToFilter('type', ModelBundleByType::TYPE_COMMON);

        if (!$collection->getSize()) {
            return false;
        }

        return !empty($collection->getLastItem()->getBundle());
    }

    /**
     * Is Page Matched
     *
     * @param string $type
     * @return bool
     */
    public function isPageMatched($type)
    {
        $fullActionName = $this->getRequest()->getFullActionName();
        $pathInfo = $this->getRequest()->getOriginalPathInfo();
        return $type === $this->typeMapper->map($fullActionName, $pathInfo);
    }

    /**
     * Has Empty Bundle Page.
     *
     * @param string $types
     * @return bool
     */
    public function hasEmptyBundlePage($types)
    {
        $hasEmptyBundlePage = false;

        foreach ($types as $type) {
            if ($hasEmptyBundlePage) {
                continue;
            }

            if (!$hasEmptyBundlePage) {
                if (empty($type->getPageBundle())) {
                    $hasEmptyBundlePage = true;
                }
            }
        }

        return $hasEmptyBundlePage;
    }

    /**
     * Get Auto Collect Cancelling Url.
     *
     * @return string
     */
    public function getAutoCollectCancellingUrl()
    {
        return $this->getUrl(NextPage::COLLECT_BUNDLEJS_PATH, ['auto_collect' => AutoCollect::STATE_CANCELED]);
    }

    /**
     * Get Manual Collect Cancelling Url.
     *
     * @return string
     */
    public function getManualCollectCancellingUrl()
    {
        return $this->getUrl(NextPage::COLLECT_BUNDLEJS_PATH, ['manual_collect' => AutoCollect::STATE_CANCELED]);
    }

    /**
     * Get Design Params.
     *
     * @return array
     */
    public function getDesignParams()
    {
        return $this->design->getDesignParams();
    }
}
