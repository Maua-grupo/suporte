<?php

require_once __DIR__ . "/" . "/../../../../includes/env.php";
loadEnvFile(dirname(__DIR__, 4) . "/.env");
require_once __DIR__ . "/" . "DBConfig.php";
require_once __DIR__ . "/" . "../../vendor/coffeecode/datalayer/src/Connect.php";
require_once __DIR__ . "/" . "../../vendor/coffeecode/datalayer/src/CrudTrait.php";
require_once __DIR__ . "/" . "../../vendor/coffeecode/datalayer/src/DataLayer.php";
require_once __DIR__ . "/" . "../Models/Config.php";
require_once __DIR__ . "/" . "../Models/MailConfig.php";


// use CoffeeCode\DataLayer\DataLayer;
use OcomonApi\Models\Config;
use OcomonApi\Models\MailConfig;

$config = (new Config())->findById(1);
$configData = ($config && method_exists($config, "data") && $config->data() ? $config->data() : (object)["conf_ocomon_site" => ""]);
$publicAppUrl = rtrim((string)envValue('APP_URL', (string)$configData->conf_ocomon_site), '/');
$apiAddress = $publicAppUrl . "/api/ocomon_api/";

$mailConfig = (new MailConfig())->findById(1);
$mailConfigData = ($mailConfig && method_exists($mailConfig, "data") && $mailConfig->data() ? $mailConfig->data() : (object)[
    "mail_send" => 0,
    "mail_host" => "",
    "mail_port" => 587,
    "mail_user" => "",
    "mail_pass" => "",
    "mail_from_name" => "",
    "mail_from" => "",
    "mail_ishtml" => 1,
    "mail_isauth" => 1,
    "mail_secure" => "tls"
]);

/**
 * PROJECT URLs
 */
define("CONF_URL_BASE", $apiAddress);
define("CONF_URL_TEST", $apiAddress);

/**
 * UPLOAD
 */
define("CONF_UPLOAD_DIR", "storage");


/**
 * PASSWORD - HASH
*/
define("CONF_PASSWD_ALGO", PASSWORD_DEFAULT);
define("CONF_PASSWD_OPTION", ["cost => 10"]);


/* E-mail SMTP*/
define("CONF_MAIL_SEND", envBool("MAIL_SEND", (bool)$mailConfigData->mail_send));
define("CONF_MAIL_HOST", envValue("MAIL_HOST", (string)$mailConfigData->mail_host));
define("CONF_MAIL_PORT", (int)envValue("MAIL_PORT", (string)$mailConfigData->mail_port));
define("CONF_MAIL_USER", envValue("MAIL_USER", (string)$mailConfigData->mail_user));
define("CONF_MAIL_PASS", envValue("MAIL_PASS", (string)$mailConfigData->mail_pass));
define("CONF_MAIL_SENDER", [
    "name" => envValue("MAIL_FROM_NAME", (string)$mailConfigData->mail_from_name),
    "address" => envValue("MAIL_FROM", (string)$mailConfigData->mail_from)
]);
define("CONF_MAIL_SUPPORT", envValue("MAIL_FROM", (string)$mailConfigData->mail_from));

define("CONF_MAIL_OPTION_LANG", "br");
define("CONF_MAIL_OPTION_HTML", envBool("MAIL_HTML", (bool)$mailConfigData->mail_ishtml));
define("CONF_MAIL_OPTION_AUTH", envBool("MAIL_AUTH", (bool)$mailConfigData->mail_isauth));
define("CONF_MAIL_OPTION_SECURE", envValue("MAIL_SECURE", (string)$mailConfigData->mail_secure));
define("CONF_MAIL_OPTION_CHARSET", "utf-8");
