<?php

namespace App\Console\Commands;

use App\Services\CrawlerService;
use App\Services\LineBotService;
use App\Services\SlackService;
use App\Transformers\Slack\PushAnimationTransformer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushAnimationNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push-baha-animation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送巴哈當季動畫通知';


    private $path;

    /** @var CrawlerService  */
    private $crawlerService;

    /** @var LineBotService  */
    private $lineBotService;

    /** @var SlackService  */
    private $slackService;

    /** @var PushAnimationTransformer  */
    private $pushAnimationTransformer;

    /**
     * PushAnimationNotification constructor.
     * @param CrawlerService $crawlerService
     * @param LineBotService $lineBotService
     * @param SlackService $slackService
     */
    public function __construct(
        CrawlerService $crawlerService,
        LineBotService $lineBotService,
        SlackService $slackService
    ) {
        parent::__construct();
        $this->path = config('services.url.baHa');
        $this->crawlerService = $crawlerService;
        $this->lineBotService = $lineBotService;
        $this->slackService = $slackService;
        $this->pushAnimationTransformer = app(PushAnimationTransformer::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $originalData = $this->crawlerService->getOriginalData($this->path);
        $list = $this->crawlerService->getNewAnimationFromBaHa($originalData);

        $today = today()->format('m/d');
        $existedList = [];
        $list = array_map(function ($d) use ($today, &$existedList) {
            if (false === strpos($d['date'], $today) ||
                in_array($d['label'], $existedList, true)
            ) {
                return null;
            }
            if (!$this->isCorrectTime($d['directUri'])) {
                return null;
            }

            $existedList[] = $d['label'];
            return $d;
        }, $list);

        $targets = array_filter($list, function ($d) {
            return null !== $d;
        });
        if (empty($targets)) {
            return;
        }

        $message = "{$today} 最新動畫來囉！";

        $messageBuilders = $this->getLineBotMessageBuilders($targets, $message);

        foreach ($messageBuilders as $messageBuilder) {
            $this->lineBotService->pushMessage($messageBuilder);
        }

        $targets = array_map(function ($target) {
            return $this->pushAnimationTransformer->transform($target);
        }, $targets);
        $this->slackService->sendMessage($message, $targets, '#animation', '動漫外送員');

        echo "Good luck!\n";
    }

    /**
     * @param array $target
     * @param string $message
     * @return array
     */
    private function getLineBotMessageBuilders(array $target, string $message): array
    {
        $content = array_map(function ($data) {
            if (mb_strlen($data['label'], 'UTF-8') > 12) {
                $data['label'] = mb_substr($data['label'], 0, 9, 'UTF-8') . '...';
            }
            return $data;
        }, $target);

        return $this->lineBotService->buildTemplateMessageBuilder($content, $message);
    }

    private function isCorrectTime($path): bool
    {
        $crawler = $this->crawlerService->getOriginalData($path);
        $data = $crawler->evaluate('substring-after(//div[@class="anime_name"]/p, "：")');

        $time = array_first($data);
        $time = htmlentities($time);
        $time = str_replace('&nbsp;', '', $time);

        $targetTime = Carbon::createFromFormat('Y-m-d H:i:s', trim($time))->timestamp;

        $tenMinutesAgo = Carbon::now()->subMinutes(11)->timestamp;


        if ($targetTime > $tenMinutesAgo) {
            return true;
        }
        return false;
    }
}
