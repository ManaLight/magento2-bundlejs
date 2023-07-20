<?php

declare(strict_types=1);

namespace PureMashiro\BundleJs\Action;

use Magento\Framework\App\View\Deployment\Version\StorageInterface as DeploymentVersionStorage;
use Magento\Framework\Code\Minifier\AdapterInterface as MinifyAdapter;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory as FileWriteFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\View\Asset\Minification;
use PureMashiro\BundleJs\Helper\Config as ConfigHelper;
use PureMashiro\BundleJs\Helper\Data as BundleJsHelper;
use PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig\CollectionFactory as ContextsConfigCollectionFactory;

class GenerateCriticalJsAssets
{
    /**
     * @var DeploymentVersionStorage
     */
    private $deploymentVersionStorage;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @var BundleJsHelper
     */
    private $bundleJsHelper;

    /**
     * @var FileWriteFactory
     */
    private $fileWriteFactory;

    /**
     * @var ContextsConfigCollectionFactory
     */
    private $contextsConfigCollectionFactory;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var MinifyAdapter
     */
    private $minifyAdapter;

    /**
     * @param DeploymentVersionStorage $deploymentVersionStorage
     * @param IoFile $ioFile
     * @param BundleJsHelper $bundleJsHelper
     * @param FileWriteFactory $fileWriteFactory
     * @param ContextsConfigCollectionFactory $contextsConfigCollectionFactory
     * @param ConfigHelper $configHelper
     * @param FileDriver $fileDriver
     * @param Minification $minification
     * @param MinifyAdapter $minifyAdapter
     */
    public function __construct(
        DeploymentVersionStorage        $deploymentVersionStorage,
        IoFile                          $ioFile,
        BundleJsHelper                  $bundleJsHelper,
        FileWriteFactory                $fileWriteFactory,
        ContextsConfigCollectionFactory $contextsConfigCollectionFactory,
        ConfigHelper                    $configHelper,
        FileDriver                      $fileDriver,
        Minification                    $minification,
        MinifyAdapter                   $minifyAdapter
    ) {
        $this->deploymentVersionStorage = $deploymentVersionStorage;
        $this->ioFile = $ioFile;
        $this->bundleJsHelper = $bundleJsHelper;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->contextsConfigCollectionFactory = $contextsConfigCollectionFactory;
        $this->configHelper = $configHelper;
        $this->fileDriver = $fileDriver;
        $this->minification = $minification;
        $this->minifyAdapter = $minifyAdapter;
    }

    /**
     * Execute.
     *
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param bool   $media
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($area, $theme, $locale, $media = false)
    {
        $this->generateContextsConfigJs($area, $theme, $locale, $media);
        $this->generateCriticalDepsJs($area, $theme, $locale, $media);
        $this->generateAllowedComponentsJs($area, $theme, $locale, $media);
    }

    /**
     * Add Critical Dependencies.
     *
     * @param string $type
     * @param string $theme
     * @return string|null
     */
    private function addCriticalDependencies($type, $theme)
    {
        $deps = $this->getCriticalDependencies($type, $theme);

        if (empty($deps)) {
            return null;
        }

        $deps = json_encode($deps);

        return <<<JS
var config = {
    deps: {$deps}
};
require.config(config);
JS;
    }

    /**
     * Get Critical Dependencies.
     *
     * @param string $type
     * @param string $theme
     * @return array|mixed
     */
    private function getCriticalDependencies($type, $theme)
    {
        return $this->configHelper->getCriticalDeps($type, $theme);
    }

