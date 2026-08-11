<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos';

    protected static ?string $navigationLabel = 'Movimientos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Generales de la transacción')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->required()
                            ->label('Usuario')
                            ->placeholder('Selecciona un usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de movimiento')
                            ->placeholder('Selecciona el tipo de movimiento')
                            ->required()
                            ->native(false)
                            ->options(CategoryResource::TYPES)
                            // Al cambiar el tipo se limpia la categoría, porque las
                            // categorías disponibles dependen del tipo elegido.
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('category_id', null)),
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->label('Categoría')
                            ->placeholder('Selecciona una categoría')
                            ->searchable()
                            ->options(fn (Forms\Get $get): array => Category::query()
                                ->when(
                                    $get('type'),
                                    fn (Builder $query, string $type) => $query->where('type', $type)
                                )
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()),
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('$'),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Fecha del movimiento')
                            ->placeholder('Selecciona la fecha del movimiento')
                            ->default(now())
                            ->minDate(now()->subYear())
                            ->maxDate(now()->addYear())
                            ->required(),
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('transactions'),
                        Forms\Components\RichEditor::make('description')
                            ->label('Descripción')
                            ->placeholder('Descripción del movimiento')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo de movimiento')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => CategoryResource::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'gasto' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$ ')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    // La descripción viene del editor enriquecido, así que se limpia
                    // el HTML antes de recortarla para no partir etiquetas a la mitad.
                    ->formatStateUsing(fn (?string $state): string => str(strip_tags((string) $state))->squish()->limit(50))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->height(100)
                    ->width(100),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Fecha del movimiento')
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
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de movimiento')
                    ->placeholder('Todos')
                    ->options(CategoryResource::TYPES),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->placeholder('Todas')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->placeholder('Todos')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->title('Movimiento eliminado')
                            ->body('El movimiento se ha eliminado exitosamente.')
                            ->success()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->title('Movimientos eliminados')
                                ->body('Los movimientos se han eliminado exitosamente.')
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
