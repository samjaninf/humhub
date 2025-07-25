<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\i18n\PhpMessageSource;

Yii::setAlias('@humhub', $_ENV['HUMHUB_ALIASES__HUMHUB'] ?? realpath(__DIR__ . '/../'));

// Workaround: PHP 7.3 compatible ZF2 ArrayObject class
Yii::$classMap['Zend\Stdlib\ArrayObject'] = '@humhub/compat/ArrayObject.php';
Yii::$classMap['humhub\modules\search\interfaces\Searchable'] = '@humhub/compat/search/Searchable.php';
Yii::$classMap['humhub\modules\search\events\SearchAddEvent'] = '@humhub/compat/search/SearchAddEvent.php';

// Workaround: If OpenSSL extension is not available (#3852)
if (!defined('PKCS7_DETACHED')) {
    define('PKCS7_DETACHED', 64);
}

$logTargetConfig = [
    'levels' => ['error', 'warning'],
    'except' => [
        'yii\web\HttpException:400',
        'yii\web\HttpException:401',
        'yii\web\HttpException:403',
        'yii\web\HttpException:404',
        'yii\web\HttpException:405',
        'yii\web\User::getIdentityAndDurationFromCookie',
        'yii\web\User::renewAuthStatus',
    ],
    'logVars' => ['_GET', '_SERVER'],
    'maskVars' => [
        '_SERVER.HTTP_AUTHORIZATION',
        '_SERVER.PHP_AUTH_USER',
        '_SERVER.PHP_AUTH_PW',
        '_SERVER.HUMHUB_CONFIG__COMPONENTS__DB__PASSWORD',
    ],
];

