<?php

namespace App\Providers;

use App\Services\BangumiService;
use App\Services\ComicService;
use App\Services\LineBotService;
use App\Services\SlackService;
use App\Services\TwitchService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Maknz\Slack\Client as SlackClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->lineBotRegister();
        $this->lineBotServiceRegister();
        $this->slackServiceRegister();
        $this->twitchServiceRegister();
        $this->comicServiceRegister();
    }

    private function lineBotRegister()
    {
        $this->app->singleton(LINEBot::class, function () {
            $httpClient = new CurlHTTPClient(env('LINEBOT_TOKEN'));
            return new LINEBot($httpClient, ['channelSecret' => env('LINEBOT_SECRET')]);
        });
    }

    private function lineBotServiceRegister()
    {
        $this->app->singleton(LineBotService::class, function () {
            return new LineBotService(env('LINE_USER_ID'));
        });
    }

    private function slackServiceRegister()
    {
        $this->app->singleton(SlackService::class, function () {
            $setting = [
                'channel' => config('services.slack.channel'),
                'username' => config('services.slack.username'),
            ];
            $client =  new SlackClient(env('SLACK_WEBHOOK_URL'), $setting);
            return new SlackService($client);
        });
    }

    private function twitchServiceRegister()
    {
        $this->app->singleton(TwitchService::class, function () {
            $client = new Client([
                    'base_uri' => config('services.api.twitch'),
                    'headers' => ['Client-ID' => env('TWITCH_CLIENT_ID')],
                ]);
            return new TwitchService($client, config('services.url.twitch'));
        });
    }

    private function comicServiceRegister()
    {
        $this->app->singleton(ComicService::class, function () {
            $endpoint = config('services.url.comic99770');
            return new ComicService($endpoint);
        });
    }

    private function bangumiServiceRegister()
    {
        $this->app->singleton(BangumiService::class, function () {
            $id = env('BGM_APP_ID');
            $secret = env('BGM_SECRET');
            return new BangumiService($id, $secret);
        });
    }
}
