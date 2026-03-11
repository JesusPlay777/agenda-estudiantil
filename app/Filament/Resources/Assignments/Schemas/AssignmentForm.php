<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\TeachingAssignment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teaching_assignment_id')
                    ->label(__('ui.fields.teaching_assignment'))
                    ->relationship(
                        name: 'teachingAssignment',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->with(['academicSection:id,name', 'subject:id,name', 'teacher:id,name'])
                            ->where('is_active', true),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (TeachingAssignment $record): string => __(
                            'ui.teaching_assignment_option.format',
                            [
                                'section' => $record->academicSection?->name ?? __('ui.teaching_assignment_option.none_section'),
                                'subject' => $record->subject?->name ?? __('ui.teaching_assignment_option.none_subject'),
                                'teacher' => $record->teacher?->name ?? __('ui.teaching_assignment_option.none_teacher'),
                            ],
                        ),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('title')
                    ->label(__('ui.fields.title'))
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label(__('ui.fields.description'))
                    ->required()
                    ->columnSpanFull(),

                DatePicker::make('due_date')
                    ->label(__('ui.fields.due_date')),

                DateTimePicker::make('published_at')
                    ->label(__('ui.fields.published_at')),

                Toggle::make('is_active')
                    ->label(__('ui.fields.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
