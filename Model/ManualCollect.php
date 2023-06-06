<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

class ManualCollect
{
    public const STATE_CANCELED = 'canceled';

    public const MODE = 'manual';
    public const IDENTIFIER = 'mashiro_manual_collect';
    public const LIFE_TIME = 3600 * 2;
}
