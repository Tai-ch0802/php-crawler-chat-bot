<?php
namespace App;

use App\Models\SlackMember;
use App\SlashCommands\SlashCommandsInterface;
use App\Transformers\TransformerInterface;
use RuntimeException;

class Helper
{
    /**
     * @param $instance
     * @param array $date
     * @return array
     * @throws RuntimeException
     */
    public static function slackTransform($instance, array $date): array
    {
        /** @var TransformerInterface $target */
        $target = app($instance);
        if (!$target instanceof TransformerInterface) {
            throw new RuntimeException("{$instance} is not TransformerInterface!");
        }
        return $target->transform($date);
    }

    /**
     * @param $instance
     * @param array $command
     * @param SlackMember $operator
     * @return array
     */
    public static function toSlashCommand($instance, array $command, SlackMember $operator): array
    {
        /** @var SlashCommandsInterface $target */
        $target = app($instance, [
            'command' => $command,
            'operator' => $operator
        ]);
        if (!$target instanceof SlashCommandsInterface) {
            throw new RuntimeException("{$instance} is not SlashCommandsInterface!");
        }
        return $target->buildReply();
    }
}
