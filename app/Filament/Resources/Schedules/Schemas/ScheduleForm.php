<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\TeachingAssignment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ScheduleForm
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

                Select::make('weekday')
                    ->label(__('ui.fields.weekday'))
                    ->options([
                        'lunes' => __('ui.weekdays.lunes'),
                        'martes' => __('ui.weekdays.martes'),
                        'miercoles' => __('ui.weekdays.miercoles'),
                        'jueves' => __('ui.weekdays.jueves'),
                        'viernes' => __('ui.weekdays.viernes'),
                    ])
                    ->required(),

                TimePicker::make('start_time')
                    ->label(__('ui.fields.start_time'))
                    ->seconds(false)
                    ->required(),

                TimePicker::make('end_time')
                    ->label(__('ui.fields.end_time'))
                    ->seconds(false)
                    ->required(),
            ]);
    }
}
