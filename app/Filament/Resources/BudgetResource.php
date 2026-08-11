<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $modelLabel = 'Presupuesto';

    protected static ?string $pluralModelLabel = 'Presupuestos';

    protected static ?string $navigationLabel = 'Presupuestos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Generales de el presupuesto')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->required()
                            ->label('Usuario')
                            ->placeholder('Selecciona un usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->label('Categoría')
                            ->placeholder('Selecciona una categoría')
                            // Un presupuesto limita gastos, así que sólo tiene sentido
                            // asignarlo a categorías de tipo "gasto".
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('type', 'gasto'),
                            )
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('assignedAmount')
                            ->required()
                            ->label('Monto Asignado')
                            ->placeholder('Monto Asignado')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0.00),
                        Forms\Components\TextInput::make('spentAmount')
                            ->label('Monto Gastado')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            // Se calcula a partir de los movimientos; nunca se envía.
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Se calcula automáticamente a partir de los movimientos del periodo.'),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->label('Fecha de Inicio')
                            ->default(now()->startOfMonth())
                            ->live()
                            ->beforeOrEqual('end_date'),
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->label('Fecha de Fin')
                            ->default(now()->endOfMonth())
                            ->afterOrEqual('start_date'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedAmount')
                    ->label('Monto Asignado')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$ ')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spentAmount')
                    ->label('Monto Gastado')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$ ')
                    ->alignEnd()
                    ->color(fn (Budget $record): string => $record->spentAmount > $record->assignedAmount ? 'danger' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('Consumido')
                    ->alignCenter()
                    ->badge()
                    ->state(fn (Budget $record): string => $record->usage_percentage.' %')
                    ->color(fn (Budget $record): string => match (true) {
                        $record->usage_percentage >= 100 => 'danger',
                        $record->usage_percentage >= 75 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha de Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fecha de Fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->placeholder('Todos')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->placeholder('Todas')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->title('Presupuesto eliminado')
                            ->body('El presupuesto se ha eliminado exitosamente.')
                            ->success()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->title('Presupuestos eliminados')
                                ->body('Los presupuestos se han eliminado exitosamente.')
                                ->success()
                        ),
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
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
