<?php
/**
 * Remote plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

/**
 * Remote config.php
 *
 * This file exists only as a template for the Remote settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'remote.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    'apiKey' => getenv('REMOTE_API_KEY'),
	'host' => [
//        'name' => 'Servd',
//        'handle' => 'servd',
//        'icon' => '',
//        'url' => 'https://your-host.com/project-path',
//        'plan' => 'free',
//        'region' => 'us-east-1',
//        'owner' => 'Client',
//        'server_access' => true,
//        'dns_provider' => 'Cloudflare',
//        'dns_access' => true,
//        'notes' => '',
//        'meta' => [],
	],
	'meta' => [
//        'Development Lead' => 'Lead Developer Name',
//        'Project Manager' => 'Project Manager Name',
    ]
];
