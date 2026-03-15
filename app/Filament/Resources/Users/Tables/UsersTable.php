<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\AcademicSection;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                TextColumn::make('studentProfile.academicSection.name')
                    ->label(__('ui.fields.academic_section'))
                    ->getStateUsing(fn (User $record): string => $record->studentProfile?->academicSection
                        ? $record->studentProfile->academicSection->name.' - '.$record->studentProfile->academicSection->school_year
                        : '—')
                    ->toggleable(),

                TextColumn::make('identity_card_display')
                    ->label(__('ui.fields.identity_card'))
                    ->getStateUsing(fn (User $record): string => match ($record->role) {
                        User::ROLE_STUDENT => $record->studentProfile?->identity_card ?? '—',
                        User::ROLE_TEACHER => $record->teacherProfile?->identity_card ?? '—',
                        default => '—',
                    })
                    ->visibleFrom('md')
                    ->toggleable(),

                TextColumn::make('phone_display')
                    ->label(__('ui.fields.phone'))
                    ->getStateUsing(fn (User $record): string => match ($record->role) {
                        User::ROLE_STUDENT => $record->studentProfile?->phone ?? '—',
                        User::ROLE_TEACHER => $record->teacherProfile?->phone ?? '—',
                        default => '—',
                    })
                    ->visibleFrom('md')
                    ->toggleable(),

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
                SelectFilter::make('role')
                    ->label(__('ui.fields.role'))
                    ->options([
                        User::ROLE_ADMIN => __('ui.roles.admin'),
                        User::ROLE_TEACHER => __('ui.roles.teacher'),
                        User::ROLE_STUDENT => __('ui.roles.student'),
                    ])
                    ->native(false),

                SelectFilter::make('academic_section_id')
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
                    ->query(function (Builder $query, array $data): Builder {
                        $sectionId = $data['value'] ?? null;

                        return $query->when(
                            filled($sectionId),
                            fn (Builder $query) => $query->whereHas(
                                'studentProfile',
                                fn (Builder $studentProfileQuery) => $studentProfileQuery->where('academic_section_id', $sectionId),
                            ),
                        );
                    })
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
