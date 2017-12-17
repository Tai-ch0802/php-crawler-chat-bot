<?php
namespace App\Services;

use Maknz\Slack\Client;

class SlackService
{
    public function __construct()
    {
    }

    public function fake()
    {
    }

    /**
     * @param string $webhook
     * @param string $channel
     * @param string $userName
     * @return Client
     */
    public function buildClient(string $webhook, string $channel = '', string $userName = ''): Client
    {
        $client = new Client($webhook);
        if (!empty($channel)) {
            $client->setDefaultChannel($channel);
        }
        if (!empty($userName)) {
            $client->setDefaultUsername($userName);
        }
        return $client;
    }

    /**
     * @param Client $slackClient
     * @param string $message
     * @param array $attach
     */
    public function sendMessage(Client $slackClient, string $message, array $attach = []): void
    {
        $message = $slackClient->createMessage()->setText($message)->setAttachments($attach);
        $slackClient->sendMessage($message);
    }
}
