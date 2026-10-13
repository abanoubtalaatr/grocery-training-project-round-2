<?php




namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(User::USERNAME_MAX_LENGTH)
                    ->rules([new UsernameMustContainLetter]),
                Forms\Components\FileUpload::make('profile_image')
                    ->image(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('firstname')
                    ->maxLength(255),
                Forms\Components\TextInput::make('lastname')
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'customer' => 'Customer',
                        'delivery_partner' => 'Driver',
                        'support_agent' => 'Support Agent',
                        'admin' => 'Admin',
                    ])
                    ->default('customer')
                    ->required(),
                Forms\Components\TextInput::make('country_code')
                    ->required()
                    ->maxLength(255)
                    ->default('+20'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(12)
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state)),
                Forms\Components\Toggle::make('agree_terms')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->circular(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\IconColumn::make('email_verified')
                    ->boolean()
                    ->label('Email Verified')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('phone_verified')
                    ->boolean()
                    ->label('Phone Verified')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'customer' => 'Customer',
                        'delivery_partner' => 'Driver',
                        'support_agent' => 'Support Agent',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\TernaryFilter::make('email_verified')
                    ->label('Email Verified'),
                Tables\Filters\TernaryFilter::make('phone_verified')
                    ->label('Phone Verified'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->hidden(fn (?User $record): bool => $record && $record->id === Auth::id()),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Check if the given record is the last active admin.
     */
    public static function isLastActiveAdmin(User $record): bool
    {
        if (! $record->isAdmin()) {
            return false;
        }

        $otherActiveAdminCount = User::where('is_active', true)
            ->where('id', '!=', $record->id)
            ->where('role', User::ROLE_ADMIN)
            ->count();

        return $otherActiveAdminCount === 0;
    }
}