    /**
     * Generate Contexts Config Js.
     *
     * @param string    $area
     * @param string    $theme
     * @param string    $locale
     * @param bool      $media
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateContextsConfigJs($area, $theme, $locale, $media = false)
    {
        $filePath = 'contexts_config.min.js';
        $destination = $this->getFileDestination($media, $area, $theme, $locale, $filePath);
        $js = $this->getContextsConfigJsContent();
        if ($js && $this->minification->isEnabled('js')) {
            $js = $this->minifyAdapter->minify($js);
        }
        $this->writeFile($destination, $js);
    }

    /**
     * Generate critical Deps Js.
     *
     * @param string    $area
     * @param string    $theme
     * @param string    $locale
     * @param bool      $media
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCriticalDepsJs($area, $theme, $locale, $media = false)
    {
        $types = ['common', 'cms', 'category', 'product'];

        foreach ($types as $type) {
            $filePath = "critical_$type.min.js";
            $destination = $this->getFileDestination($media, $area, $theme, $locale, $filePath);
            $js = $this->addCriticalDependencies($type, $theme);

            if ($js) {
                if ($this->minification->isEnabled('js')) {
                    $js = $this->minifyAdapter->minify($js);
                }
                $this->writeFile($destination, $js);
            } else {
                if ($this->fileDriver->isExists($destination)) {
                    $this->fileDriver->deleteFile($destination);
                }
            }
        }
    }

    /**
     * Get File Destination.
     *
     * @param string $media
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param string $filePath
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFileDestination($media, $area, $theme, $locale, string $filePath): string
    {
        if ($media) {
            $deploymentVersion = $this->deploymentVersionStorage->load();
            $destinationDir = sprintf('%s/%s/%s/mashiro/critical/%s', $area, $theme, $locale, $deploymentVersion);

            $this->ioFile->checkAndCreateFolder(
                $this->bundleJsHelper->getPubMediaDir()->getAbsolutePath($destinationDir)
            );
            $destinationPath = $destinationDir . '/' . $filePath;
            $destination = $this->bundleJsHelper->getPubMediaDir()->getAbsolutePath($destinationPath);
        } else {
            $destinationPath = $area . '/' . $theme . '/' . $locale . '/' . 'mashiro' . '/' . $filePath;
            $destination = $this->bundleJsHelper->getPubStaticDir()->getAbsolutePath($destinationPath);
        }
        return $destination;
    }

    /**
     * Write File.
     *
     * @param string $destination
     * @param string $js
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeFile(string $destination, string $js): void
    {
        $file = $this->fileWriteFactory->create(
            $destination,
            DriverPool::FILE,
            'w'
        );
        $file->write($js);
        $file->close();
    }

    /**
     * Get Contexts Config Js Content.
     *
     * @return string
     */
    private function getContextsConfigJsContent(): string
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\ContextsConfig\Collection $collection */
        $collection = $this->contextsConfigCollectionFactory->create();
        $contextsConfig = $collection->getFirstItem();
        $config = $contextsConfig->getConfig();
        $js = <<<JS
var contextsConfig = {$config};
if (requirejs.s.contexts._.config['baseUrl']) {
    contextsConfig['baseUrl'] = requirejs.s.contexts._.config['baseUrl'];
}
if (requirejs.s.contexts._.config.config['jsbuild']) {
    contextsConfig.config['jsbuild'] = requirejs.s.contexts._.config.config['jsbuild'];
}
if (requirejs.s.contexts._.config.config['text']) {
    contextsConfig.config['text'] = requirejs.s.contexts._.config.config['text'];
}
Object.assign(requirejs.s.contexts._.config, contextsConfig)
JS;
        return $js;
    }

    /**
     * Generate Allowed Components Js.
     *
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param bool $media
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function generateAllowedComponentsJs($area, $theme, $locale, bool $media)
    {
        $filePath = 'allowed_components.min.js';
        $destination = $this->getFileDestination($media, $area, $theme, $locale, $filePath);
        $js = $this->getAllowedComponentsJsContent();
        if ($js && $this->minification->isEnabled('js')) {
            $js = $this->minifyAdapter->minify($js);
        }
        $this->writeFile($destination, $js);
    }

    /**
     * Get Allowed Components Js Content.
     *
     * @return string|null
     */
    private function getAllowedComponentsJsContent()
    {
        $allowedJsComponents = $this->configHelper->getAllowedJsComponents();
        if (empty($allowedJsComponents)) {
            return null;
        }

        $allowedJsComponents = json_encode($allowedJsComponents);

        return <<<JS
window.mashiro = {
    "bundleJs": {
        "allowedComponents": $allowedJsComponents
    }
};

require([
    'PureMashiro_BundleJs/mage/apply/main',
    'mage/url',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'domReady!'
], function (mage, url) {
    url.setBaseUrl(window.BASE_URL);
    setTimeout(mage.apply);
});
JS;
    }
}
