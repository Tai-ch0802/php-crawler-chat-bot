<?php

namespace App\Console\Commands;

use App\Helper;
use App\Models\Twitch;
use App\Services\LineBotService;
use App\Services\SlackService;
use App\Services\TwitchService;
use App\Transformers\Slack\PushTwitchTransformer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushTwitchNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push-twitch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送追蹤的開台實況主';

    /** @var TwitchService  */
    private $twitchService;

    /** @var LineBotService  */
    private $lineBotService;

    /** @var SlackService  */
    private $slackService;

    /**
     * PushTwitchNotification constructor.
     * @param SlackService $slackService
     * @param LineBotService $lineBotService
     * @param TwitchService $twitchService
     */
    public function __construct(
        SlackService $slackService,
        LineBotService $lineBotService,
        TwitchService $twitchService
    ) {
        parent::__construct();
        $this->twitchService = $twitchService;
        $this->lineBotService = $lineBotService;
        $this->slackService = $slackService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function handle()
    {
        $broadcastList = $this->twitchService->getAll()->transform(function (Twitch $twitch) {
            return $twitch->channel_name;
        })->toArray();

        $targets = array_map(function ($broadcast) {
            $response = json_decode(
                $this->twitchService->getLiveStreams($broadcast)->getBody()->getContents(),
                true
            );
            if (!empty($response['streams'])) {
                $stream = array_first($response['streams']);
                if (!$this->isWithinTenMinutesAgo($stream['created_at'])) {
                    return null;
                }

                $channel = $stream['channel'];
                return [
                    'authorName' => $channel['display_name'],
                    'authorIcon' =>  $channel['logo'],
                    'label' => $channel['status'],
                    'directUri' => $channel['url'],
                    'text' => "當前觀看數量: {$stream['viewers']}\n總觀看次數: {$channel['views']}",
                    'imagePath' => $channel['video_banner'],
                ];
            }
            return null;
        }, $broadcastList);

        $targets = array_filter($targets, function ($d) {
            return null !== $d;
        });
        if (empty($targets)) {
            echo 'none twitch';
            return;
        }

        $targets = array_map(function ($target) {
            return Helper::transform(PushTwitchTransformer::class, $target);
        }, $targets);
        $this->slackService->sendMessage('最新實況來啦!', $targets, '#twitch', '實況播報員');

        echo 'enjoy it!';
    }


    private function isWithinTenMinutesAgo(string $time)
    {
        $target = Carbon::createFromFormat(DATE_ATOM, $time, 'UTC')->timestamp;
        $tenMinutes = now()->subMinutes(11)->timestamp;

        if ($target >= $tenMinutes) {
            return true;
        }
        return false;
    }
}
