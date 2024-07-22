<?php
/**
 * Remote plugin for Craft CMS 5.x
 *
 * Send your Craft site's telemetry like versions, installed plugins, and more to the third-party Craft Remote service.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2024 Good Work
 */

namespace simplygoodwork\remote\models;

use craft\base\Model;
use craft\helpers\StringHelper;

class Plugin extends Model
{

    /**
     * Plugin Name
     *
     * @var string
     */
    public string $name;

    /**
     * Plugin version
     *
     * @var string
     */
    public string $version;

    /**
     * Plugin edition (lite, pro, standard)
     *
     * @var string
     */
    public string $edition;

    /**
     * @var string|null
     */
    public ?string $licensedEdition;

    /**
     * @var array
     */
    public array $licenseIssues;

    /**
     * @var string
     */
    public string $issueText = '';

    /**
     * @var string
     */
    public string $developer;

    /**
     * @var string
     */
    public string $description;


    /**
     * @var bool
     */
    public bool $isTrial;

    /**
     * @var bool|null
     */
    public ?bool $upgradeAvailable;

    /**
     * @var bool
     */
    public bool $private;

    /**
     * License status ('trial', 'valid', 'unknown' => '')
     *
     * @var string
     */
    public string $licenseKeyStatus;

    /**
     * Documentation URL
     *
     * @var null|string
     */
    public ?string $documentationUrl = '';


    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        $config['edition'] = StringHelper::toTitleCase($config['edition']);

        if ($config['licenseKeyStatus'] === 'unknown') {
            $config['licenseKeyStatus'] = 'Not Required';
        }

        $config['licenseKeyStatus'] = StringHelper::toTitleCase($config['licenseKeyStatus']);

        if (count($config['licenseIssues'])) {
            $config['issueText'] = implode(' ', $config['licenseIssues']);
        }

        parent::__construct($config);
    }
}
