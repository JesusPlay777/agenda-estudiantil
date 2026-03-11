<?php

namespace App\Filament\Resources\AcademicSections\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('ui.fields.name'))
                    ->required(),
                TextInput::make('school_year')
                    ->label(__('ui.fields.school_year'))
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('ui.fields.is_active'))
                    ->required(),
            ]);
    }
}
