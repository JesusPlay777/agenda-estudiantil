<?php

namespace App\Filament\Resources\TeachingAssignments\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TeachingAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_section_id')
                    ->label(__('ui.fields.academic_section'))
                    ->relationship(
                        name: 'academicSection',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('is_active', true)
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('subject_id')
                    ->label(__('ui.fields.subject'))
                    ->relationship(
                        name: 'subject',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('is_active', true)
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('teacher_id')
                    ->label(__('ui.fields.teacher'))
                    ->relationship(
                        name: 'teacher',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('role', User::ROLE_TEACHER)
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('is_active')
                    ->label(__('ui.fields.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
