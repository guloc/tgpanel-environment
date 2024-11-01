<?php declare(strict_types=1);
define("ROCKET_SCRIPT", "v1.2");
set_time_limit(0);

use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;

if (php_sapi_name() !== 'cli')
    die();

require_once(__DIR__ . '/app/libraries/MadelineProto/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

$cfg_error = 'Troubles with config ...';
if (isset($json_config) && ! empty($json_config)) {
    $json_config = (array) @json_decode($json_config);
    if (is_array($json_config))
        foreach($json_config as $def_key => $new_def)
            define('RS_' . $def_key, $new_def);
    else
        die($cfg_error);
} else
    die($cfg_error);

class BasicEventHandler extends SimpleEventHandler
{

    public $httpClient;

    /**
     * Handle incoming updates from users, chats and channels.
     */
    #[Handler]
    public function handleMessage(Incoming&Message $message): void
    {
        if (empty($this->httpClient))
            $this->httpClient = \Amp\Http\Client\HttpClientBuilder::buildDefault();
        $handlerUrl = RS_SITE_URL . '/update_handler?key=' . RS_KEY;
        $request = new \Amp\Http\Client\Request($handlerUrl, 'POST');
        $request->setBody(json_encode($message));
        $response = $this->httpClient->request($request);
    }
}

// $settings = (new \danog\MadelineProto\Settings\Logger)->setLevel(0);
// BasicEventHandler::startAndLoop('session.madeline', $settings);
BasicEventHandler::startAndLoop('session.madeline');
