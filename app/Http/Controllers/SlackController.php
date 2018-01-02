<?php
namespace App\Http\Controllers;

use App\Services\ComicService;
use App\Services\SlackService;
use App\Services\TwitchService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SlackController extends Controller
{
    /**
     * @param TwitchService $twitchService
     * @param SlackService $slackService
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        $responseUrl = $request->input('response_url');

        $payload = $request->input('payload', []);
        if($payload !== []) {
        dd($payload);
        }
        $responseUrl = $payload['response_url'] ?? $responseUrl;

        $data = $twitchService->replySlashCommand($text, $payload, $operator);

        if (null !== $response = $this->sendResponseUrl($responseUrl, $data)) {
            return response()->json($response);
        }
    }

    /**
     * @param ComicService $comicService
     * @param SlackService $slackService
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        $responseUrl = $request->input('response_url');

        $payload = $request->input('payload', []);
        $responseUrl = $payload['response_url'] ?? $responseUrl;

        $data = $comicService->replySlashCommand($text, $payload, $operator);

        if (null !== $response = $this->sendResponseUrl($responseUrl, $data)) {
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
            $response = $slackService->buildSlackMessages(
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