$config = [
    'name' => 'HumHub',
    'version' => '1.17.4-master',
    'minRecommendedPhpVersion' => '8.1',
    'minSupportedPhpVersion' => '8.1',
    'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
    'bootstrap' => [
        'log',
        'humhub\components\bootstrap\ModuleAutoLoader',
        'queue',
        'humhub\modules\ui\view\bootstrap\ThemeLoader',
    ],
    'runtimePath' => '@app/runtime',
    'sourceLanguage' => 'en',
    'aliases' => [
        '@webroot' => realpath(__DIR__ . '/../../../'),
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@filestore' => '@webroot/uploads/file',
        '@config' => '@app/config',
        '@themes' => '@webroot/themes',
    ],
    'components' => [
        'moduleManager' => [
            'class' => \humhub\components\ModuleManager::class,
        ],
        'notification' => [
            'class' => \humhub\modules\notification\components\NotificationManager::class,
            'targets' => [
                \humhub\modules\notification\targets\WebTarget::class => [
                    'renderer' => ['class' => \humhub\modules\notification\renderer\WebRenderer::class],
                ],
                \humhub\modules\notification\targets\MailTarget::class => [
                    'renderer' => ['class' => \humhub\modules\notification\renderer\MailRenderer::class],
                ],
                \humhub\modules\notification\targets\MobileTarget::class => [],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                \yii\log\FileTarget::class => \yii\helpers\ArrayHelper::merge(
                    ['class' => \yii\log\FileTarget::class],
                    $logTargetConfig,
                ),
                \yii\log\DbTarget::class => \yii\helpers\ArrayHelper::merge(
                    ['class' => \yii\log\DbTarget::class],
                    $logTargetConfig,
                ),
            ],
        ],
        'settings' => [
            'class' => \humhub\components\SettingsManager::class,
            'moduleId' => 'base',
        ],
        'i18n' => [
            'class' => \humhub\components\i18n\I18N::class,
            'translations' => [
                'base' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@humhub/messages',
                ],
                'error' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@humhub/messages',
                ],
                'humhub.yii' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@humhub/messages',
                ],
                'SearchModule.*' => [
                    // Temporary: During conversion of the search module
                    'class' => PhpMessageSource::class,
                    'basePath' => '@humhub/messages',
                ],
                'custom' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@humhub/messages',
                ],
            ],
        ],
        'formatter' => [
            'class' => \humhub\components\i18n\Formatter::class,
        ],
        'cache' => [
            'class' => \yii\caching\DummyCache::class,
        ],
        'runtimeCache' => [
            'class' => \yii\caching\ArrayCache::class,
            'serializer' => false,
        ],
        'mailer' => [
            'class' => \humhub\components\mail\Mailer::class,
            'viewPath' => '@humhub/views/mail',
            'view' => [
                'class' => \yii\web\View::class,
                'theme' => [
                    'class' => \humhub\modules\ui\view\components\Theme::class,
                    'name' => 'HumHub',
                ],
            ],
        ],
        'assetManager' => [
            'class' => \humhub\components\AssetManager::class,
            'appendTimestamp' => true,
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD || YII_ENV_TEST ? 'assets-prod.php' : 'assets-dev.php')),
        ],
        'view' => [
            'class' => \humhub\modules\ui\view\components\View::class,
            'theme' => [
                'class' => \humhub\modules\ui\view\components\Theme::class,
                'name' => 'HumHub',
            ],
        ],
        'db' => [
            'class' => \yii\db\Connection::class,
            // Fix for MySQL 8.0.21+: https://github.com/yiisoft/yii2/issues/18207
            'schemaMap' => [
                'mysqli' => 'humhub\components\db\MysqlSchema',
                'mysql' => 'humhub\components\db\MysqlSchema',
            ],
            'dsn' => 'mysql:host=localhost;dbname=humhub',
            'username' => '',
            'password' => '',
            'charset' => 'utf8mb4',
            'enableSchemaCache' => true,
            'on afterOpen' => ['humhub\libs\Helpers', 'SqlMode'],
        ],
        'authClientCollection' => [
            'class' => \humhub\modules\user\authclient\Collection::class,
            'clients' => [],
        ],
        'queue' => [
            'class' => \humhub\modules\queue\driver\MySQL::class,
        ],
        'urlManager' => [
            'class' => \humhub\components\UrlManager::class,
        ],
        'live' => [
            'class' => \humhub\modules\live\components\Sender::class,
            'driver' => [
                'class' => \humhub\modules\live\driver\Poll::class,
            ],
        ],
        'mutex' => [
            'class' => \yii\mutex\MysqlMutex::class,
        ],
    ],
    'params' => [
        'installed' => false,
        'databaseDefaultStorageEngine' => 'InnoDB',
        'dynamicConfigFile' => '@config/dynamic.php',
        'moduleAutoloadPaths' => ['@app/modules', '@humhub/modules'],
        'availableLanguages' => [
            'en-US' => 'English (US)',
            'en-GB' => 'English (UK)',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'pt' => 'Português',
            'pt-BR' => 'Português do Brasil',
            'es' => 'Español',
            'es-419' => 'Español (latinoamericano)',
            'ca' => 'Català',
            'it' => 'Italiano',
            'th' => 'ไทย',
            'tr' => 'Türkçe',
            'ru' => 'Русский',
            'uk' => 'Українська',
            'el' => 'Ελληνικά',
            'ja' => '日本語',
            'hu' => 'Magyar',
            'nb-NO' => 'Norsk bokmål',
            'nn-NO' => 'Nynorsk',
            'zh-CN' => '中文(简体)',
            'zh-TW' => '中文(台灣)',
            'an' => 'Aragonés',
            'vi' => 'Tiếng Việt',
            'sv' => 'Svenska',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'uz' => 'Ўзбек',
            'fa-IR' => 'فارسی',
            'bg' => 'Български',
            'sk' => 'Slovenčina',
            'ro' => 'Română',
            'ar' => 'العربية/عربي‎‎',
            'ko' => '한국어',
            'id' => 'Bahasa Indonesia',
            'lt' => 'Lietuvių kalba',
            'ht' => 'Kreyòl ayisyen',
            'lv' => 'Latvijas',
            'sl' => 'Slovenščina',
            'hr' => 'Hrvatski',
            'am' => 'አማርኛ',
            'fi' => 'Suomalainen',
            'he' => 'עברית',
            'sq' => 'Shqip',
            'cy' => 'Cymraeg',
            'sw' => 'Kiswahili',
            'sr' => 'Сербисцх',
            'eu' => 'Basque',
        ],
        'ldap' => [
            // LDAP date field formats
            'dateFields' => [
                //'birthday' => 'Y.m.d'
            ],
        ],
        'formatter' => [
            // Default date format, used especially in DatePicker widgets
            // Deprecated: Use Yii::$app->formatter->dateInputFormat instead.
            'defaultDateFormat' => 'short',
            // Seconds before switch from relative time to date format
            // Set to false to always use relative time in TimeAgo Widget
            'timeAgoBefore' => 172800,
            // Use static timeago instead of timeago js
            'timeAgoStatic' => false,
            // Seconds before hide time from timeago date
            // Set to false to always display time
            'timeAgoHideTimeAfter' => 259200,
            // Optional: Callback for TimageAgo FullDateFormat
            //'timeAgoFullDateCallBack' => function($timestamp) {
            //    return 'formatted';
            //}
        ],
        'humhub' => [
            // Marketplace / New Version Check
            'apiEnabled' => true,
            'apiUrl' => 'https://api.humhub.com',
        ],
        'curl' => [
            // Check SSL certificates on cURL requests
            'validateSsl' => true,
        ],
        // Allowed languages limitation (optional)
        'allowedLanguages' => [],
        'defaultPermissions' => [],
        'richText' => [
            'class' => \humhub\modules\content\widgets\richtext\ProsemirrorRichText::class,
        ],
        'twemoji' => [
            'path' => '@web-static/img/twemoji/',
            'size' => '72x72',
        ],
        'enablePjax' => true,
        'dailyCronExecutionTime' => '18:00',
    ],
];

return $config;
