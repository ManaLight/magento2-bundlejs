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
    private $genCritJsAssets;

    /**
     * BundlePlugin constructor.
     * @param MashiroBundle $mashiroBundle
     * @param BundleRegistry $bundleRegistry
     * @param TypeMapper $typeMapper
     * @param ConfigHelper $configHelper
     * @param GenerateCriticalJsAssets $genCritJsAssets
     */
    public function __construct(
        MashiroBundle $mashiroBundle,
        BundleRegistry $bundleRegistry,
        TypeMapper $typeMapper,
        ConfigHelper $configHelper,
        GenerateCriticalJsAssets $genCritJsAssets
    ) {
        $this->mashiroBundle = $mashiroBundle;
        $this->bundleRegistry = $bundleRegistry;
        $this->typeMapper = $typeMapper;
        $this->configHelper = $configHelper;
        $this->genCritJsAssets = $genCritJsAssets;
    }

    /**
     * After Deploy.
     *
     * @param MagentoBundle $subject
     * @param mixed         $result
     * @param string        $area
     * @param string        $theme
     * @param string        $locale
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDeploy(MagentoBundle $subject, $result, $area, $theme, $locale)
    {
        // Check if static bundle generation is enabled and the current subject is not MashiroBundle
        if (!$this->configHelper->canGenerateStaticBundleJs()
            || $subject instanceof MashiroBundle
            || $area !== Area::AREA_FRONTEND
        ) {
            return $result;
        }

        // Define the types of bundles to deploy
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

        // Deploy bundles for each type
        foreach ($types as $type) {
            $this->bundleRegistry->startDeployAdvancedBundle($type);
            $this->mashiroBundle->deploy($area, $theme, $locale);
            $this->bundleRegistry->endDeployAdvancedBundle();
        }

        // Generate critical js for each page type
        $this->genCritJsAssets->execute($area, $theme, $locale);

        return $result;
    }
}
