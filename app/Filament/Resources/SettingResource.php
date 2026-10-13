<?php




namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $modelLabel = 'Store Settings';

    protected static ?string $navigationLabel = 'Store Settings';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Schemas\Components\Section::make('General')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('site_description')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('copyright_text')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('logo')
                            ->maxLength(255)
                            ->helperText('Path or URL to the logo image.'),
                        Forms\Components\TextInput::make('favicon')
                            ->maxLength(255)
                            ->helperText('Path or URL to the favicon.'),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('support_email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('support_phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('store_address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('Social Media')
                    ->schema([
                        Forms\Components\TextInput::make('facebook')
                            ->maxLength(255)
                            ->label('Facebook URL'),
                        Forms\Components\TextInput::make('instagram')
                            ->maxLength(255)
                            ->label('Instagram URL'),
                        Forms\Components\TextInput::make('twitter')
                            ->maxLength(255)
                            ->label('Twitter URL'),
                        Forms\Components\TextInput::make('linkedin')
                            ->maxLength(255)
                            ->label('LinkedIn URL'),
                        Forms\Components\TextInput::make('youtube')
                            ->maxLength(255)
                            ->label('YouTube URL'),
                        Forms\Components\TextInput::make('tiktok')
                            ->maxLength(255)
                            ->label('TikTok URL'),
                        Forms\Components\TextInput::make('whatsapp')
                            ->maxLength(255)
                            ->label('WhatsApp Number'),
                        Forms\Components\TextInput::make('snapchat')
                            ->maxLength(255)
                            ->label('Snapchat URL'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Schemas\Components\Section::make('Store Configuration')
                    ->schema([
                        Forms\Components\Select::make('store_status')
                            ->options([
                                Setting::STORE_STATUS_OPEN => 'Open',
                                Setting::STORE_STATUS_CLOSED => 'Closed',
                                Setting::STORE_STATUS_MAINTENANCE => 'Maintenance',
                            ])
                            ->default(Setting::STORE_STATUS_OPEN),
                        Forms\Components\Toggle::make('maintenance_mode')
                            ->label('Enable Maintenance Mode')
                            ->helperText('When enabled, the store is inaccessible to customers.'),
                        Forms\Components\TextInput::make('store_hours')
                            ->maxLength(255)
                            ->helperText('e.g., Mon-Sat: 9AM-10PM'),
                        Forms\Components\Select::make('currency_code')
                            ->options([
                                'EGP' => 'EGP - Egyptian Pound',
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'SAR' => 'SAR - Saudi Riyal',
                                'AED' => 'AED - UAE Dirham',
                            ])
                            ->default('EGP'),
                        Forms\Components\TextInput::make('currency_symbol')
                            ->maxLength(10)
                            ->default('£'),
                        Forms\Components\TextInput::make('locale')
                            ->maxLength(10)
                            ->helperText('Default locale code (e.g., en, ar).'),
                        Forms\Components\TextInput::make('timezone')
                            ->maxLength(50)
                            ->helperText('e.g., Africa/Cairo'),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('Shipping')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_fee')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Default shipping fee for delivery orders. Pickup is always 0.'),
                        Forms\Components\TextInput::make('free_shipping_min_order')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->helperText('Order subtotal above which shipping is free. Leave empty to never offer free shipping.'),
                        Forms\Components\Textarea::make('shipping_note')
                            ->helperText('Note displayed to customers about shipping.'),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('Payments & Tax')
                    ->schema([
                        Forms\Components\TextInput::make('tax_rate')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->default(0.1)
                            ->helperText('Tax rate as decimal (e.g., 0.10 for 10%).'),
                        Forms\Components\TagsInput::make('payment_methods')
                            ->helperText('Available payment methods (e.g., stripe_checkout, cash_on_delivery).'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('store_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        'maintenance' => 'warning',
                    }),
                Tables\Columns\IconColumn::make('maintenance_mode')
                    ->boolean()
                    ->label('Maintenance'),
                Tables\Columns\TextColumn::make('currency_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->label('Tax Rate')
                    ->suffix('%')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state * 100, 1)),
                Tables\Columns\TextColumn::make('shipping_fee')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
