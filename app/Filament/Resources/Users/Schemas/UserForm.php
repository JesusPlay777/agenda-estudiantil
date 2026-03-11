<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $roles = [
            User::ROLE_ADMIN => __('ui.roles.admin'),
            User::ROLE_TEACHER => __('ui.roles.teacher'),
            User::ROLE_STUDENT => __('ui.roles.student'),
        ];

        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('ui.fields.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(__('ui.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('role')
                    ->label(__('ui.fields.role'))
                    ->options($roles)
                    ->default(User::ROLE_STUDENT)
                    ->in(array_keys($roles))
                    ->required()
                    ->native(false),

                TextInput::make('password')
                    ->label(__('ui.fields.password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->confirmed()
                    ->maxLength(255),

                TextInput::make('password_confirmation')
                    ->label(__('ui.fields.password_confirmation'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->maxLength(255),
            ]);
    }
}
