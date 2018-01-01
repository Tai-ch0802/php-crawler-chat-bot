<?php

Route::middleware('slash')->group(function () {
    Route::post('/slash-commands/twitch', 'SlackController@replySlashCommandTwitch');
    Route::post('/slash-commands/comic', 'SlackController@replySlashCommandComic');
});
