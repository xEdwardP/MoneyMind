<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Numeric;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos';

    protected static ?string $navigationLabel = 'Movimientos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Datos Generales de la Categoría')
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
                                Forms\Components\Select::make('type')
                                    ->label('Tipo de movimiento')
                                    ->placeholder('Selecciona el tipo de movimiento')
                                    ->required()
                                    ->options([
                                        'ingreso' => 'Ingreso',
                                        'gasto' => 'Gasto',
                                    ]),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Monto')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Descripción')
                                    ->placeholder('Descripción del movimiento')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('photo')
                                    ->label('Foto')
                                    ->placeholder('Selecciona una foto')
                                    ->image()
                                    ->disk('public')
                                    ->directory('transactions'),
                                Forms\Components\DatePicker::make('transaction_date')
                                    ->label('Fecha del movimiento')
                                    ->placeholder('Selecciona la fecha del movimiento')
                                    ->default(now())
                                    ->minDate(now()->subYear(1))
                                    ->maxDate(now()->addYear(1))
                                    ->required(),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo de movimiento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->html()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->searchable()
                    // ->extraImgAttributes(['loading' => 'lazy'])
                    ->height(100)
                    ->width(100),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Fecha del movimiento')
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
                SelectFilter::make('type')
                    ->label('Tipo de movimiento')
                    ->placeholder('Selecciona el tipo de movimiento')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'gasto' => 'Gasto',
                    ]),
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
