<?php

namespace App\Console\Commands;

use App\Services\CrawlerService;
use App\Services\LineBotService;
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

    /**
     * PushAnimationNotification constructor.
     * @param CrawlerService $crawlerService
     * @param LineBotService $lineBotService
     */
    public function __construct(CrawlerService $crawlerService, LineBotService $lineBotService)
    {
        parent::__construct();
        $this->path = config('services.url.baHa');
        $this->crawlerService = $crawlerService;
        $this->lineBotService = $lineBotService;
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
        $list = array_map(function ($d) use ($today, &$existedList){
            if (
                false === strpos($d['date'], $today) ||
                in_array($d['label'], $existedList, true)
            ) {
                return null;
            }
            $existedList[] = $d['label'];

            if (mb_strlen($d['label'], 'UTF-8') > 12) {
                $d['label'] = mb_substr($d['label'], 0, 9, 'UTF-8') . '...';
            }
            return $d;
        }, $list);

        $target = array_filter($list, function ($d) {
            return null !== $d;
        });

        $message = "{$today} 最新動畫來囉！";
        $messageBuilders = $this->lineBotService->buildTemplateMessageBuilder($target, $message);

        foreach ($messageBuilders as $target) {
            $this->lineBotService->pushMessage($target);
        }

        echo "Good luck!\n";
    }
}
