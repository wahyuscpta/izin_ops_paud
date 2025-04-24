<?php

namespace App\Filament\Widgets;

use App\Models\Permohonan;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PermohonanTerbaruWidget extends BaseWidget
{
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 3;

    protected int $permohonanLimit = 5;

    protected function getTableQuery(): Builder
    {
        return Permohonan::query()
            ->where('status_permohonan', 'menunggu_verifikasi')
            ->latest()
            ->limit($this->permohonanLimit);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('no_permohonan')
                ->label('No Permohonan'),

            TextColumn::make('user.name')
                ->label('Nama Pemohon'),

            TextColumn::make('identitas.nama_lembaga')
                ->label('Nama Lembaga'),                
            
            TextColumn::make('tgl_permohonan')
                ->label('Tanggal Pengajuan')
                ->date('d M Y'),                    
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('tinjau')
                ->label('Tinjau')
                ->url(fn (Permohonan $record): string => route('filament.admin.resources.permohonans.view', $record))
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function getTableRecordUrlUsing(): \Closure
    {
        return fn (Permohonan $record): string => 
            route('filament.admin.resources.permohonans.view', $record);
    }

    protected function getTableHeading(): string
    {
        return 'Permohonan Menunggu Verifikasi';
    }

    protected function getTableDescription(): string|Htmlable|null
    {
        return 'Daftar permohonan yang sedang menunggu verifikasi';
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function isTableSearchable(): bool
    {
        return false;
    }

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'kepala_dinas', 'super_admin']);
    }
}