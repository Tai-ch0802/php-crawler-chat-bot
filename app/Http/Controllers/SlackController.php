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
        $updater = $slackService->getUpdater(
            $request->input('user_id'),
            $request->input('user_name')
        );
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
                $presenterName = $data[1] ?? null;
                $channelName = $data[2] ?? null;
                if (in_array(
                    null,
                    [
                        $presenterName,
                        $channelName
                    ],
                    true
                )) {
                    break;
                }
                $fields = $twitchService->buildNewSubscription($presenterName, $channelName, $updater);
                $response = $slackService->buildSlashCommandResponse(
                    '有新實況主納入追蹤名單！',
                    '請參考以下資訊',
                    $fields,
                    SlackService::SLASH_COMMAND_REPLY_PUBLIC,
                    SlackService::ATTACH_COLOR_GREEN
                );
                break;

            case 'delete':
                //TODO add new item, example: /twitch delete <channelName>
                //TODO add permission(?)
                $channelName = $data[1] ?? null;
                if (null === $channelName) {
                    break;
                }
                $fields = $twitchService->buildDeleteSubscription($channelName, $updater);
                if (null === $fields) {
                    $response = $slackService->buildSlashCommandResponse(
                        '本來就沒有追蹤這頻道！',
                        '',
                        [],
                        SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                        SlackService::ATTACH_COLOR_RED
                    );
                    break;
                }

                $response = $slackService->buildSlashCommandResponse(
                    '有人從追蹤名單裡被除名了！',
                    '大家在看他最後一面吧！',
                    $fields,
                    SlackService::SLASH_COMMAND_REPLY_PUBLIC,
                    SlackService::ATTACH_COLOR_ORANGE
                );
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
                        'title' => '/twitch delete <實況主頻道id>',
                        'value' => "刪除追蹤對象\n舉例：/twitch delete yuniko0720",
                    ],
                ];
                $response = $slackService->buildSlashCommandResponse(
                    'Twitch支援指令清單',
                    '你可以輸入以下指令',
                    $fields
                );
        }

        if (empty($response)) {
            $response = $slackService->buildSlashCommandResponse(
                'Twitch輸入指令錯誤',
                '請輸入 `/twitch` 確認指令格式',
                [],
                SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                SlackService::ATTACH_COLOR_RED
            );
        }

        $response['response_url'] = $request->input('response_url');
        return response()->json($response);
    }
}
