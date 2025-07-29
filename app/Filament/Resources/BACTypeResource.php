<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BACTypeResource\Pages;
use App\Filament\Resources\BACTypeResource\RelationManagers;
use App\Models\BACType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BACTypeResource extends Resource
{
    protected static ?string $model = BACType::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'BAC Types';
    protected static ?string $pluralLabel = 'BAC Types';
    protected static ?string $modelLabel = 'BAC Type';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('abbreviation')
                    ->required()
                    ->unique()
                    ->maxLength(10),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->hint('Optional, auto-generate if blank'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('abbreviation')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBACTypes::route('/'),
            'create' => Pages\CreateBACType::route('/create'),
            'edit' => Pages\EditBACType::route('/{record}/edit'),
        ];
    }
}
