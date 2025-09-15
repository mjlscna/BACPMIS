<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(7) // 🔹 3-column grid
                    ->schema([
                        // 🔹 HRIS ID (auto-filled, disabled)
                        TextInput::make('hris_id')
                            ->label('HRIS ID')
                            ->disabled()
                            ->dehydrated() // ✅ still saves to DB even if disabled
                            ->required()   // ✅ required in Filament form
                            ->unique(ignoreRecord: true) // ✅ enforces uniqueness in Filament form
                            ->columnSpan(1),

                        // 🔹 Employee Select from API
                        Select::make('name')
                            ->label('Employee Name')
                            ->options(function () {
                                try {
                                    $response = \Illuminate\Support\Facades\Http::get('http://192.168.100.162:8081/public/get-employees');

                                    if ($response->successful()) {
                                        $employeesList = $response->json()['employeesList'] ?? [];

                                        $employees = collect($employeesList)->map(function ($employee) {
                                            return [
                                                'id' => $employee['id'],
                                                'fullName' => $employee['firstName'] . ' ' . $employee['lastName'],
                                            ];
                                        });

                                        return $employees->pluck('fullName', 'id');
                                    }

                                    return [];
                                } catch (\Throwable $e) {
                                    report($e);
                                    return [];
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                try {
                                    $response = \Illuminate\Support\Facades\Http::get('http://192.168.100.162:8081/public/get-employees');
                                    if ($response->successful()) {
                                        $employeesList = $response->json()['employeesList'] ?? [];
                                        $employee = collect($employeesList)->firstWhere('id', $state);

                                        if ($employee) {
                                            $set('hris_id', $employee['id']);
                                            $set('name', $employee['firstName'] . ' ' . $employee['lastName']);
                                        }
                                    }
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            })
                            ->columnSpan(3),

                        // 🔹 Roles (full width below)
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Roles')
                            ->columnSpan(2),
                    ]),


            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('hris_id')
                    ->label('HRIS ID'),

                TextColumn::make('name')
                    ->searchable(),

                BadgeColumn::make('roles.name')
                    ->label('Roles')
                    ->colors([
                        'primary',
                        'success',
                        'warning',
                        'danger',
                    ])
                    ->separator(', '),


                // ✅ Roles column
                // show multiple roles separated by comma
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
