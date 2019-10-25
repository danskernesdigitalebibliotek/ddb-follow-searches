<?php

namespace App\Events;

use Adgangsplatformen\Provider\AdgangsplatformenUser;

abstract class BaseEvent
{

    /** @var \Adgangsplatformen\Provider\AdgangsplatformenUser */
    protected $user;

    /** @var string */
    protected $list;

    /** @var string|null */
    protected $search;

    public function __construct(
        AdgangsplatformenUser $user,
        string $list,
        ?string $search = null
    ) {
        $this->user = $user;
        $this->list = $list;
        $this->search = $search;
    }

    public function getUser(): AdgangsplatformenUser
    {
        return $this->user;
    }

    public function getList(): string
    {
        return $this->list;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }
}
