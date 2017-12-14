<?php

namespace App\Console\Commands;

use App\Services\CrawlerService;
use App\Services\LineBotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushComicNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push-99770-comic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送當天最新的追蹤漫畫';


    private $path;

    /** @var CrawlerService  */
    private $crawlerService;

    /** @var LineBotService  */
    private $lineBotService;

    /**
     * @var array 追蹤的動畫清單
     */
    protected static $comicList = [
        'onePiece' => '170',
        'tokyoGhoul' => '25010',
    ];

    /**
     * PushComicNotification constructor.
     * @param CrawlerService $crawlerService
     * @param LineBotService $lineBotService
     */
    public function __construct(CrawlerService $crawlerService, LineBotService $lineBotService)
    {
        parent::__construct();
        $this->path = config('services.url.comic99770');
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
        $today = Carbon::today();
        $filterDays = [
            $today->format('Y-m-d'),
            $today->subDay(1)->format('Y-m-d'),
        ];

        $data = [];
        foreach (self::$comicList as $key => $value) {
            $path = $this->path . $value;
            $crawler = $this->crawlerService->getOriginalData($path);
            $target = $this->crawlerService->getNewEpisodeFromComic99770($crawler);

            if (in_array($target['date'], $filterDays)) {
                $target['imagePath'] = 'https://i.imgur.com/3eue6GG.gif';
                $data[] = $target;
            }
        }

        $message = "{$today->format('m/d')} 最新的追蹤漫畫來囉！";
        $messageBuilders = $this->lineBotService->buildTemplateMessageBuilder($data, $message);

        foreach ($messageBuilders as $target) {
            $this->lineBotService->pushMessage($target);
        }

        echo "Good luck!\n";
    }
}
