<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\AcademicSection;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if ($state !== User::ROLE_STUDENT) {
                            $set('academic_section_id', null);
                        }
                    })
                    ->native(false),

                Select::make('academic_section_id')
                    ->label(__('ui.fields.academic_section'))
                    ->options(fn (): array => AcademicSection::query()
                        ->where('is_active', true)
                        ->orderByDesc('school_year')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (AcademicSection $section): array => [
                            $section->id => "{$section->name} - {$section->school_year}",
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('role') === User::ROLE_STUDENT)
                    ->visible(fn (Get $get): bool => $get('role') === User::ROLE_STUDENT)
                    ->helperText(__('Students can belong to only one academic section.'))
                    ->rules(['nullable', 'integer', 'exists:academic_sections,id'])
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
