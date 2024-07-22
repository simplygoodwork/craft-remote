<?php
/**
 * Remote plugin for Craft CMS 5.x
 *
 * Send your Craft site's telemetry like versions, installed plugins, and more to the third-party Craft Remote service.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2024 Good Work
 */

namespace simplygoodwork\remote;

use craft\helpers\App;
use yii\base\Event;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use nystudio107\codeeditor\events\RegisterCodeEditorAutocompletesEvent;
use nystudio107\codeeditor\services\AutocompleteService;
use nystudio107\pluginvite\services\VitePluginService;

use simplygoodwork\remote\assetbundles\remote\RemoteAsset;
use simplygoodwork\remote\autocompletes\HostVariableAutocomplete;
use simplygoodwork\remote\models\Settings;
use simplygoodwork\remote\variables\RemoteVariable;

/**
 *
 * @author    Good Work
 * @package   Remote
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Remote extends Plugin
{
    /**
     * @var Remote
     */
    public static Remote $plugin;

    public static bool $useVite = false;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;
    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    public static function config(): array
    {
        self::$useVite = true;
        return [
            'components' => [
                'remote' => __CLASS__,
                'vite' => [
                    'class' => VitePluginService::class,
                    'assetClass' => RemoteAsset::class,
                    'useDevServer' => App::parseEnv('VITE_PLUGIN_DEVSERVER') ?? false,
                    'devServerPublic' => 'https://localhost:3001/',
                    'serverPublic' => App::env('DEFAULT_SITE_URL') . '/dist/',
                    'errorEntry' => 'src/index.js',
                    'devServerInternal' => 'https://localhost:3001/',
                    'checkDevServer' => false,
                ]
            ]
        ];
    }

    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$useVite = true;

        $this->_registerComponents();

        // Register our utilities
//        Event::on(
//            Utilities::class,
//            Utilities::EVENT_REGISTER_UTILITY_TYPES,
//            function(RegisterComponentTypesEvent $event) {
//                $event->types[] = RemoteSyncUtility::class;
//            }
//        );

        Event::on(
            AutocompleteService::class,
            AutocompleteService::EVENT_REGISTER_CODEEDITOR_AUTOCOMPLETES,
            function(RegisterCodeEditorAutocompletesEvent $event) {
                if ($event->fieldType === 'HostField' && version_compare(PHP_VERSION, '7.1', '>=')) {
                    $event->types = [HostVariableAutocomplete::class];
                }
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variables = ['class' => RemoteVariable::class,];

                if (self::$useVite) {
                    $variables['viteService'] = $this->vite;
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
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
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
//            'sync' => SyncService::class,
        ]);
    }
}
