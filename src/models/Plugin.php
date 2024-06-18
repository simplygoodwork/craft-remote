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

  use craft\helpers\App;
  use craft\helpers\ArrayHelper;
  use craft\helpers\StringHelper;
  use simplygoodwork\remote\Remote;

  use Craft;
  use craft\base\Model;

  class Plugin extends Model
  {

    /**
     * Plugin Name
     * @var string
     */
    public $name;

    /**
     * Plugin version
     * @var string
     */
    public $version;

    /**
     * Plugin edition (lite, pro, standard)
     * @var string
     */
    public $edition;

	  /**
	   * @var string|null
	   */
	  public $licensedEdition;

	  /**
	   * @var array
	   */
	  public $licenseIssues;

	  /**
	   * @var string
	   */
	  public $issueText = '';

	  /**
	   * @var string
	   */
	  public $developer;

	  /**
	   * @var string
	   */
	  public $description;


	  /**
	   * @var bool
	   */
	  public $isTrial;

	  /**
	   * @var bool|null
	   */
	  public $upgradeAvailable;

	  /**
	   * @var bool
	   */
	  public $private;

    /**
     * License status ('trial', 'valid', 'unknown' => '')
     * @var string
     */
    public $licenseKeyStatus;

    /**
     * Documentation URL
     * @var null|string
     */
    public $documentationUrl = '';


    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
      $config['edition'] = StringHelper::toTitleCase($config['edition']);

      if($config['licenseKeyStatus'] === 'unknown'){
        $config['licenseKeyStatus'] = 'Not Required';
      }

      $config['licenseKeyStatus'] = StringHelper::toTitleCase($config['licenseKeyStatus']);

      if (count($config['licenseIssues'])) {
          $config['issueText'] = implode(' ', $config['licenseIssues']);
      }

      parent::__construct($config);
    }
  }
