<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

namespace PureMashiro\BundleJs\Service;

class Bundle extends \Magento\Deploy\Service\Bundle
{
    public const PREFIX = 'mashiro';

    /**
     * @inheritdoc
     */
    public function deploy($area, $theme, $locale)
    {
        $helper = $this->getBundleHelperByPlugin();
        $bundle = $helper->getBundleFactory()->create(
            [
                'area' => $area,
                'theme' => $theme,
                'locale' => $locale . '/' . self::PREFIX . '/' . $helper->getBundleRegistry()->getCurrentBundle()
            ]
        );

        // delete all previously created bundle files
        $bundle->clear();

        // get file paths
        $filePaths = $this->getFilePaths();
        if (empty($filePaths)) {
            return;
        }

        foreach ($filePaths as $filePath) {
            $sourcePath = $area . '/' . $theme . '/' . $locale . '/' . $filePath;

            $contentType = $helper->getFile()->getPathInfo($filePath);
            if (!array_key_exists('extension', $contentType) ||
                !in_array($contentType['extension'], self::$availableTypes)
            ) {
                continue;
            }
            $contentType = $contentType['extension'];

            if ($helper->fileExists($sourcePath)) {
                $bundle->addFile($filePath, $sourcePath, $contentType);
            }
        }
        $bundle->flush();
    }

    /**
     * @return \PureMashiro\BundleJs\Helper\Data|null
     */
    public function getBundleHelperByPlugin()
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getFilePaths()
    {
        $helper = $this->getBundleHelperByPlugin();
        $currentBundle = $helper->getBundleRegistry()->getCurrentBundle();
        return $helper->getBundleContentByType($currentBundle);
    }
}
