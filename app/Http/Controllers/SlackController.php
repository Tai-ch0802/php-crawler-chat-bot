<?php
namespace App\Http\Controllers;

use App\Services\SlackService;
use App\Services\TwitchService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackController extends Controller
{
    public function replySlashCommandTwitch(
        TwitchService $twitchService,
        SlackService $slackService,
        Request $request
    ) {
        $operator = $slackService->getUpdater(
            $request->input('user_id'),
            $request->input('user_name')
        );
        $text = empty($text = $request->input('text', '')) ? 'help' : $text;

        $response = $twitchService->replySlashCommand($text, $operator);

        $client = new Client([
            'headers' => ['content-type' => 'application/json']
        ]);
        $async = $client->requestAsync('POST', $request->input('response_url'), json_encode($response));
        $async->then(function ($response) use ($request) {
            Log::info("Got a response! status:{$response->getStatusCode()} url:{$request->input('response_url')}");
        });
        sleep(5);
        return response()->json($response);
    }
}
