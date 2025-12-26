<?php
namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class AuthEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $type, public $data) {}
}