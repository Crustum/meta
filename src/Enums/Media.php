<?php
declare(strict_types=1);

namespace Crustum\Meta\Enums;

/**
 * CSS media query values for common device contexts.
 */
enum Media: string
{
    case Light = '(prefers-color-scheme: light)';
    case Dark = '(prefers-color-scheme: dark)';
    case Portrait = '(orientation: portrait)';
    case Landscape = '(orientation: landscape)';
}
