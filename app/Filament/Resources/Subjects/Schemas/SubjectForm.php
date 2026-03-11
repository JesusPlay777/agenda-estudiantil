<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('ui.fields.name'))
                    ->required(),
                TextInput::make('code')
                    ->label(__('ui.fields.code')),
                Toggle::make('is_active')
                    ->label(__('ui.fields.is_active'))
                    ->required(),
            ]);
    }
}
