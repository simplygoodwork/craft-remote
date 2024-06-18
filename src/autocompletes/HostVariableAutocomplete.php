<?php
/**
 * CodeEditor for Craft CMS
 *
 * Provides a code editor field with Twig & Craft API autocomplete
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2022 nystudio107
 */

namespace simplygoodwork\remote\autocompletes;

use nystudio107\codeeditor\base\Autocomplete;
use nystudio107\codeeditor\models\CompleteItem;
use nystudio107\codeeditor\types\AutocompleteTypes;
use nystudio107\codeeditor\types\CompleteItemKind;
use simplygoodwork\remote\Remote;

/**
 * @author    nystudio107
 * @package   CodeEditor
 * @since     1.0.0
 */
class HostVariableAutocomplete extends Autocomplete
{
    // Public Properties
    // =========================================================================

    const DEFAULT_HOST_NAMES = [
        'Arcustech',
        'AWS',
        'Cloudways',
        'Craft Cloud',
        'DigitalOcean',
        'Fortrabbit',
        'Heroku',
        'Hetzner',
        'Krystal',
        'Laravel Forge',
        'Linode',
        'Nexcess',
        'Servd',
        'Custom Hosting…',
    ];

    const DEFAULT_HOST_HANDLES = [
        'arcustech',
        'aws',
        'cloudways',
        'craftcloud',
        'digitalocean',
        'fortrabbit',
        'heroku',
        'hetzner',
        'krystal',
        'forge',
        'linode',
        'nexcess',
        'servd',
    ];
    /**
     * @var string The name of the autocomplete
     */
    public $name = 'HostVariableAutocomplete';

    /**
     * @var string The type of the autocomplete
     */
    public $type = AutocompleteTypes::GeneralAutocomplete;

    /**
     * @var bool Whether the autocomplete should be parsed with . -delimited nested sub-properties
     */
    public $hasSubProperties = true;

    // Public Methods
    // =========================================================================


    /**
     * @inerhitDoc
     */
    public function generateCompleteItems(): void
    {
        $allowedHostKeys = Remote::$plugin->settings->getAllowedHostKeys();

        foreach ($allowedHostKeys as $key => $description) {
            $subproperties = null;

            if (is_array($description)) {
                $subproperties = $description['subproperties'];
                $description = $description['description'];
            }

            CompleteItem::create()
                ->label($key)
                ->insertText('"' . $key . '": ')
                ->detail($description)
                ->documentation($description)
                ->kind(CompleteItemKind::TextKind)
                ->sortText('0000')
                ->add($this);

            if ($subproperties && constant(get_class($this) . "::${subproperties}")) {
                foreach (constant(get_class($this) . "::${subproperties}") as $subproperty) {
                    CompleteItem::create()
                        ->label($key . '.' . $subproperty)
                        ->insertText('"' . $subproperty . '"')
                        ->detail($subproperty)
                        ->documentation($subproperty)
                        ->kind(CompleteItemKind::TextKind)
                        ->add($this);
                }
            }
        }
    }
}
