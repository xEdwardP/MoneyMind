<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource\RelationManagers;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Card::make('Datos Generales de el presupuesto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->required()
                                    ->label('Usuario')
                                    ->placeholder('Selecciona un usuario')
                                    ->options(User::all()->pluck('name', 'id')),
                                Forms\Components\Select::make('category_id')
                                    ->required()
                                    ->label('Categoría')
                                    ->placeholder('Selecciona una categoría')
                                    ->options(Category::all()->pluck('name', 'id')),
                                Forms\Components\TextInput::make('assignedAmount')
                                    ->required()
                                    ->label('Monto Asignado')
                                    ->placeholder('Monto Asignado')
                                    ->numeric()
                                    ->default(0.00),
                                Forms\Components\TextInput::make('spentAmount')
                                    ->required()
                                    ->label('Monto Gastado')
                                    ->placeholder('Monto Gastado')
                                    ->numeric()
                                    ->default(0.00)
                                    ->disabled(),
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->label('Fecha de Inicio')
                                    ->default(now()),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->label('Fecha de Fin')
                                    ->default(now()->addMonth()),
                            ])
                            ->columns(2),
                    ])
                    ->columns(1)
                    ->columnSpan(2),
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spentAmount')
                    ->label('Monto Gastado')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha de Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fecha de Fin')
                    ->date()
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
