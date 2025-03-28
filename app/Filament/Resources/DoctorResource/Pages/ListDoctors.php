<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Gate;
use App\Models\User;
class ListDoctors extends ListRecords
{   protected static string $resource = DoctorResource::class;
    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        $user = Auth::user();

        if (Gate::allows('view_any_doctor')) {
            return $query; 
        }

        return $query->where('user_id', $user->id); 
    }
}