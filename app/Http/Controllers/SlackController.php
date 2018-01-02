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

        $data = $twitchService->replySlashCommand($text, $operator);

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

        $data = $comicService->replySlashCommand($text, $operator);

        if (null !== $response = $this->sendResponseUrl($responseUrl, $data)) {
            return response()->json($response);
        }
    }

    public function replyInteractiveSlashCommand(
        SlackService $slackService,
        Request $request
    ) {
        $payload = json_decode($request->input('payload'));
        if (null === $payload) {
            //TODO Throw Exception
        }
        $operator = $slackService->getUpdater(
            $payload['user']['id'],
            $payload['user']['name']
        );
        $command = explode(' ', $payload['callback_id']);
        $text = $command[0];
        $instance = $command[1];
        $responseUrl = $payload['response_url'];
        $data = app($instance)->replySlashCommand($text, $operator, $payload);

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
