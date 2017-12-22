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
        $text = $request->input('text', '');
        $data = explode(' ', $text);
        $command = $data[0];
        $response = [];
        switch ($command) {
            case 'list':
                //TODO show list
                break;

            case 'add':
                //TODO add new item, example: /twitch add <name> <channelName>
                break;

            case 'delete':
                //TODO add new item, example: /twitch delete <channelName>
                //TODO add permission(?)
                break;

            default:
                $content = [
                    '**/twitch list**',
                    '   查看現在已經在追蹤的實況主名單',
                    '**/twitch add < 實況主名稱 > < 實況主頻道id >**',
                    '   舉例：/twitch add 小熊 yuniko0720',
                    '**/twitch delete < 實況主頻道id >**',
                    '   舉例：/twitch delete yuniko0720',
                ];
                $response = $slackService->buildSlashCommandResponse(
                    '你可以輸入以下指令',
                    implode(PHP_EOL, $content)
                );
        }
        return response()->json($response);
    }
}
