<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

class BundleRegistry
{
    private $currentBundle;

    /**
     * @param $type
     */
    public function startDeployAdvancedBundle($type)
    {
        $this->currentBundle = $type;
    }

    public function endDeployAdvancedBundle()
    {
        $this->currentBundle = null;
    }

    /**
     * @return mixed
     */
    public function getCurrentBundle()
    {
        return $this->currentBundle;
    }
}
