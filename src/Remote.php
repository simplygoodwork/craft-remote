<?php
/**
 * Remote plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\remote;

use nystudio107\codeeditor\autocompletes\EnvironmentVariableAutocomplete;
use nystudio107\codeeditor\events\RegisterCodeEditorAutocompletesEvent;
use nystudio107\codeeditor\services\AutocompleteService;
use simplygoodwork\remote\assetbundles\remote\RemoteAsset;
use simplygoodwork\remote\autocompletes\HostVariableAutocomplete;
use simplygoodwork\remote\services\Sync as SyncService;
use simplygoodwork\remote\variables\RemoteVariable;
use simplygoodwork\remote\models\Settings;
use simplygoodwork\remote\utilities\RemoteSync as RemoteSyncUtility;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

//use nystudio107\pluginvite\services\VitePluginService;

/**
 *
 * @author    Good Work
 * @package   Remote
 * @since     1.0.0
 *
 * @property  SyncService $sync
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Remote extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     *
     * @var Remote
     */
    public static $plugin;

	public static $useVite = false;

    // Public Properties
    // =========================================================================

    /**
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     *
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
      $config['components'] = [
        'remote' => __CLASS__,
        // Register the vite service

      ];

	  if(version_compare(Craft::getVersion(), '3.4.0', '>=')) {
		  self::$useVite = true;
		  $config['components']['vite'] = [
	          'class' => \nystudio107\pluginvite\services\VitePluginService::class,
	          'assetClass' => RemoteAsset::class,
	          'useDevServer' => false,
	          'devServerPublic' => 'http://localhost:3002',
	          'serverPublic' => 'http://localhost:8000',
	          'errorEntry' => 'src/index.js',
	          'devServerInternal' => 'http://host.docker.internal:3002',
	          'checkDevServer' => false,
            ];
	  }

      parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        parent::init();
        self::$plugin = $this;
//		if(isset($this->components['vite'])) {
//			self::$useVite = true;
//		}

        $this->_registerComponents();

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'simplygoodwork\remote\console\controllers';
        }

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = RemoteSyncUtility::class;
            }
        );

//		Event::on(
//	    AutocompleteService::class,
//	    AutocompleteService::EVENT_REGISTER_CODEEDITOR_AUTOCOMPLETES,
//	    function (RegisterCodeEditorAutocompletesEvent $event) {
//	        if($event->fieldType === 'HostField' && version_compare(PHP_VERSION , '7.1', '>=')) {
//				$event->types = [HostVariableAutocomplete::class];
//	        }
//	    }
//		);

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
				$variables = ['class' => RemoteVariable::class,];

				if(self::$useVite){
					$variables['vite'] = $this->vite;
				}

                $variable->set('remote', $variables);
            }
        );

    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'remote/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

  /**
   * Registers components.
   */
  private function _registerComponents()
  {
    // Register services as components
    $this->setComponents([
      'sync' => SyncService::class,
    ]);
  }
}
