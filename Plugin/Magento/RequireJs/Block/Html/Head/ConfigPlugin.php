<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Plugin\Magento\RequireJs\Block\Html\Head;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Block\Html\Head\Config as HeadConfig;
use Magento\Theme\Model\View\Design;
use PureMashiro\BundleJs\Action\GenerateCriticalJsAssets;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Model\BundleByType;
use PureMashiro\BundleJs\Model\FileManager;
use PureMashiro\BundleJs\Model\TypeMapper;
use PureMashiro\BundleJs\Model\Validator\IsAllowedStaticPage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigPlugin
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var AssetConfig
     */
    private $assetConfig;

    /**
     * @var GenerateCriticalJsAssets
     */
    private $generateCriticaL;

    /**
     * @var Design
     */
    private $design;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var IsAllowedStaticPage
     */
    private $isAllowedStaticPage;

    /**
     * @param FileManager $fileManager
     * @param PageConfig $pageConfig
     * @param Minification $minification
     * @param ConfigHelper $configHelper
     * @param AssetConfig $assetConfig
     * @param GenerateCriticalJsAssets $generateCriticaL
     * @param Design $design
     * @param FileDriver $fileDriver
     * @param RequestInterface $request
     * @param TypeMapper $typeMapper
     * @param IsAllowedStaticPage $isAllowedStaticPage
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FileManager              $fileManager,
        PageConfig               $pageConfig,
        Minification             $minification,
        ConfigHelper             $configHelper,
        AssetConfig              $assetConfig,
        GenerateCriticalJsAssets $generateCriticaL,
        Design                   $design,
        FileDriver               $fileDriver,
        RequestInterface         $request,
        TypeMapper               $typeMapper,
        IsAllowedStaticPage      $isAllowedStaticPage
    ) {
        $this->fileManager = $fileManager;
        $this->pageConfig = $pageConfig;
        $this->minification = $minification;
        $this->configHelper = $configHelper;
        $this->assetConfig = $assetConfig;
        $this->generateCriticaL = $generateCriticaL;
        $this->design = $design;
        $this->fileDriver = $fileDriver;
        $this->request = $request;
        $this->typeMapper = $typeMapper;
        $this->isAllowedStaticPage = $isAllowedStaticPage;
    }

    /**
     * Set After Layout
     *
     * @param HeadConfig $subject
     * @param mixed $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterSetLayout(HeadConfig $subject, $result)
    {
        if (!$this->isEnable()) {
            return $result;
        }

        $after = RequireJsConfig::REQUIRE_JS_FILE_NAME;
        if ($this->minification->isEnabled('js')) {
            $minResolver = $this->fileManager->createMinResolverAsset();
            $after = $minResolver->getFilePath();
        }
        $requireJsMapConfig = $this->fileManager->createRequireJsMapConfigAsset();
        if ($requireJsMapConfig) {
            $after = $requireJsMapConfig->getFilePath();
        }

        $assetCollection = $this->pageConfig->getAssetCollection();

        if ($this->configHelper->isDisableBundlesOnStaticPages()
            && $this->isAllowedStaticPage->validate($subject->getLayout())) {
            $this->insertCriticalJsAssets($assetCollection, $after);
        }
        
        if (!$this->configHelper->isDisableBundlesOnStaticPages()
        && !$this->isAllowedStaticPage->validate($subject->getLayout())) {
            $bundleAssets = $this->fileManager->createBundleJsPool();
            $staticAsset = $this->fileManager->createStaticJsAsset();
            /** @var \Magento\Framework\View\Asset\File $bundleAsset */
            if (!empty($bundleAssets) && $staticAsset !== false) {
                $bundleAssets = array_reverse($bundleAssets);
                foreach ($bundleAssets as $bundleAsset) {
                    $assetCollection->insert(
                        $bundleAsset->getFilePath(),
                        $bundleAsset,
                        $after
                    );
                }

                if ($this->isAllowedStaticPage->validate($subject->getLayout())) {
                    $after = reset($bundleAssets)->getFilePath();
                    $this->insertCriticalJsAssets($assetCollection, $after);
                }

                $assetCollection->insert(
                    $staticAsset->getFilePath(),
                    $staticAsset,
                    reset($bundleAssets)->getFilePath()
                );
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->configHelper->canBundleJsInStorefront() && !$this->assetConfig->isBundlingJsFiles();
    }

    /**
     * @param GroupedCollection $assetCollection
     * @param $after
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function insertCriticalJsAssets(GroupedCollection $assetCollection, $after)
    {
        /**
         * Reference \PureMashiro\BundleJs\Action\GetBundleTypes::execute
         */
        $fullActionName = $this->request->getFullActionName();
        $pathInfo = $this->request->getOriginalPathInfo();
        $type = $this->typeMapper->map($fullActionName, $pathInfo);
        $fileTypes = empty($type) ? [BundleByType::TYPE_COMMON] : [BundleByType::TYPE_COMMON, $type];

        $files = ['contexts_config.min.js'];

        $allowedJsComponents = $this->configHelper->getAllowedJsComponents();
        if (!empty($allowedJsComponents)) {
            $files[] = 'allowed_components.min.js';
        }

        foreach ($fileTypes as $fileType) {
            $files[] = "critical_$fileType.min.js";
        }

        $files = array_reverse($files);

        $designParams = $this->design->getDesignParams();
        $area = $designParams['area'];
        $theme = $designParams['themeModel']->getThemePath();
        $locale = $designParams['locale'];

        foreach ($files as $filePath) {
            $destination = $this->generateCriticaL->getFileDestination(false, $area, $theme, $locale, $filePath);
            if (!$this->fileDriver->isExists($destination)) {
                continue;
            }

            $criticalAsset = $this->fileManager->getCriticalJsAsset($filePath);
            $assetCollection->insert(
                $criticalAsset->getFilePath(),
                $criticalAsset,
                $after
            );
        }
    }
}
