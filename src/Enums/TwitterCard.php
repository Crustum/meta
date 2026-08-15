<?php
declare(strict_types=1);

namespace Crustum\Meta\Enums;

/**
 * Twitter card type values.
 */
enum TwitterCard: string
{
    case Summary = 'summary';
    case SummaryWithLargeImage = 'summary_large_image';
    case App = 'app';
    case Player = 'player';
}
