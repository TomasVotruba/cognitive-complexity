<?php

enum Status: int
{
    case Active = 1;
    case Inactive = 0;

    public function label(): string
    {
        return match ($this) { // +1
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
