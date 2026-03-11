<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Models\Assignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teachingAssignment.id')
                    ->label(__('ui.fields.teaching_assignment'))
                    ->getStateUsing(
                        fn (Assignment $record): string => __(
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
                TextColumn::make('title')
                    ->label(__('ui.fields.title'))
                    ->searchable(),
                TextColumn::make('due_date')
                    ->label(__('ui.fields.due_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(__('ui.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('ui.fields.is_active'))
                    ->boolean(),
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
