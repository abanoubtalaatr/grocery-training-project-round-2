<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('order_id')
                ->relationship('order', 'id')
                ->required()
                ->label('Order'),
            Forms\Components\Select::make('meal_id')
                ->relationship('meal', 'title')
                ->required()
                ->label('Meal'),
            Forms\Components\TextInput::make('quantity')
                ->label('Quantity')
                ->required()
                ->numeric()
                ->integer()
                ->minValue(1)
                ->default(1)
                ->rules(['required', 'integer', 'min:1'])
                ->validationMessages(['min' => 'Value must be greater than zero.']),
            Forms\Components\TextInput::make('unit_price')
                ->label('Unit price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->rules(['required', 'numeric', 'min:0'])
                ->validationMessages(['min' => 'Value must be greater than or equal to zero.']),
            Forms\Components\TextInput::make('discount_amount')
                ->label('Discount amount')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->default(0)
                ->rules(['required', 'numeric', 'min:0'])
                ->validationMessages(['min' => 'Value must be greater than or equal to zero.']),
            Forms\Components\TextInput::make('subtotal')
                ->label('Subtotal')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->rules(['required', 'numeric', 'min:0'])
                ->validationMessages(['min' => 'Value must be greater than or equal to zero.']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => OrderResource::getUrl('edit', ['record' => $record->order_id])),
                Tables\Columns\TextColumn::make('meal.title')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order_id')
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
