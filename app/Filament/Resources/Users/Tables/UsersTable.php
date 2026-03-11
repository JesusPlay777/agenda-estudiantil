<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('ui.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('ui.fields.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label(__('ui.fields.role'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => __('ui.roles.'.($state ?? '')))
                    ->color(fn (?string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_TEACHER => 'warning',
                        User::ROLE_STUDENT => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                IconColumn::make('email_verified_at')
                    ->label(__('ui.fields.email_verified'))
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => filled($record->email_verified_at))
                    ->alignCenter(),

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
                //
            ]);
    }
}
