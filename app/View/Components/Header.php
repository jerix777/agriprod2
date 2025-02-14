<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Header extends Component
{
    public $title;

    public $searchAction;

    public $addAction;

    public $printAction;

    public $addButtonText;

    public $addButtonPrint;

    public $tabs;

    public function __construct($title, $searchAction, $addAction, $printAction, $addButtonText, $addButtonPrint, $tabs)
    {
        $this->title = $title;
        $this->searchAction = $searchAction;
        $this->addAction = $addAction;
        $this->printAction = $printAction;
        $this->addButtonText = $addButtonText;
        $this->addButtonPrint = $addButtonPrint;
        $this->tabs = $tabs;
    }

    public function render()
    {
        return view('components.header');
    }
}
