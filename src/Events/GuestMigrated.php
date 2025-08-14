<?php

namespace MattYeend\GuestToUserHelper\Events;

class GuestMigrated
{
    public function __construct(
        public int|string $userId
    ) {}
}
