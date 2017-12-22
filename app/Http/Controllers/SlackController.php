<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;

class SlackController extends Controller
{
    public function slashCommandTwitch(Request $request)
    {
        $text = $request->input('text', '');
        $data = explode(' ', $text);
        $command = $data[0];

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
                //TODO --help
        }
    }
}