<?php

namespace App\Providers;

use App\Services\LineBotService;
use App\Services\SlackService;
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
        //
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
}
