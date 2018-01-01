<?php
namespace App\Http\Controllers;

use App\Services\ComicService;
use App\Services\SlackService;
use App\Services\TwitchService;
use GuzzleHttp\Client;
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
        $text = empty($text = $request->input('text', '')) ? 'help' : $text;
        $payload = $request->input('payload', []);

        $data = $twitchService->replySlashCommand($text, $payload, $operator);

        if (null !== $response = $this->sendResponseUrl($request->input('response_url'), $data)) {
            return response()->json($response);
        }
    }

    public function replySlashCommandComic(
        ComicService $comicService,
        SlackService $slackService,
        Request $request
    ) {
        $operator = $slackService->getUpdater(
            $request->input('user_id'),
            $request->input('user_name')
        );
        $text = empty($text = $request->input('text', '')) ? 'help' : $text;

        $data = $comicService->replySlashCommand($text, $operator);

        if (null !== $response = $this->sendResponseUrl($request->input('response_url'), $data)) {
            return response()->json($response);
        }
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    private function sendResponseUrl(string $endpoint, array $data): ?array
    {
        /** @var SlackService $slackService */
        $slackService = app(SlackService::class);
        try {
            $client = new Client();
            $client->request(
                'POST',
                $endpoint,
                [
                    'headers' => ['content-type' => 'application/json'],
                    'body' => json_encode($data),
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
            return $response;
        }

        return null;
    }
}
