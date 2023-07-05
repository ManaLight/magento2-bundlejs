<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

class BundleRegistry
{
    /**
     * @var string
     */
    private $currentBundle;

    /**
     * Start Deloy Advanced Bundle.
     *
     * @param string $type
     */
    public function startDeployAdvancedBundle($type)
    {
        $this->currentBundle = $type;
    }

    /**
     * End Deploy Advanced Bundle.
     */
    public function endDeployAdvancedBundle()
    {
        $this->currentBundle = null;
    }

    /**
     * Get Current Bundle.
     *
     * @return mixed
     */
    public function getCurrentBundle()
    {
        return $this->currentBundle;
    }
}
