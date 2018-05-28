<?php

namespace App\Console\Commands;

use App\Helper;
use App\Services\BangumiService;
use App\Services\SlackService;
use App\Transformers\Slack\PushBiliAnimationTransformer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PushBliAnimeNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push-bili-animation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送Bangumi當日新番動畫通知';

    /**
     * @var BangumiService
     */
    private $bangumiService;

    /** @var SlackService  */
    private $slackService;

    private $cacheKey;

    /**
     * PushBliAnimeNotification constructor.
     * @param BangumiService $bangumiService
     * @param SlackService $slackService
     */
    public function __construct(
        BangumiService $bangumiService,
        SlackService $slackService
    ) {
        parent::__construct();
        $this->bangumiService = $bangumiService;
        $this->slackService = $slackService;

        $this->clearOldCache();
        $this->cacheKey = today()->toDateString() . '-daily-manga';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subjectIds = $this->getTodayManga();
        $preRefreshCache = $subjectIds;

        $attach = [];
        foreach ($subjectIds as $subjectId => $value) {
            $data = $this->getEp($subjectId, $preRefreshCache);
            if (null !== $data) {
                $attach[] = Helper::slackTransform(PushBiliAnimationTransformer::class, $data);
            }
        }

        $this->refreshCache($preRefreshCache);

        if (!empty($attach)) {
            $this->slackService->sendMessage(
                '當日最新番情報來啦！',
                $attach,
                '#animation',
                'Bangumi'
            );
        }
        echo "Good luck!\n";
    }

    private function refreshCache(array $data)
    {
        if (Cache::store('redis')->has($this->cacheKey)) {
            Cache::store('redis')->put($this->cacheKey, $data, 1440);
        }
    }

    private function getTodayManga(): array
    {
        if (!Cache::store('redis')->has($this->cacheKey)) {
            $week = $this->bangumiService->getCalendar();
            $items = [];
            foreach ($week as $day) {
                if ($day['weekday']['id'] === (int)date('N')) {
                    $items = $day['items'];
                    break;
                }
            }
            if (empty($items)) {
                $message = PushBliAnimeNotification::class . '::getTodayManga() getCalendar failed.';
                throw new \RuntimeException($message);
            }
            $target = [];
            foreach ($items as $item) {
                $target[$item['id']] = $item['id'];
            }

            Cache::store('redis')->put($this->cacheKey, $target, 1440);
        }

        return Cache::store('redis')->get($this->cacheKey);
    }

    private function clearOldCache()
    {
        $oldCache = today()->subDay()->toDateString() . '-daily-manga';
        if (Cache::store('redis')->has($oldCache)) {
            Cache::store('redis')->forget($oldCache);
        }
    }

    /**
     * @param $subjectId
     * @param $preUpdateCache
     * @return array|null
     */
    private function getEp($subjectId, &$preUpdateCache)
    {
        $data = $this->bangumiService->getSubjectEps((int)$subjectId);
        $eps = $data['eps'];
        if (empty($eps)) {
            return null;
        }

        $target = null;
        $isSerial = false;
        foreach ($eps as $ep) {
            if ($ep['airdate'] === today()->toDateString()) {
                if ('Air' === $ep['status']) {
                    $target = $ep;
                    unset($preUpdateCache[$subjectId]);
                }
                $isSerial = true;
                break;
            }
        }

        // TODO 這邊為了過濾掉不是當季新番
        if (!$isSerial) {
            unset($preUpdateCache[$subjectId]);
        }

        if (null !== $target) {
            $target = [
                'name' => empty($data['name_cn']) ? $data['name'] : $data['name_cn'],
                'image' => empty($data['images']['large']) ? $data['images']['common'] : $data['images']['large'],
                'ep' => $target,
            ];
        }

        return $target;
    }
}
