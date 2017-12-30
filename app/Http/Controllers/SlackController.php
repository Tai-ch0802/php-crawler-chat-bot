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

        try {
            $client = new Client();
            $client->request(
                'POST',
                $request->input('response_url'),
                [
                    'headers' => ['content-type' => 'application/json'],
                    'body' => json_encode($response),
                ]
            );
        } catch (\Exception $exception) {
            $data = var_export($exception, true);
            $response = $slackService->buildSlashCommandResponse(
                '訊息回送失敗！',
                "```{$data}```",
                [],
                SlackService::SLASH_COMMAND_REPLY_PUBLIC,
                SlackService::ATTACH_COLOR_RED
            );
            return response()->json($response);
        }
    }
}
