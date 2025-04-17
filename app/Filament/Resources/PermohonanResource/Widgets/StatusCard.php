<?php

namespace App\Filament\Resources\PermohonanResource\Widgets;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class StatusCard extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.permohonan.status-card';

    public $record;

    public $showModalTolak = false;

    public $formData = [];

    public function mount($record)
    {
        $this->record = $record;        
    }

    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    public function getFormSchema(): array
    {
        return [
            Textarea::make('catatan')
                ->label('')                
                ->rows(4)
                ->visible(fn () => $this->showModalTolak),

            FileUpload::make('file_validasi_lapangan')
                ->label('Upload Berkas Validasi Lapangan')
                ->directory('lampiran')
                ->disk('public')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(2048)
                ->visible(fn () => !$this->showModalTolak),
        ];
    }

    public function confirmVerification()
    {
        $this->record->update(['status_permohonan' => 'menunggu_validasi_lapangan']);
        return redirect()->to('permohonans');
    }

    public function save()
    {
        $data = $this->form->getState();

        if (!empty($data['file_validasi_lapangan'])) {
            $filePath = is_array($data['file_validasi_lapangan'])
                ? reset($data['file_validasi_lapangan'])
                : $data['file_validasi_lapangan'];

            $this->record->lampiran()->create([
                'lampiran_type' => 'file_validasi_lapangan',
                'lampiran_path' => $filePath,
            ]);
        }

        $this->record->update(['status_permohonan' => 'proses_penerbitan_izin']);
        
        return redirect()->to('permohonans');
    }

    public function confirmIzinProcess()
    {
        $this->record->update(['status_permohonan' => 'izin_diterbitkan']);
        return redirect()->to('permohonans');
    }

    public function openModalTolak()
    {
        $this->showModalTolak = true;

        $this->dispatch('open-modal', id: 'catatan-tolak');
    }

    public function closeModalTolak()
    {
        $this->showModalTolak = false;

        $this->dispatch('close-modal', id: 'catatan-tolak');

        $this->form->fill([
            'catatan' => '',
        ]);
    }

    public function submitPenolakan()
    {
        $data = $this->form->getState();

        $this->record->update([
            'status_permohonan' => 'permohonan_ditolak',
            'catatan' => $data['catatan'],
        ]);

        $this->dispatch('close-modal', id: 'catatan-tolak');

        Notification::make()
            ->success()
            ->title('Permohonan telah ditolak')
            ->send();

        return redirect()->to('permohonans');
    }
}