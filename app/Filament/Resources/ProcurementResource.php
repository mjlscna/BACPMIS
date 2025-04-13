<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcurementResource\Pages;
use App\Models\ModeOfProcurement;
use App\Models\Procurement;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Livewire;

class ProcurementResource extends Resource
{
    protected static ?string $model = Procurement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Procurement')
                        ->schema([
                            Section::make('Procurement Information')
                                ->schema([
                                    TextInput::make('pr_number')
                                        ->label('PR No.')
                                        ->required()
                                        ->maxLength(12)
                                        ->validationMessages([
                                            'required' => 'The PR No. field is required.'
                                        ])
                                        ->extraInputAttributes(['class' => 'text-right']),
                                    Textarea::make('procurement_program_project')
                                        ->label('Procurement Program / Project')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    DatePicker::make('date_receipt_advance')
                                        ->label('Date Receipt')
                                        ->helperText('Advance Copy'),
                                    DatePicker::make('date_receipt_signed')
                                        ->label('Date Receipt')
                                        ->helperText('Signed Copy'),
                                    Select::make('rbac_sbac')
                                        ->label('RBAC / SBAC')
                                        ->options(['RBAC' => 'RBAC', 'SBAC' => 'SBAC'])
                                        ->default('RBAC')
                                        ->required(),
                                    TextInput::make('dtrack_no')
                                        ->label('DTRACK #')
                                        ->maxLength(12),
                                    TextInput::make('unicode')
                                        ->label('UniCode')
                                        ->maxLength(30)
                                        ->extraInputAttributes(['class' => 'text-right'])
                                        ->columnSpan(2),
                                    Select::make('divisions_id')
                                        ->label('Division')
                                        ->relationship('division', 'divisions')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(2),
                                    Select::make('cluster_committees_id')
                                        ->label('Cluster / Committee')
                                        ->relationship('clusterCommittee', 'clustercommittee')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(2),
                                    Select::make('category_id')
                                        ->label('Category')
                                        ->relationship('category', 'category')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(2),
                                ])->columns(7),

                            Section::make('Venue Details')
                                ->schema([
                                    Select::make('venue_specific_id')
                                        ->label('Venue (Specific)')
                                        ->relationship('venueSpecific', 'venue')
                                        ->searchable()
                                        ->preload(),
                                    Select::make('venue_province_huc_id')
                                        ->label('Venue (Province/HUC)')
                                        ->relationship('venueProvince', 'province')
                                        ->searchable()
                                        ->preload(),
                                    Select::make('category_venue_id')
                                        ->label('Category / Venue')
                                        ->relationship('categoryVenue', 'category_venue')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2),
                                    Textarea::make('approved_ppmp')
                                        ->label('w/Approved PPMP')
                                        ->columnSpanFull(),
                                    Textarea::make('app_updated')
                                        ->label('APP (Updated)')
                                        ->columnSpanFull(),
                                ])->columns(4),

                            Section::make('Procurement Schedule')
                                ->schema([
                                    Textarea::make('immediate_date_needed')
                                        ->label('Immediate Date Needed')
                                        ->columnSpan(2),
                                    Textarea::make('date_needed')
                                        ->label('Date Needed')
                                        ->columnSpan(2),
                                    Select::make('end_users_id')
                                        ->label('PMO/End-User')
                                        ->relationship('endUser', 'endusers')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(1),
                                    Toggle::make('early_procurement')
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->helperText('No / Yes'),
                                ])->columns(4),

                            Section::make('Funding Details')
                                ->schema([
                                    Select::make('fund_source_id')
                                        ->label('Source of Funds')
                                        ->relationship('fundSource', 'fundsources')
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('expense_class')
                                        ->label('Expense Class')
                                        ->maxLength(255),
                                    TextInput::make('abc')
                                        ->label('ABC Amount')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->minValue(0)
                                        ->default(0)
                                        ->required()
                                        ->extraInputAttributes(['class' => 'text-right'])
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $numericValue = (float) str_replace(',', '', $state);
                                            $set('abc_50k', $numericValue > 50000 ? 'above_50k' : '50k_or_less');
                                        }),
                                    Select::make('abc_50k')
                                        ->label('ABC <=> 50k')
                                        ->options([
                                            '50k_or_less' => '50k or less',
                                            'above_50k' => 'above 50k',
                                        ])
                                        ->disabled(),
                                ])->columns(5),
                        ])
                        ->afterValidation(fn($livewire) => $livewire->saveStep()),

                    Step::make('Additional Information')
                        ->schema([
                            Split::make([
                                Section::make()
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                Select::make('mode_of_procurement_id')
                                                    ->label('Mode of Procurement')
                                                    ->relationship('modeOfProcurement', 'modeofprocurements')
                                                    ->searchable()
                                                    ->preload()
                                                    ->default(1)
                                                    ->reactive()
                                                    ->afterStateHydrated(fn($state, callable $set) => $set('mode_of_procurement_id', $state ?? 1))
                                                    ->columnSpan(1) // Keep it small
                                                    ->extraAttributes(['style' => 'width: 300px;']), // Force a smaller dropdown width
                                            ])
                                    ])
                                    ->columnSpan(1),

                                Section::make([
                                    TextInput::make('ib_number')
                                        ->label('IB No.')
                                        ->maxLength(12)->
                                        extraInputAttributes(['class' => 'text-right']),
                                    DatePicker::make('pre_proc_conference')
                                        ->label('Pre-Proc Conference'),
                                    DatePicker::make('ads_post_ib')
                                        ->label('Ads/Post IB'),
                                    DatePicker::make('pre_bid_conf')
                                        ->label('Pre-Bid Conference'),
                                    DatePicker::make('eligibility_check')
                                        ->label('Eligibility Check'),
                                    DatePicker::make('sub_open_bids')
                                        ->label('Sub/Open of Bids'),
                                ])
                                    ->columns(3)
                                    ->columnSpan(3),
                            ]),
                            Repeater::make('Bids')
                                ->schema([
                                    TextInput::make('name')->required(),
                                    Select::make('role')
                                        ->options([
                                            'member' => 'Member',
                                            'administrator' => 'Administrator',
                                            'owner' => 'Owner',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2)
                                ->reactive() // Ensures UI updates when `mode_of_procurement_id` changes
                                ->disabled(fn($get) => $get('mode_of_procurement_id') == 1),
                        ]),
                ])
                    ->startOnStep(request()->route('record') ? 2 : 1),
            ])
            ->columns(1);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pr_number')
                    ->label('PR Number')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('procurement_program_project')
                    ->label('Procurement Program / Project')
                    ->wrap(),
                TextColumn::make('date_receipt_advance')
                    ->label(' Date Receipt (Advance Copy)')
                    ->date()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('date_receipt_signed')
                    ->label(' Date Receipt (Signed Copy)')
                    ->date()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('rbac_sbac')
                    ->label('RBAC / SBAC')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => [
                        'RBAC' => 'RBAC',
                        'SBAC' => 'SBAC',
                    ][$state] ?? '')
                    ->alignCenter(),
                TextColumn::make('dtrack_no')
                    ->label('DTRACK #')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('unicode')
                    ->label('UniCode')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('division.divisions')
                    ->label('Division')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('clusterCommittee.clustercommittee')
                    ->label('Cluster / Committee')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('category.category')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('venueSpecific.venue')
                    ->label('Venue(Specific)')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('venueProvince.province')
                    ->label('Venue(Province/HUC)')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('approved_ppmp')
                    ->label('w/Approved PPMP')
                    ->wrap()
                    ->alignCenter(),
                TextColumn::make('app_updated')
                    ->label('APP (Updated)')
                    ->wrap()
                    ->alignCenter(),
                TextColumn::make('immediate_date_needed')
                    ->label('Immediate Date Needed')
                    ->wrap()
                    ->alignCenter(),
                TextColumn::make('date_needed')
                    ->label('Date Needed')
                    ->wrap()
                    ->alignCenter(),
                TextColumn::make('endUser.endusers')
                    ->label('PMO/End-User')
                    ->alignCenter(),
                TextColumn::make('fundSource.fundsources')
                    ->label('Source of Funds')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                IconColumn::make('early_procurement')
                    ->label('Early Procurement')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('fundSource.fundsources')
                    ->label('Source of Funds')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('expense_class')
                    ->label('Expense Class')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('abc')
                    ->label('ABC')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('abc_50k')
                    ->label('ABC <=> 50k')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => [
                        '50k_or_less' => '50k or less',
                        'above_50k' => 'above 50k',
                    ][$state] ?? '')
                    ->alignCenter(),
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
                BulkAction::make('bulkEdit')
                    ->label('Bulk Edit')
                    ->action(fn($records) => redirect()->route('filament.admin.resources.procurements.bulk-edit', [
                        'recordIds' => implode(',', $records->pluck('id')->toArray()),
                    ]))

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
            'index' => Pages\ListProcurements::route('/'),
            'create' => Pages\CreateProcurement::route('/create'),
            'edit' => Pages\EditProcurement::route('/{record}/edit'),
            'bulk-edit' => Pages\BulkEditProcurements::route('/bulk-edit'),
        ];
    }

}
