<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

namespace PureMashiro\BundleJs\Plugin\Service;

use PureMashiro\BundleJs\Helper\Data;
use PureMashiro\BundleJs\Service\Bundle;

class BundlePlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * BundlePlugin constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After Get Bundle Helper By Plugin.
     *
     * @param Bundle $subject
     * @param mixed  $result
     * @return Data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetBundleHelperByPlugin(Bundle $subject, $result)
    {
        return $this->helper;
    }
}
