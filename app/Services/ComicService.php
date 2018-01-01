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
     * @return Collection
     */
    public function buildFollowList(): Collection
    {
        //FIXME use repository(?)
        $collection = Comic::all();

        $collection->transform(function (Comic $item) {
            return [
                'title' => $item->name,
                'value' => implode(PHP_EOL, [
                    "漫畫網址： {$this->url}{$item->comic_id}",
                    "追蹤日期： {$item->created_at}",
                    "追蹤建立人： {$item->creator->user_name}",
                ]),
            ];
        });

        return $collection;
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
    public function replySlashCommand(string $text, SlackMember $operator)
    {
        $command = explode(' ', $text);
        $action = $command[0];

        $actions = [
            'list' => ComicList::class,
            'add' => ComicAdd::class,
            'delete' => ComicDelete::class,
        ];
        $instance = array_get($actions, $action, ComicDefault::class);

        return Helper::toSlashCommand($instance, $command, $operator);
    }
}
