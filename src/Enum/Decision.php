<?php

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Enum;

class Decision
{
    public const LIGHT_GREEN = 'LIGHT_GREEN';
    public const GREEN = 'GREEN';
    public const YELLOW_GREEN = 'YELLOW_GREEN';
    public const YELLOW = 'YELLOW';
    public const ORANGE = 'ORANGE';
    public const RED = 'RED';
    public const DARK_RED = 'DARK_RED';
    public const ERROR = 'ERROR';
}
