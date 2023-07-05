<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Api;

interface SaveBundleByPageInterface
{
    /**
     * Save bundle by page
     *
     * @param string $fullActionName
     * @param string $pathInfo
     * @param string[] $bundle
     * @param bool $critical
     * @param string|null $config
     * @param string|null $area
     * @param string|null $theme
     * @param string|null $locale
     * @return array
     */
    public function execute(
        $fullActionName,
        $pathInfo,
        $bundle,
        $critical = false,
        $config = null,
        $area = null,
        $theme = null,
        $locale = null
    );
}
