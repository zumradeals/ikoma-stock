<?php

namespace App\Livewire\Components;

use Livewire\Attributes\On;
use Livewire\Component;

class ConfirmationModal extends Component
{
    public bool $show = false;

    public string $title = '';

    public string $message = '';

    public ?string $detail = null;

    public bool $danger = false;

    public ?string $eventName = null;

    public array $eventParams = [];

    #[On('confirm-action')]
    public function open(string $title, string $message, ?string $detail = null, bool $danger = false, ?string $eventName = null, array $eventParams = []): void
    {
        $this->title = $title;
        $this->message = $message;
        $this->detail = $detail;
        $this->danger = $danger;
        $this->eventName = $eventName;
        $this->eventParams = $eventParams;
        $this->show = true;
    }

    public function confirm(): void
    {
        if ($this->eventName !== null) {
            $this->dispatch($this->eventName, ...$this->eventParams);
        }

        $this->reset(['show', 'title', 'message', 'detail', 'danger', 'eventName', 'eventParams']);
    }

    public function cancel(): void
    {
        $this->reset(['show', 'title', 'message', 'detail', 'danger', 'eventName', 'eventParams']);
    }

    public function render()
    {
        return view('livewire.components.confirmation-modal');
    }
}
