<?php

namespace App\Livewire;

use Livewire\Component;

class TourRegister extends Component
{
    public string $name;
    public array $steps = [];
    public array $validators = [];

    public function mount(string $name, array $steps = [], array $validators = [])
    {
        $this->name = $name;
        $this->steps = $steps;
        $this->validators = $validators;
    }

    public function render()
    {
        return view('livewire.tour-register');
    }
}
