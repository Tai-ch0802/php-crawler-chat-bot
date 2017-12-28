<?php
namespace App\Http\Controllers;

use App\Services\SlackService;
use App\Services\TwitchService;
use Illuminate\Http\Request;

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
        $text = $request->input('text', '--help');
        if (empty($text)) {
            $text = '--help';
        }

        $response = $twitchService->replySlashCommand($text, $operator);

        $response['response_url'] = $request->input('response_url');
        return response()->json($response);
    }
}
