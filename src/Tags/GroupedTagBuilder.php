<?php
declare(strict_types=1);

namespace Crustum\Meta\Tags;

/**
 * Base for tag builders that render as a group of related tags.
 */
abstract class GroupedTagBuilder extends TagBuilder
{
    /**
     * Grouped tag builders are represented as an empty array when absent
     * from the Head::toArray() result.
     *
     * @return array<int, mixed>
     */
    public static function headArrayDefault(): mixed
    {
        return [];
    }
}
