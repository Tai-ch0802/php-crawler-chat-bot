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
                $fields = $twitchService->buildFollowList();
                $response = $slackService->buildSlashCommandResponse(
                    'Twitch追蹤名單',
                    "目前共有 {$fields->count()} 位實況主追蹤中",
                    $fields->toArray()
                );
                break;

            case 'add':
                //TODO add new item, example: /twitch add <name> <channelName>
                break;

            case 'delete':
                //TODO add new item, example: /twitch delete <channelName>
                //TODO add permission(?)
                break;

            default:
                $fields = [
                    [
                        'title' => '/twitch list',
                        'value' => '查看現在已經在追蹤的實況主名單',
                    ],
                    [
                        'title' => '/twitch add <實況主名稱> <實況主頻道id>',
                        'value' => "新增追蹤實況主\n舉例：/twitch add 小熊 yuniko0720",
                    ],
                    [
                        'title' => '/twitch add <實況主名稱> <實況主頻道id>',
                        'value' => "刪除追蹤對象\n舉例：/twitch delete yuniko0720",
                    ],
                ];
                $response = $slackService->buildSlashCommandResponse(
                    'Twitch支援指令清單',
                    '你可以輸入以下指令',
                    $fields
                );
        }
        $response['response_url'] = $request->input('response_url');
        return response()->json($response);
    }
}
