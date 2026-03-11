<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Models\Schedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teachingAssignment.id')
                    ->label(__('ui.fields.teaching_assignment'))
                    ->getStateUsing(
                        fn (Schedule $record): string => __(
                            'ui.teaching_assignment_option.format',
                            [
                                'section' => $record->teachingAssignment?->academicSection?->name ?? __('ui.teaching_assignment_option.none_section'),
                                'subject' => $record->teachingAssignment?->subject?->name ?? __('ui.teaching_assignment_option.none_subject'),
                                'teacher' => $record->teachingAssignment?->teacher?->name ?? __('ui.teaching_assignment_option.none_teacher'),
                            ],
                        ),
                    )
                    ->wrap()
                    ->searchable(),
                TextColumn::make('weekday')
                    ->label(__('ui.fields.weekday'))
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return '-';
                        }

                        $translated = __('ui.weekdays.'.$state);

                        return $translated === 'ui.weekdays.'.$state ? $state : $translated;
                    })
                    ->searchable(),
                TextColumn::make('start_time')
                    ->label(__('ui.fields.start_time'))
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label(__('ui.fields.end_time'))
                    ->time()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('ui.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('ui.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
