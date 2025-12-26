<?php
namespace Modules\AppChannels\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelEvent
{
    use Dispatchable, SerializesModels;

    public string $type;
    public array $data;

    public function __construct(string $type, array $data = [])
    {
        $this->type = $type;
        $this->data = $data;
    }
}