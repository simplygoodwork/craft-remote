# Telemetron plugin for Craft CMS 3.x

Send your Craft "telemetry" like versions, installed plugins, and more to Airtable. 

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Quickstart

Install via 

```shell
composer require simplygoodwork/craft-telemetron
```

Add the following Environment variables to the environment you want tracked: 

```env
TELEMETRON_BASE_ID=""
TELEMETRON_API_KEY=""
TELEMETRON_ENABLED=1
```

Alternate environment variable names can be defined in the Settings page.

## Airtable Setup

This plugin assumes you are using Airtable and have the following tables setup already. The Airtable API does not support creating new tables/structures, so you'll need to manually set these up before using the plugin. All columns are Text fields unless otherwise noted.

#### Plugins

**Table Name:** Plugins

**Table Structure**

| Hash         	| Name 	| Version	| Documentation URL (link)                           	|
|--------------------	|-------------	|----------------	|----------------------------------------------------	|
| Telemetron (1.0.0) 	| Telemetron  	| 1.0.0          	| https://github.com/simplygoodwork/craft-telemetron 	|

#### Craft Versions

**Table Name:** Craft Versions

**Table Structure**
| Name        	|
|--------------	|
| 3.7.57 	      |

#### Database Versions

**Table Name:** Database Versions

**Table Structure**
| Name        	|
|--------------	|
| 3.7.57 	      |

#### PHP Versions

**Table Name:** PHP Versions

**Table Structure**
| Name        	|
|--------------	|
| 3.7.57 	      |

#### SMTP

**Table Name:** SMTP

**Table Structure**
| Name                           | Transportation Type | Host | Username | Use Authentication (checkbox) | Sender               | Reply To               | From Name   | Encryption Method | Commands |
|--------------------------------|---------------------|------|----------|-------------------------------|----------------------|------------------------|-------------|-------------------|----------|
| GMAIL - (https://yoursite.com) | GMAIL               |      | username |                               | hello@samplesite.com | support@samplesite.com | Sample Site |                   |          |


#### Production Inventory

**Table Name:** Production Inventory

**Table Structure**
| Name             | Site URL    | Plugins (Link to another record)  | Server IP | Webroot Path | Multisite (checkbox) | Commerce (checkbox) | Craft Version (link to another record) | Craft Edition | PHP Version (link to another record) | Locales (long text)                                                      | DB Version (link to another record) | SMTP (link to another record)       |
|------------------|-------------|-----------------------------------|-----------|--------------|----------------------|---------------------|----------------------------------------|---------------|--------------------------------------|--------------------------------------------------------------------------|-------------------------------------|-------------------------------------|
| Craft Telemetron | samplesite.com | Telemetron (1.0.0)                | 127.0.0.1 |              |                      |                     | [3.7.57]                               |               | [8.0.13]                             | English ( http://localhost:8003/ )  Spanish ( http://localhost:8003/es/) | [MySQL 8.0.30]                      | [GMAIL - (https://samplesite.com/)] |




## Roadmap

- Add Events before writing to each Table so that users can customize the data being sent to Airtable
- Add support for custom data to be sent to Airtable from within the Settings