<?php
/**
 * Remote plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\remote\models;

use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\Json;
use simplygoodwork\remote\behaviors\JsonParsingBehavior;
use craft\helpers\App;
use craft\helpers\StringHelper;
use simplygoodwork\remote\Remote;

use Craft;
use craft\base\Model;

/**
 * Remote Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Good Work
 * @package   Remote
 * @since     1.0.0
 */
class Settings extends Model
{
	// Public Properties
	// =========================================================================

	const ALLOWED_HOST_KEYS = [
		'name' => [
			'description' => 'Hosting provider name',
			'subproperties' => 'DEFAULT_HOST_NAMES'
		],
		'handle' => [
			'description' => 'Hosting provider handle',
			'subproperties' => 'DEFAULT_HOST_HANDLES'
		],
		'icon' => 'Hosting provider icon',
		'url' => 'Hosting dashboard URL',
		'plan' => 'Hosting plan',
		'region' => 'Server region',
		'owner' => 'Server owner',
		'server_access' => 'Do you have server access? (bool)',
		'dns_provider' => 'DNS provider',
		'dns_access' => 'Do you have DNS access? (bool)',
		'notes' => 'Hosting provider notes',
		'meta' => 'Hosting metadata (object)'
];

	const REQUIRED_HOST_KEYS = ['name','handle'];

	/**
	 * @var string
	 */
	public $apiKey;

	/**
	 * @var array
	 */
	public $meta;

	/**
	 * @var string
	 */
	public $notes = '';

	/**
	 * @var array
	 */
	public $host;

	// Public Methods
	// =========================================================================
	/**
	 * @inheritdoc
	 */
	public function behaviors(): array
	{
		return [
			'parser' => [
				'class' => EnvAttributeParserBehavior::class,
				'attributes' => ['apiKey'],
			]
		];
	}

	/**
	 * Returns the validation rules for attributes.
	 *
	 * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			[['apiKey'], 'string'],
			[['apiKey'], 'required'],
			[['host'], 'validateHost']
		];
	}

	public function validateHost($attribute, $params)
	{
		$value = $this->$attribute;

		if (!is_array($value)) {
			$value = Json::decodeIfJson($value);
		}

		$this->$attribute = $value;

		foreach ($value as $key => $val) {
			if (!in_array($key, array_keys($this::ALLOWED_HOST_KEYS))) {
				$this->addError($attribute, "Invalid key '$key' found in Host settings array.");
				return;
			}
		}

		foreach($this::REQUIRED_HOST_KEYS as $key){
			if(!array_key_exists($key, $value)){
				$this->addError($attribute, "Missing required key '$key' in Host settings array.");
				return;
			}
		}
	}

	public function getAllowedHostKeys(): array
	{
		return $this::ALLOWED_HOST_KEYS;
	}

}
