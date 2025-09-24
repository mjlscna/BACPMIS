<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueSpecificResource\Pages;
use App\Models\VenueSpecific;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Str;

class VenueSpecificResource extends Resource
{
    protected static ?string $model = VenueSpecific::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    // change icon if you want
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $pluralLabel = 'Venue Specific';
    protected static ?string $navigationLabel = 'Venue Specific';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Venue Specific Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')
                                ->label('Venue Name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->label('Slug')
                                ->maxLength(255)
                                ->disabled()
                                ->required()
                                ->dehydrated()
                                ->unique(VenueSpecific::class, 'slug', ignoreRecord: true),

                            Toggle::make('is_active')
                                ->label('Active Status')
                                ->default(true)
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Venue')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListVenueSpecifics::route('/'),
            'create' => Pages\CreateVenueSpecific::route('/create'),
            'edit' => Pages\EditVenueSpecific::route('/{record}/edit'),
        ];
    }
}
