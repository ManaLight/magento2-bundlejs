<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

namespace PureMashiro\BundleJs\Helper;

use Magento\Deploy\Package\BundleInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Asset\Minification;
use PureMashiro\BundleJs\Model\BundleRegistry;
use PureMashiro\BundleJs\Model\ResourceModel\BundleByType\CollectionFactory as BundleByTypeCollectionFactory;

class Data
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $pubStaticDir;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $pubMediaDir;

    /**
     * @var BundleInterfaceFactory
     */
    private $bundleFactory;

    /**
     * @var Files
     */
    private $utilityFiles;

    /**
     * @var BundleRegistry
     */
    private $bundleRegistry;

    /**
     * @var BundleByTypeCollectionFactory
     */
    private $bundleByType;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @var SerializerInterface|null
     */
    private $serializer;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * Data constructor.
     *
     * @param Filesystem $filesystem
     * @param BundleInterfaceFactory $bundleFactory
     * @param Files $utilityFiles
     * @param BundleRegistry $bundleRegistry
     * @param BundleByTypeCollectionFactory $bundleByType
     * @param Minification $minification
     * @param SerializerInterface|null $serializer
     * @param File|null $file
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        BundleInterfaceFactory $bundleFactory,
        Files $utilityFiles,
        BundleRegistry $bundleRegistry,
        BundleByTypeCollectionFactory $bundleByType,
        Minification $minification,
        SerializerInterface $serializer = null,
        File $file = null
    ) {
        $this->filesystem = $filesystem;
        $this->pubStaticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->pubMediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->bundleFactory = $bundleFactory;
        $this->utilityFiles = $utilityFiles;
        $this->bundleRegistry = $bundleRegistry;
        $this->bundleByType = $bundleByType;
        $this->file = $file ?: ObjectManager::getInstance()->get(File::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->minification = $minification;
    }

    /**
     * Get Bundle Factory.
     *
     * @return BundleInterfaceFactory
     */
    public function getBundleFactory()
    {
        return $this->bundleFactory;
    }

    /**
     * Get Public Static Dir.
     *
     * @return Filesystem\Directory\WriteInterface
     */
    public function getPubStaticDir()
    {
        return $this->pubStaticDir;
    }

    /**
     * Get Utility Files.
     *
     * @return Files
     */
    public function getUtilityFiles()
    {
        return $this->utilityFiles;
    }

    /**
     * Get File.
     *
     * @return File|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get Bundle Registry.
     *
     * @return BundleRegistry
     */
    public function getBundleRegistry()
    {
        return $this->bundleRegistry;
    }

    /**
     * Get Bundle Content By Type.
     *
     * @param string $type
     * @return array|bool|float|int|string|null
     */
    public function getBundleContentByType($type)
    {
        /** @var \PureMashiro\BundleJs\Model\ResourceModel\BundleByType\Collection $collection */
        $collection = $this->bundleByType->create();
        $collection->addFieldToFilter('type', $type);
        if ($collection->getSize()) {
            $bundle = $collection->getFirstItem();
            $bundleContent = $bundle->getBundle();
            return empty($bundleContent) ? [] : $this->serializer->unserialize($bundleContent);
        }

        return [];
    }

    /**
     * File exists.
     *
     * @param string $sourcePath
     * @return bool
     */
    public function fileExists($sourcePath)
    {
        try {
            $this->pubStaticDir->readFile($this->minification->addMinifiedSign($sourcePath));
            return true;
        } catch (FileSystemException $e) {
            return false;
        }
    }

    /**
     * Get Public Media Dir.
     *
     * @return Filesystem\Directory\WriteInterface
     */
    public function getPubMediaDir()
    {
        return $this->pubMediaDir;
    }
}
