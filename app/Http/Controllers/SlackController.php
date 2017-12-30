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
        
        $async = $client->requestAsync(
            'POST',
            $request->input('response_url'),
            ['body' => json_encode($response)]
        )->getBody()->getContents();

        return response()->json('', 204);
    }
}
