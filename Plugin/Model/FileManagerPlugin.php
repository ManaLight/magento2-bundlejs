<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Plugin\Model;

use PureMashiro\BundleJs\Helper\FileManager;

class FileManagerPlugin
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * FileManagerPlugin constructor.
     * @param FileManager $fileManager
     */
    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    /**
     * @return FileManager
     */
    public function afterGetFileManagerByPlugin()
    {
        return $this->fileManager;
    }
}
