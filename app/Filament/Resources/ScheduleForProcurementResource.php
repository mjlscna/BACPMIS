<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleForProcurementResource\Pages;
use App\Filament\Resources\ScheduleForProcurementResource\RelationManagers;
use App\Models\ScheduleForProcurement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleForProcurementResource extends Resource
{
    protected static ?string $model = ScheduleForProcurement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ib_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('opening_of_bids')
                    ->required(),
                Forms\Components\Textarea::make('project_name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_framework')
                    ->required(),
                Forms\Components\TextInput::make('status_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('action_taken')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('next_bidding_schedule'),
                Forms\Components\Textarea::make('google_drive_link')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ABC')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('no_items_lot')
                    ->numeric(),
                Forms\Components\TextInput::make('pr_count')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ib_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('opening_of_bids')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_framework')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_bidding_schedule')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ABC')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_items_lot')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pr_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListScheduleForProcurements::route('/'),
            'create' => Pages\CreateScheduleForProcurement::route('/create'),
            'edit' => Pages\EditScheduleForProcurement::route('/{record}/edit'),
        ];
    }
}
