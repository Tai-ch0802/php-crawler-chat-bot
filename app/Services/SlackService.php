<?php
namespace App\Services;

use App\Models\SlackMember;
use Maknz\Slack\Client as SlackClient;

class SlackService
{
    public const SLASH_COMMAND_REPLY_PUBLIC = 'in_channel';
    public const SLASH_COMMAND_REPLY_PRIVATE = 'ephemeral';

    public const ATTACH_COLOR_BLUE = '#000079';
    public const ATTACH_COLOR_RED = '#FF0000';
    public const ATTACH_COLOR_GREEN = '#00BB00';
    public const ATTACH_COLOR_ORANGE = '#FF5809';


    /** @var SlackClient */
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

    /**
     * @param string $title
     * @param string $text
     * @param array $fields
     * @param string $responseType
     * @param string $color
     * @return array
     */
    public function buildSlashCommandResponse(
        string $title,
        string $text,
        array $fields = [],
        string $responseType = self::SLASH_COMMAND_REPLY_PRIVATE,
        string $color = self::ATTACH_COLOR_BLUE
    ): array {
        return [
            'response_type' => $responseType,
            'attachments' => [
                [
                    'title' => $title,
                    'text' => $text,
                    'fields' => $fields,
                    'color' => $color,
                    'mrkdwn_in' => [
                        'text',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $userId
     * @param string $userName
     * @return SlackMember
     */
    public function getUpdater(string $userId, string $userName): SlackMember
    {

        $updater = SlackMember::where('user_id', $userId)->get()->first();

        if (null === $updater) {
            $updater = new SlackMember();
            $updater->user_name = $userName;
            $updater->user_id = $userId;
            $updater->save();
        }
        return $updater;
    }
}
