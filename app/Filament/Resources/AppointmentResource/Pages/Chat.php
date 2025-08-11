<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\Page;
use App\Models\Appointment;
use App\Models\Message;
use Livewire\WithPagination;

class Chat extends Page
{
    use WithPagination;

    protected static string $resource = AppointmentResource::class;

    protected static string $view = 'filament.chat.messages';

    public Appointment $appointment;

    public function mount($record): void
    {
        $this->appointment = Appointment::with(['user', 'doctor'])->findOrFail($record);
    }

    public function getMessagesProperty()
    {
        return Message::where('appointment_id', $this->appointment->id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->paginate(20);
    }
}
