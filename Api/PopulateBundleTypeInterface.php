<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Api;

interface PopulateBundleTypeInterface
{
    /**
     * Generate bundles
     *
     * @return array
     */
    public function execute();
}
