<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action\Collect;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\View\Deployment\Version\StorageInterface as DeploymentVersionStorage;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\View\Design;
use PureMashiro\BundleJs\Action\GenerateCriticalJsAssets;
use PureMashiro\BundleJs\Model\AutoCollect;

class AddRequireJsContextsConfig
{
    /**
     * @var DeploymentVersionStorage
     */
    private $deployVersionStorage;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Design
     */
    private $design;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var GenerateCriticalJsAssets
     */
    private $genCritJsAssets;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @param DeploymentVersionStorage $deployVersionStorage
     * @param StoreManagerInterface $storeManager
     * @param Design $design
     * @param CacheInterface $cache
     * @param GenerateCriticalJsAssets $genCritJsAssets
     * @param FileDriver $fileDriver
     */
    public function __construct(
        DeploymentVersionStorage $deployVersionStorage,
        StoreManagerInterface    $storeManager,
        Design                   $design,
        CacheInterface           $cache,
        GenerateCriticalJsAssets $genCritJsAssets,
        FileDriver               $fileDriver
    ) {
        $this->deployVersionStorage = $deployVersionStorage;
        $this->storeManager = $storeManager;
        $this->design = $design;
        $this->cache = $cache;
        $this->genCritJsAssets = $genCritJsAssets;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Execute.
     *
     * @param \simple_html_dom\simple_html_dom $dom
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($dom)
    {
        if (!$this->isPhase2()) {
            return;
        }

        $deploymentVersion = $this->deployVersionStorage->load();
        $designParams = $this->design->getDesignParams();
        $area = $designParams['area'];
        $theme = $designParams['themeModel']->getThemePath();
        $locale = $designParams['locale'];

        $files = [
            'contexts_config.min.js',
            'critical_common.min.js',
            'critical_cms.min.js',
            'critical_category.min.js',
            'critical_product.min.js',
            'allowed_components.min.js'
        ];

        foreach ($files as $filePath) {
            $destination = $this->genCritJsAssets->getFileDestination(true, $area, $theme, $locale, $filePath);
            if (!$this->fileDriver->isExists($destination)) {
                continue;
            }

            $destinationDir = sprintf('%s/%s/%s/mashiro/critical/%s', $area, $theme, $locale, $deploymentVersion);

            $filePath = $destinationDir . '/' . $filePath;
            $filePath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filePath;

            $node = $dom->createElement("script");
            $node->setAttribute('src', $filePath);
            $head = $dom->find('head');
            if (isset($head[0])) {
                $head[0]->appendChild($node);
            }
        }
    }

    /**
     * Is phase 2.
     *
     * @return bool
     */
    private function isPhase2()
    {
        return $this->cache->load(AutoCollect::PHASE) === AutoCollect::PHASE_VALUE_2;
    }
}
