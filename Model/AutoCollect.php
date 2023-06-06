<?php
/*
 *  Copyright Pure Mashiro. All rights reserved.
 *  @author Mana Light
 */

declare(strict_types=1);

namespace PureMashiro\BundleJs\Model;

class AutoCollect
{
    public const STATE_CANCELED = 'canceled';
    public const STATE_COMPLETE = 'complete';
    public const PHASE = 'mashiro_auto_collect_phase';
    public const PHASE_VALUE_1 = 'phase_1';
    public const PHASE_VALUE_2 = 'phase_2';

    public const MODE = 'auto';
    public const IDENTIFIER = 'mashiro_auto_collect';
    public const LIFE_TIME = 3600 * 2;
}
