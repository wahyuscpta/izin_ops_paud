<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomActivitylogResource\Pages;
use App\Filament\Resources\CustomActivitylogResource\RelationManagers;
use App\Models\CustomActivitylog;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

class CustomActivitylogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationLabel = 'Riwayat Aktivitas';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Manajemen Aktivitas';

    protected static ?string $label = 'Riwayat Aktivitas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Str::ucfirst($state)),

                TextColumn::make('event')
                    ->label('Jenis Perubahan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Str::ucfirst($state)),

                TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi Aktivitas')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Waktu Aktivitas')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Jenis Log')
                    ->options([
                        'Permohonan' => 'Permohonan',
                        'Pengguna' => 'Pengguna',
                    ]),

                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                Filter::make('waktu')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
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
            'index' => \App\Filament\Resources\CustomActivitylogResource\Pages\ListCustomActivitylogs::route('/'),
        ];
    }
}
