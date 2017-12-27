<?php
namespace App;

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
    public static function transform($instance, array $date): array
    {
        /** @var TransformerInterface $target */
        $target = app($instance);
        if (!$target instanceof TransformerInterface) {
            throw new RuntimeException("{$instance} is not TransformerInterface!");
        }
        return $target->transform($date);
    }
}
