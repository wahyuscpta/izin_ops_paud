<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pengguna';

    public static function getNavigationGroup(): ?string
    {
        return Auth::user()?->hasRole('pemohon')
            ? 'Permohonan Saya'
            : 'Manajemen Data';
    }

    protected static ?string $breadcrumb = 'Pengguna';

    public static function getVerificationEmailNotification()
    {
        return CustomVerifyEmail::class;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                ->columns(2)
                    ->schema([
                        
                        TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255),
                        Select::make('roles')
                            ->label('Jabatan')
                            ->relationship('roles', 'name', function ($query) {
                                return $query->whereNotIn('name', ['super_admin', 'kepala_dinas']);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => ucwords(str_replace('_', ' ', $record->name)))
                            ->preload()
                            ->live()
                            ->required()
                            ->searchable(),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('no_telepon')
                            ->label('No Telepon')
                            ->numeric()
                            ->required()
                            ->rule('regex:/^08[0-9]{8,11}$/')
                            ->maxLength(13),
                        TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),                        
                        TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->maxLength(255)
                            ->same('password')
                            ->label('Confirm Password')
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                    ]),

                    Section::make('')
                        ->schema([
                            TextInput::make('admin_pin')
                                ->label('PIN Konfirmasi Admin')
                                ->password()
                                ->revealable()
                                ->required()
                                ->helperText('Masukkan PIN untuk verifikasi pembuatan akun admin')
                                ->prefixIcon('heroicon-m-shield-check')
                                ->extraInputAttributes(['class' => 'font-mono'])
                        ])
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('roles') === '2')
                        ->compact()
                    
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!Filament::auth()->user()?->hasRole('super_admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            });
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('no_telepon')
                    ->label('No Telepon')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Jabatan')
                    ->badge()                    
                    ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'success',
                        'admin' => 'primary',
                        'kepala_dinas' => 'primary',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('Tgl Verifikasi Email')
                    ->date(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Impersonate::make()
                    ->color('warning')
                    ->visible(fn () => Auth::user()?->hasRole('super_admin')),
                ActionsEditAction::make(),
                ActionsDeleteAction::make()
                    ->requiresConfirmation()
                        ->modalHeading('Hapus Data')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Data')
                        ->successNotificationTitle('Data berhasil dihapus')
                        ->after(function (Model $record) {
                            activity()
                                ->causedBy(Auth::user())
                                ->performedOn($record)
                                ->event('deleted')
                                ->useLog('Pengguna')
                                ->log('Telah menghapus akun pengguna dengan nama ' . $record->name . ' dengan role ' . $record->getRoleNames()->first() . '.');
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data dihapus')
                                ->body('Data telah berhasil dihapus dari sistem.')
                                ->icon('heroicon-o-trash')
                                ->iconColor('success')
                                ->duration(5000)
                        ),

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->successNotificationTitle('Data berhasil dihapus')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data dihapus')
                                ->body('Semua data terpilih telah berhasil dihapus dari sistem.')
                                ->icon('heroicon-o-trash')
                                ->iconColor('success')
                                ->duration(5000)
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
