<?php
/**
 * Remote plugin for Craft CMS 4.x
 *
 * Send your Craft site's telemetry like versions, installed plugins, and more to the third-party Craft Remote service.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2024 Good Work
 */

namespace simplygoodwork\remote\models;

use Craft;
use craft\base\Model;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use Exception;
use simplygoodwork\remote\helpers\Helpers;
use simplygoodwork\remote\Remote;
use yii\base\NotSupportedException;
use function get_class;

/**
 * Packet Model
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
class Packet extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Site URL
     *
     * @var string
     */
    public string $siteUrl;

    /**
     * @var string
     */
    public string $siteName;

    /**
     * @var string
     */
    public string $serverIp;

    /**
     * @var string
     */
    public string $webroot;

    /**
     * @var ?string
     */
    public ?string $locales;

    /**
     * @var bool
     */
    public bool $isMultisite;

    /**
     * @var string|int
     */
    public string|int $craftVersion;

    /**
     * @var string
     */
    public string $craftEdition;

    /**
     * @var string|int
     */
    public string|int $phpVersion;

    /**
     * @var string
     */
    public string $dbDriver;

    /**
     * @var string|int
     */
    public string|int $dbVersion;

    /**
     * @var string
     */
    public string $cpUrl;

    /**
     * @var array
     */
    public array $plugins;

    /**
     * @var array
     */
    public array $modules;

    /**
     * @var array
     */
    public array $cms;

    /**
     * @var array
     */
    public array $updates;

    /**
     * @var bool
     */
    public bool $isCommerce = false;

    public array $emailSettings = [];

    public array $licenseInfo = [];

    public ?RemoteUpdates $pluginUpdateData = null;

    public string $notes = "";

    public array $meta = [];

    public array $host = [];
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        $config['siteUrl'] = UrlHelper::siteUrl('/');
        $config['siteName'] = Craft::$app->getSystemName();
        $config['serverIp'] = $_SERVER['SERVER_ADDR'] ?? '';
        $config['webroot'] = Craft::getAlias('@webroot');
        $config['isMultisite'] = Craft::$app->getIsMultiSite();
        $config['craftVersion'] = Craft::$app->getVersion();
        $config['craftEdition'] = App::editionName(Craft::$app->getEdition());
        $config['phpVersion'] = App::phpVersion();
        $config['plugins'] = $this->_getPlugins();
        $config['modules'] = $this->_getModules();
        $config['cms'] = $this->_getCmsUpdates();
        $config['updates'] = $this->_getUpdateSummary();
        $config['host'] = $this->_getHostInfo();
        $config['cpUrl'] = UrlHelper::cpUrl();
        $config['notes'] = Remote::$plugin->settings->notes ?? "";
        $config['meta'] = Remote::$plugin->settings->meta ?? [];

        $this->_setEmailAttributes();

        $config['locales'] = Json::encode([]);
        if ($config['isMultisite']) {
            $config['locales'] = self::_getMultiSiteJson();
        }

        try {
            $config['dbDriver'] = self::_dbDriver();
        } catch (NotSupportedException $e) {
            $config['dbDriver'] = 'Error';
        }

        try {
            $config['dbVersion'] = self::_dbVersion();
        } catch (NotSupportedException $e) {
            $config['dbVersion'] = 'Error';
        }

        parent::__construct($config);
    }

    //  /**
    //   * Returns the validation rules for attributes.
    //   *
    //   * @return array
    //   */
    //  public function rules()
    //  {
    //    return [
    //      ['someAttribute', 'string'],
    //      ['someAttribute', 'default', 'value' => 'Some Default'],
    //    ];
    //  }

    /**
     * Returns the DB driver name and version
     *
     * @return string
     * @throws NotSupportedException
     */
    private static function _dbDriver(): string
    {
        $db = Craft::$app->getDb();

        if (version_compare(Craft::getVersion(), '3.8.1', '>=')) {
            return $db->getDriverLabel();
        }

        if ($db->getIsMysql()) {
            // Actually MariaDB though?
            if (StringHelper::contains($db->getSchema()->getServerVersion(), 'mariadb', false)) {
                return 'MariaDB';
            }

            return 'MySQL';
        }

        return 'PostgreSQL';
    }

    private static function _dbVersion(): string
    {
        $db = Craft::$app->getDb();

        try {
            return App::normalizeVersion($db->getSchema()->getServerVersion());
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * returns all of the site names and urls
     *
     * @return string
     */
    private static function _getMultiSiteJson(): string
    {
        $sites = Craft::$app->sites->getAllSites();
        $sitesArr = [];
        foreach ($sites as $site) {
            $sitesArr[] = [
                'name' => $site->name,
                'url' => $site->baseUrl,
                'language' => $site->language,
                'primary' => $site->primary
            ];
        }
        return Json::encode($sitesArr);
    }

    private function _getPlugins(): array
    {
        $allPlugins = Craft::$app->plugins->getAllPluginInfo();
        $pluginUpdateData = $this->_getUpdates();
        $plugins = [];

        foreach ($allPlugins as $handle => $plugin) {
            if ($plugin['isEnabled']) {
                if ($plugin['name'] === 'Commerce') {
                    $this['isCommerce'] = true;
                }

                $plugins[$handle] = (new Plugin([
                    'name' => $plugin['name'],
                    'version' => $plugin['version'],
                    'edition' => $plugin['edition'],
                    'licensedEdition' => $plugin['licensedEdition'],
                    'licenseKeyStatus' => $plugin['licenseKeyStatus'],
                    'licenseIssues' => $plugin['licenseIssues'],
                    'developer' => $plugin['developer'],
                    'description' => $plugin['description'],
                    'isTrial' => $plugin['isTrial'],
                    // upgrade refers to plugin edition, e.g. Trial, Lite, Standard, Pro
                    'upgradeAvailable' => $plugin['upgradeAvailable'],
                    'private' => $plugin['private'] ?? false,
                ]))->toArray();

                if (isset($pluginUpdateData['plugins'][$handle])) {
                    // If it's a model, convert to array, otherwise we get yii validators and stuff in the output
                    $hasCritical = $pluginUpdateData['plugins'][$handle]->getHasCritical();
                    $data = $pluginUpdateData['plugins'][$handle]->toArray();
                    if (isset($data['phpConstraint'])) {
                        $data['phpConstraint'] = preg_replace('/[^0-9.]/', '', $data['phpConstraint']);
                    } else {
                        $data['phpConstraint'] = null;
                    }
                    $data['hasCritical'] = $hasCritical;
                    $plugins[$handle] = ArrayHelper::merge($plugins[$handle], $data);
                }
            }
        }

        return $plugins;
    }

    private function _getModules(): array
    {
        $modules = Craft::$app->getModules(true);
        $data = [];
        foreach ($modules as $namespace => $module) {
            if(str_contains(get_class($module), 'modules\\')){
                $data[] = [
                    'name' => $module->id,
                    'namespace' => get_class($module),
                ];
            }
        }
        return $data;
    }

    private function _getCmsUpdates(): array
    {
        $data = Craft::$app->api->getLicenseInfo();
        $updateData = $this->_getUpdates();
        if (isset($updateData['cms'])) {
            $hasCritical = $updateData['cms']->getHasCritical();
            $data = ArrayHelper::merge($data, $updateData['cms']->toArray());
            $data['hasCritical'] = $hasCritical;
        }

        if (isset($data['phpConstraint'])) {
            $data['phpConstraint'] = preg_replace('/[^0-9.]/', '', $data['phpConstraint']);
        } else {
            $data['phpConstraint'] = null;
        }
        $data['trial'] = Craft::$app->getLicensedEdition() !== Craft::$app->getEdition();
        return $data;
    }

    /**
     * @return array
     */
    private function _getUpdateSummary(): array
    {
        $updates = $this->_getUpdates();
        return [
            'total' => $updates->getTotal(),
            'critical' => $updates->getHasCritical(),
            'expired' => $updates->getExpired(),
            'breakpoints' => $updates->getBreakpoints(),
            'abandoned' => $updates->getAbandoned(),
        ];
    }

    /**
     * @return RemoteUpdates
     */
    private function _getUpdates(): RemoteUpdates
    {
        if ($this->pluginUpdateData) {
            return $this->pluginUpdateData;
        } else {
            $this->pluginUpdateData = Craft::$app->cache->getOrSet('remote-plugin-update-data', function() {
                return new RemoteUpdates(Craft::$app->getApi()->getUpdates());
            }, 600);
        }

        return $this->pluginUpdateData;
    }

    private function _getHostInfo(): array
    {
        return Remote::$plugin->settings->host ?? [];
    }

    private function _setEmailAttributes()
    {
        $ms = App::mailSettings();
        $transportType = explode('\\', $ms->transportType);

        $this->emailSettings = [
            'sender' => Helpers::parseEnv($ms->fromEmail),
            'replyTo' => Helpers::parseEnv($ms->fromEmail),
            'fromName' => Helpers::parseEnv($ms->fromName),
            'transportType' => strtoupper(end($transportType)),
            'host' => null,
            'username' => null,
            'encryptionMethod' => null,
            'useAuthentication' => false,
            'command' => null
        ];

        if (isset($ms->transportSettings['encryptionMethod'])) {
            $this->emailSettings['encryptionMethod'] = Helpers::parseEnv($ms->transportSettings['encryptionMethod']);
        }

        if (isset($ms->transportSettings['host'])) {
            $this->emailSettings['host'] = Helpers::parseEnv($ms->transportSettings['host']);
        }

        if (isset($ms->transportSettings['username'])) {
            $this->emailSettings['username'] = Helpers::parseEnv($ms->transportSettings['username']);
        }

        if (isset($ms->transportSettings['useAuthentication'])) {
            $this->emailSettings['useAuthentication'] = Helpers::parseBooleanEnv($ms->transportSettings['useAuthentication']);
        }

        if (isset($ms->transportSettings['command'])) {
            $this->emailSettings['command'] = $ms->transportSettings['command'];
        }
    }

}
