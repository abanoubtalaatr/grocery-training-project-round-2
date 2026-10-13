<?php

namespace App\Filament\Resources\SmartListResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MealsRelationManager extends RelationManager
{
    protected static string $relationship = 'meals';

    protected static ?string $title = 'List items (meals)';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->image_url ?? $record->image),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->headerActions([
                Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereNotIn('id', $this->getOwnerRecord()->meals->pluck('id'))),
            ])
            ->actions([
                Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DetachBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No items in this list')
            ->emptyStateDescription('Add meals to this list using the button above.');
    }
}
