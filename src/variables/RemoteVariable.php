<?php
/**
 * Remote plugin for Craft CMS 3.x
 *
 * An internal project tracking tool.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\remote\variables;

use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use simplygoodwork\remote\Remote;

use Craft;

/**
 * Remote Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.remote }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Good Work
 * @package   Remote
 * @since     1.0.0
 */
class RemoteVariable implements ViteVariableInterface
{
  use ViteVariableTrait;
    // Public Methods
    // =========================================================================

    /**
     * @param null $optional
     * @return string
     */
    public function useVite()
    {
        return Remote::$useVite;
    }
}
