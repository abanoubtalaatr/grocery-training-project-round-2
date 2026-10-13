<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OtpResource\Pages;
use App\Models\Otp;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OtpResource extends Resource
{
    protected static ?string $model = Otp::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('identifier')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('otp')
                ->required()
                ->maxLength(10),
            Forms\Components\TextInput::make('type')
                ->required()
                ->maxLength(255),
            Forms\Components\Toggle::make('is_used')
                ->required(),
            Forms\Components\DateTimePicker::make('expires_at')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('otp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_used')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
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
            'index' => Pages\ListOtps::route('/'),
            'create' => Pages\CreateOtp::route('/create'),
            'edit' => Pages\EditOtp::route('/{record}/edit'),
        ];
    }
}
