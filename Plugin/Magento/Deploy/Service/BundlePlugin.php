<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Plugin\Magento\Deploy\Service;

use Magento\Deploy\Service\Bundle as MagentoBundle;
use Magento\Framework\App\Area;
use PureMashiro\BundleJs\Action\GenerateCriticalJsAssets;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\BundleRegistry;
use PureMashiro\BundleJs\Model\TypeMapper;
use PureMashiro\BundleJs\Service\Bundle as MashiroBundle;

class BundlePlugin
{
    /**
     * @var MashiroBundle
     */
    private $mashiroBundle;

    /**
     * @var BundleRegistry
     */
    private $bundleRegistry;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var GenerateCriticalJsAssets
     */
    private $generateCriticalJsAssets;

    /**
     * BundlePlugin constructor.
     * @param MashiroBundle $mashiroBundle
     * @param BundleRegistry $bundleRegistry
     * @param TypeMapper $typeMapper
     * @param ConfigHelper $configHelper
     * @param GenerateCriticalJsAssets $generateCriticalJsAssets
     */
    public function __construct(
        MashiroBundle $mashiroBundle,
        BundleRegistry $bundleRegistry,
        TypeMapper $typeMapper,
        ConfigHelper $configHelper,
        GenerateCriticalJsAssets $generateCriticalJsAssets
    ) {
        $this->mashiroBundle = $mashiroBundle;
        $this->bundleRegistry = $bundleRegistry;
        $this->typeMapper = $typeMapper;
        $this->configHelper = $configHelper;
        $this->generateCriticalJsAssets = $generateCriticalJsAssets;
    }

    /**
     * @param MagentoBundle $subject
     * @param $result
     * @param $area
     * @param $theme
     * @param $locale
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDeploy(MagentoBundle $subject, $result, $area, $theme, $locale)
    {
        if (!$this->configHelper->canGenerateStaticBundleJs() || $subject instanceof MashiroBundle || $area !== Area::AREA_FRONTEND) {
            return $result;
        }

        $types = array_values($this->typeMapper->getMapper());
        $types[] = BundleByType::TYPE_COMMON;

        $types[] = 'critical_' . BundleByType::TYPE_COMMON;
        $types[] = 'critical_' . BundleByType::TYPE_CMS;
        $types[] = 'critical_' . BundleByType::TYPE_CATEGORY;
        $types[] = 'critical_' . BundleByType::TYPE_PRODUCT;

        $types[] = 'noncritical_' . BundleByType::TYPE_COMMON;
        $types[] = 'noncritical_' . BundleByType::TYPE_CMS;
        $types[] = 'noncritical_' . BundleByType::TYPE_CATEGORY;
        $types[] = 'noncritical_' . BundleByType::TYPE_PRODUCT;

        foreach ($types as $type) {
            $this->bundleRegistry->startDeployAdvancedBundle($type);
            $this->mashiroBundle->deploy($area, $theme, $locale);
            $this->bundleRegistry->endDeployAdvancedBundle();
        }

        // generate critical js for each page type
        $this->generateCriticalJsAssets->execute($area, $theme, $locale);

        return $result;
    }
}
