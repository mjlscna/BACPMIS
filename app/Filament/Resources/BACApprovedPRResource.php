<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BACApprovedPRResource\Pages;
use App\Filament\Resources\BACApprovedPRResource\RelationManagers;
use App\Models\BACApprovedPR;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BACApprovedPRResource extends Resource
{
    protected static ?string $model = BACApprovedPR::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListBACApprovedPRS::route('/'),
            'create' => Pages\CreateBACApprovedPR::route('/create'),
            'edit' => Pages\EditBACApprovedPR::route('/{record}/edit'),
        ];
    }
}
