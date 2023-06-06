<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Helper;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use PureMashiro\BundleJs\Action\GetBundleTypes;

class FileManager
{
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var AssetRepo
     */
    private $assetRepo;

    /**
     * @var GetBundleTypes
     */
    private $getBundleTypes;

    /**
     * FileManager constructor.
     * @param AppState $appState
     * @param Filesystem $filesystem
     * @param AssetRepo $assetRepo
     * @param GetBundleTypes $getBundleTypes
     */
    public function __construct(
        AppState $appState,
        Filesystem $filesystem,
        AssetRepo $assetRepo,
        GetBundleTypes $getBundleTypes
    ) {
        $this->appState = $appState;
        $this->filesystem = $filesystem;
        $this->assetRepo = $assetRepo;
        $this->getBundleTypes = $getBundleTypes;
    }

    /**
     * @return AppState
     */
    public function getAppState()
    {
        return $this->appState;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return AssetRepo
     */
    public function getAssetRepo()
    {
        return $this->assetRepo;
    }

    /**
     * @return GetBundleTypes
     */
    public function getBundleTypesAction()
    {
        return $this->getBundleTypes;
    }
}
