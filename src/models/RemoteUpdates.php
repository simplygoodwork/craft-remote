<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace simplygoodwork\remote\models;

use craft\base\Model;
use craft\models\Updates;

/**
 * Stores all of the available update info.
 *
 * @property bool $hasCritical Whether any of the updates have a critical release available
 * @property int $total The total number of available updates
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class RemoteUpdates extends Updates
{

    public function getExpired(): int
    {
        $count = 0;

        if ($this->cms->status === 'expired') {
            $count++;
        }

        foreach ($this->plugins as $update) {
            if ($update->status === 'expired') {
                $count++;
            }
        }

        return $count;
    }

    public function getBreakpoints(): int
    {
        $count = 0;

        if ($this->cms->status === 'breakpoint') {
            $count++;
        }

        foreach ($this->plugins as $update) {
            if ($update->status === 'breakpoint') {
                $count++;
            }
        }

        return $count;
    }

    public function getAbandoned(): int
    {
        $count = 0;

        foreach ($this->plugins as $update) {
            if (isset($update->abandoned) && $update->abandoned) {
                $count++;
            }
        }

        return $count;
    }
}
