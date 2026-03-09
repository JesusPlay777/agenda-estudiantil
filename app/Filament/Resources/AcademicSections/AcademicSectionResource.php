<?php

namespace App\Filament\Resources\AcademicSections;

use App\Filament\Resources\AcademicSections\Pages\CreateAcademicSection;
use App\Filament\Resources\AcademicSections\Pages\EditAcademicSection;
use App\Filament\Resources\AcademicSections\Pages\ListAcademicSections;
use App\Filament\Resources\AcademicSections\Schemas\AcademicSectionForm;
use App\Filament\Resources\AcademicSections\Tables\AcademicSectionsTable;
use App\Models\AcademicSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AcademicSectionResource extends Resource
{
    protected static ?string $model = AcademicSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AcademicSectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicSectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicSections::route('/'),
            'create' => CreateAcademicSection::route('/create'),
            'edit' => EditAcademicSection::route('/{record}/edit'),
        ];
    }
}
