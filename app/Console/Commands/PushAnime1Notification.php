<?php

namespace App\Console\Commands;

use App\Helper;
use App\Services\CrawlerService;
use App\Services\SlackService;
use App\Transformers\Slack\PushAnimationTransformer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

class PushAnime1Notification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push-anime1-animation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送Anime1新番動畫通知';

    /**
     * @var CrawlerService
     */
    private $crawlerService;

    /** @var SlackService  */
    private $slackService;

    private $serviceName;
    private $endpoint;
    private $icon;
    private $image;

    /**
     * PushAnime1Notification constructor.
     * @param CrawlerService $crawlerService
     * @param SlackService $slackService
     */
    public function __construct(
        CrawlerService $crawlerService,
        SlackService $slackService
    ) {
        parent::__construct();
        $this->crawlerService = $crawlerService;
        $this->slackService = $slackService;

        $this->serviceName = config('services.anime1.name');
        $this->endpoint = config('services.anime1.endpoint');
        $this->icon = config('services.anime1.icon');
        $this->image = config('services.anime1.image');

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $list = $this->getList();
        $lastRecord = $this->getLastRecord();

        $targets = [];

        foreach ($list as $item) {
            if ($item['directUri'] !== $lastRecord) {
                $targets[] = $item;
            }else {
                break;
            }
        }

        if (empty($targets)) {
            echo "No data refresh\n";
            return;
        }

        $targets = array_map(function ($target) {
            return Helper::slackTransform(PushAnimationTransformer::class, $target);
        }, $targets);

        $this->slackService->sendMessage(
            "{$this->serviceName} 最新番來啦！",
            $targets,
            '#animation',
            $this->serviceName
        );

        $this->refreshLastRecord($targets[0]['title_link']);

        echo "Good luck!\n";
    }

    private function refreshLastRecord($lastRecord)
    {
        Cache::store('redis')->pull('LAST_ANIME1_ANIMATION');
        Cache::store('redis')->forever('LAST_ANIME1_ANIMATION', $lastRecord);
    }


    private function getLastRecord()
    {
        if (!Cache::store('redis')->has('LAST_ANIME1_ANIMATION')) {
            return null;
        }
        return Cache::store('redis')->get('LAST_ANIME1_ANIMATION');
    }

    private function getList()
    {
        $crawler = $this->crawlerService->getOriginalData('https://anime1.me/feed');

        $list = $crawler
            ->filterXPath('//item')
            ->each(function (Crawler $node) {
                preg_match('/(<link>)(?P<link>https:\/\/anime1.me\/\d+)/', $node->html(), $link);
                return [
                    'authorName' => $this->serviceName,
                    'authorLink' =>  $this->endpoint,
                    'authorIcon' =>  $this->icon,
                    'label' => $node->filterXPath('//title')->text(),
                    'directUri' => $link['link'],
                    'text' => '更新時間：' . $node->filterXPath('//pubdate')->text(),
                    'imagePath' => $this->image,
                ];
            });
        return $list;
    }
}
