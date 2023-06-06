<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Api;

interface ClearBundlesInterface
{
    /**
     * Clear all bundles
     *
     * @return bool
     */
    public function execute();
}
