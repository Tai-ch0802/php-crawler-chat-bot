<?php
namespace App\Services;

use Maknz\Slack\Client as SlackClient;

class SlackService
{
    /** @var SlackClient  */
    private $client;

    public function __construct(SlackClient $client)
    {
        $this->client = $client;
    }

    public function fake()
    {
    }

    /**
     * @return SlackClient
     */
    public function getClient(): SlackClient
    {
        return $this->client;
    }

    /**
     * @param string $webhook
     * @param string $channel
     * @param string $userName
     * @return $this
     */
    public function setClient(string $webhook, string $channel = '', string $userName = '')
    {
        $this->client = new SlackClient($webhook);
        if (!empty($channel)) {
            $this->client->setDefaultChannel($channel);
        }
        if (!empty($userName)) {
            $this->client->setDefaultUsername($userName);
        }
        return $this;
    }

    /**
     * @param string $message
     * @param array $attach
     * @param string $channel
     * @param string $userName
     */
    public function sendMessage(
        string $message,
        array $attach = [],
        string $channel = '',
        string $userName = ''
    ): void {
        $message = $this->client->createMessage()->setText($message)->setAttachments($attach);
        if (!empty($channel)) {
            $message->to($channel);
        }
        if (!empty($userName)) {
            $message->from($userName);
        }
        $this->client->sendMessage($message);
    }
}
