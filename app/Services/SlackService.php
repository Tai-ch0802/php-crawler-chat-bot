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

    private $slackMessage;

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
    public function buildSlackMessages(
        string $title,
        string $text,
        array $fields = [],
        string $responseType = self::SLASH_COMMAND_REPLY_PRIVATE,
        string $color = self::ATTACH_COLOR_BLUE
    ): array {
        $this->slackMessage = [
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
        return $this->slackMessage;
    }

    /**
     * @param int $currentPage
     * @param int $totalPage
     * @param string $serviceName
     * @return array
     */
    public function attachPage(int $currentPage, int $totalPage, string $serviceName): array
    {
        if (empty($this->slackMessage)) {
            throw new \RuntimeException('The slackMessage is empty');
        }
        $this->slackMessage['replace_original'] = true;
        $clone = $this->slackMessage['attachments'];
        $clone[0]['callback_id'] = 'button_list ' . $serviceName;
        $clone[0]['fallback'] = 'There is no data.';
        if ($currentPage > 1) {
            $clone[0]['actions'][] = [
                'name' => 'page',
                'text' => '上一頁',
                'type' => 'button',
                'style' => 'primary',
                'value' => $currentPage - 1
            ];
        }
        if ($currentPage !== $totalPage) {
            $clone[0]['actions'][] = [
                'name' => 'page',
                'text' => '下一頁',
                'type' => 'button',
                'style' => 'primary',
                'value' => $currentPage + 1
            ];
        }
        $this->slackMessage['attachments'] = $clone;
        return $this->slackMessage;
    }

    /**
     * @param array $options
     * @param $text
     * @param string $serviceName
     * @return array
     */
    public function attachFilter(array $options, string $text, string $serviceName): array
    {
        if (empty($this->slackMessage)) {
            throw new \RuntimeException('The slackMessage is empty');
        }
        $this->slackMessage['replace_original'] = true;

        $clone = $this->slackMessage['attachments'];


        $attachment = [
            'title' => '尋找你想看的',
            'text' => $text,
            'fallback' => 'What?',
            'color' => self::ATTACH_COLOR_GREEN,
            'callback_id'  => 'filter_list ' . $serviceName,
            'actions' => [
                [
                    'name' => 'filter',
                    'text' => '選我選我',
                    'type' => 'select',
                    'options' => $options,
                ]
            ]
        ];
        array_unshift($clone, $attachment);

        $this->slackMessage['attachments'] = $clone;
        return $this->slackMessage;
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
