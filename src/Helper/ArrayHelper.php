<?php


namespace App\Helper;

use JetBrains\PhpStorm\Pure;

class ArrayHelper
{
    /**
     * @param array $array
     * @param int $index
     * @return array
     */
    #[Pure] public static function rearrangeFromIndex(array $array, int $index): array
    {
        return array_merge(array_slice($array, $index), array_slice($array, 0, $index));
    }

    public static function prettyPrint(array $array): void
    {
        foreach ($array as $element) {
            echo $element . ' ';
        }
        echo "\n";
    }
}