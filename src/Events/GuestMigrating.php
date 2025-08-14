<?php

namespace MattYeend\GuestToUserHelper\Events;

class GuestMigrating
{
    public function __construct(
        public int|string $userId,
        public string $guestToken
    ) {}
}
