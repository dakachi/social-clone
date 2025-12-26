<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SubHeader extends Component
{
    public $title;
    public $description;
    public $count;

    public function __construct($title, $description, $count = -1)
    {
        $this->title = $title;
        $this->description = $description;
        $this->count = $count;
    }

    public function render()
    {
        return view('components.sub_header');
    }
}