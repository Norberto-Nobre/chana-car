<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Veículos';
    protected static ?string $modelLabel = 'Veículo';
    protected static ?string $pluralModelLabel = 'Veículos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->options(Category::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('year')
                            ->label('Ano')
                            ->required()
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 1),
                        
                        Forms\Components\TextInput::make('plate')
                            ->label('Placa')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\TextInput::make('km')
                            ->label('Quilometragem')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Especificações')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'SUV' => 'SUV',
                                'Sedan' => 'Sedan',
                                'Pick-Up' => 'Pick-Up',
                                'Hatchback' => 'Hatchback',
                                'Convertible' => 'Conversível',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('fuel')
                            ->label('Combustível')
                            ->options([
                                'gasoline' => 'Gasolina',
                                'diesel' => 'Diesel',
                                'electric' => 'Elétrico',
                                'hybrid' => 'Híbrido',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('color')
                            ->label('Cor')
                            ->required()
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('doors')
                            ->label('Portas')
                            ->required()
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(8),
                        
                        Forms\Components\TextInput::make('price_per_day')
                            ->label('Preço por Dia (AOA)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('AOA'),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Disponível',
                                'in_use' => 'Em Uso',
                                'maintenance' => 'Manutenção',
                            ])
                            ->default('available')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Imagens')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Imagens do Veículo')
                            ->image()
                            ->multiple()
                            ->directory('vehicles')
                            ->maxFiles(10),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Veículo')
                    ->searchable(['brand', 'model'])
                    ->sortable(['brand', 'model']),
                
                Tables\Columns\TextColumn::make('plate')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'in_use',
                        'danger' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponível',
                        'in_use' => 'Em Uso',
                        'maintenance' => 'Manutenção',
                    }),
                
                Tables\Columns\TextColumn::make('price_per_day')
                    ->label('Preço/Dia')
                    ->money('AOA')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Reservas')
                    ->counts('bookings')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->options(Category::active()->pluck('name', 'id')),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Disponível',
                        'in_use' => 'Em Uso',
                        'maintenance' => 'Manutenção',
                    ]),
                
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'SUV' => 'SUV',
                        'Sedan' => 'Sedan',
                        'Pick-Up' => 'Pick-Up',
                        'Hatchback' => 'Hatchback',
                        'Convertible' => 'Conversível',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('brand');
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}