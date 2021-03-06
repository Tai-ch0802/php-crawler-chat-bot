<?php
namespace App\Services;

use App\Helper;
use App\Models\Comic;
use App\Models\SlackMember;
use App\SlashCommands\ComicAdd;
use App\SlashCommands\ComicDefault;
use App\SlashCommands\ComicDelete;
use App\SlashCommands\ComicList;
use Illuminate\Support\Collection;

class ComicService
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {
        return Comic::all();
    }

    /**
     * @return Collection
     */
    public function buildFollowList(Collection $collection, int $currentPage = 1, int $perPage = 4): Collection
    {
        $target = $collection->forPage($currentPage, $perPage);

        $target->transform(function (Comic $item) {
            return [
                'title' => $item->name,
                'value' => implode(PHP_EOL, [
                    "漫畫網址： {$this->url}{$item->comic_id}",
                    "追蹤日期： {$item->created_at}",
                    "追蹤建立人： {$item->creator->user_name}",
                ]),
            ];
        });

        return $target;
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function buildFilterOption(Collection $collection): array
    {
        return $collection->transform(function (Comic $item) {
            return [
                'text' => $item->name,
                'value' => json_encode([
                    'title' => $item->name,
                    'value' => implode(PHP_EOL, [
                        "漫畫網址： {$this->url}{$item->comic_id}",
                        "追蹤日期： {$item->created_at}",
                        "追蹤建立人： {$item->creator->user_name}",
                    ]),
                ]),
            ];
        })->toArray();
    }

    /**
     * @param string $comicId
     * @param SlackMember $updater
     * @return array|null
     */
    public function buildNewSubscription(string $comicId, SlackMember $updater): ?array
    {
        $comicName = $this->getComicName($comicId);
        if (null === $comicName) {
            return null;
        }

        //FIXME use repository(?)
        $comic = new Comic();
        $comic->comic_id = $comicId;
        $comic->name = $comicName;
        $comic->created_by = $updater->id;
        $comic->updated_by = $updater->id;
        $comic->save();

        return [
            [
                'title' => $comic->name,
                'value' => implode(PHP_EOL, [
                    "漫畫網址： {$this->url}{$comic->comic_id}",
                    "追蹤日期： {$comic->created_at}",
                    "追蹤建立人： {$comic->creator->user_name}",
                ]),
            ],
        ];
    }

    /**
     * @param string $comicId
     * @param SlackMember $updater
     * @return array|null
     * @throws \Exception
     */
    public function buildDeleteSubscription(string $comicId, SlackMember $updater): ?array
    {
        //FIXME use repository(?)
        /** @var Comic $comic */
        $comic = Comic::where('comic_id', $comicId)->first();
        if (null === $comic) {
            return null;
        }

        $data = [
            [
                'title' => $comic->name,
                'value' => implode(PHP_EOL, [
                    "漫畫網址： {$this->url}{$comic->comic_id}",
                    "追蹤日期： {$comic->created_at}",
                    "追蹤建立人： {$comic->creator->user_name}",
                    "刪除操作者： {$updater->user_name}",
                ]),
            ],
        ];

        $comic->delete();

        return $data;
    }

    /**
     * @param string $comicId
     * @return null|string
     */
    public function getComicName(string $comicId): ?string
    {
        /** @var CrawlerService $crawlerService */
        $crawlerService = app(CrawlerService::class);

        $crawler = $crawlerService->getOriginalData($this->url . $comicId);
        try {
            $name = $crawler->filterXPath('//table[@class="cInfoTxt"]/tr[1]/td/h1')->text();
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
        return $crawlerService->getFilterString($name);
    }

    /**
     * @param string $text
     * @param SlackMember $operator
     * @return array
     */
    public function replySlashCommand(string $text, SlackMember $operator): array
    {
        $command = explode(' ', $text);
        $action = array_first($command);

        $actions = [
            'list' => ComicList::class,
            'add' => ComicAdd::class,
            'delete' => ComicDelete::class,
        ];
        $instance = array_get($actions, $action, ComicDefault::class);

        return Helper::toSlashCommand($instance, $command, $operator);
    }

    /**
     * @param array $payload
     * @param SlackMember $operator
     * @return array
     */
    public function replyInteractiveSlashCommand(array $payload, SlackMember $operator): array
    {
        $command = explode(' ', $payload['callback_id']);
        $command['payload'] = $payload;
        $action = array_first($command);

        $actions = [
            'button_list' => ComicList::class,
            'filter_list' => ComicList::class,
        ];
        $instance = array_get($actions, $action, ComicDefault::class);

        return Helper::toSlashCommand($instance, $command, $operator);
    }
}
