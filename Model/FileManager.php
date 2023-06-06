<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

namespace PureMashiro\BundleJs\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State as AppState;
use Magento\Framework\RequireJs\Config;
use PureMashiro\BundleJs\Service\Bundle as ServiceBundle;

class FileManager extends \Magento\RequireJs\Model\FileManager
{
    /**
     * @inheritdoc
     */
    public function createBundleJsPool()
    {
        $helper = $this->getFileManagerByPlugin();
        $bundles = [];
        if ($helper->getAppState()->getMode() == AppState::MODE_PRODUCTION) {
            $libDir = $helper->getFilesystem()->getDirectoryRead(DirectoryList::STATIC_VIEW);
            /** @var $context \Magento\Framework\View\Asset\File\FallbackContext */
            $context = $helper->getAssetRepo()->getStaticViewFileContext();

            $bundleTypes = $helper->getBundleTypesAction()->execute();
            foreach ($bundleTypes as $bundleType) {
                $bundleDir = $context->getPath() . '/' . ServiceBundle::PREFIX . '/' . $bundleType . '/' . Config::BUNDLE_JS_DIR;

                if (!$libDir->isExist($bundleDir)) {
                    continue;
                }

                foreach ($libDir->read($bundleDir) as $bundleFile) {
                    if (pathinfo($bundleFile, PATHINFO_EXTENSION) !== 'js') {
                        continue;
                    }
                    $relPath = $libDir->getRelativePath($bundleFile);
                    $bundles[] = $helper->getAssetRepo()->createArbitrary($relPath, '');
                }
            }
        }

        return $bundles;
    }

    /**
     * @param $filePath
     * @return \Magento\Framework\View\Asset\File
     */
    public function getCriticalJsAsset($filePath)
    {
        $helper = $this->getFileManagerByPlugin();
        $libDir = $helper->getFilesystem()->getDirectoryRead(DirectoryList::STATIC_VIEW);
        /** @var $context \Magento\Framework\View\Asset\File\FallbackContext */
        $context = $helper->getAssetRepo()->getStaticViewFileContext();
        $file = $context->getPath() . '/' . ServiceBundle::PREFIX . '/' . $filePath;
        $relPath = $libDir->getRelativePath($file);

        return $helper->getAssetRepo()->createArbitrary($relPath, '');
    }

    /**
     * @return \PureMashiro\BundleJs\Helper\FileManager|null
     */
    public function getFileManagerByPlugin()
    {
        return null;
    }
}
