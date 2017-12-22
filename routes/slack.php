<?php

Route::middleware('slash')->group(function () {
    Route::post('/slash-commands/twitch', 'SlackController@replySlashCommandTwitch');
});
