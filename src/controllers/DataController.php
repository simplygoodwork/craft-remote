<?php
/**
 * Remote plugin for Craft CMS 4.x
 *
 * Send your Craft site's telemetry like versions, installed plugins, and more to the third-party Craft Remote service.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2024 Good Work
 */

namespace simplygoodwork\remote\controllers;

use Craft;
use craft\web\Controller;
use Exception;
use simplygoodwork\remote\helpers\Helpers;
use simplygoodwork\remote\models\Packet;
use simplygoodwork\remote\Remote;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use function get_class;
use function strpos;

/**
 * @author    Good Work
 * @package   Remote
 * @since     1.0.0
 */
class DataController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['index'];

    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        try {
            $packet = new Packet();
            return $this->asJson($packet);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            return $this->asFailure($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    private function _auth(): bool
    {
        $request = Craft::$app->getRequest();
        $headers = $request->getHeaders();
        $token = $headers->get('X-REMOTE-KEY');

        if (!$token) {
            return false;
        }

        $pluginKey = Helpers::parseEnv(Remote::$plugin->getSettings()->apiKey);

        if (!$pluginKey || $pluginKey !== $token) {
            return false;
        }

        return true;
    }
}
