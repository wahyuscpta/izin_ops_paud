<?php

namespace App\Filament\Resources\PermohonanResource\Widgets;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    public $showModalVerifikasi = false;

    public $showModalValidasi = false;

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
            Fieldset::make('')
            ->schema([

                Textarea::make('catatan')
                    ->label('')                
                    ->rows(4)
                    ->visible(fn () => $this->showModalTolak),

                Grid::make(2)
                    ->schema([
                        TextInput::make('no_surat_rekomendasi')
                            ->label('Nomor Surat Rekomendasi')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'string', 
                                'max:255', 
                                'regex:/^[A-Za-z0-9\/\.\- ]+$/'
                            ])
                            ->visible(fn () => $this->showModalValidasi),

                        DatePicker::make('tgl_surat_rekomendasi')
                            ->label('Tanggal Surat Rekomendasi')
                            ->required()
                            ->rules(['date'])
                            ->visible(fn () => $this->showModalValidasi),

                    ]),

                Grid::make(2)
                ->schema([

                    TextInput::make('pemberi_rekomendasi')
                            ->label('Pemberi Rekomendasi')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'string', 
                                'max:255',
                            ])
                            ->columnSpanFull()
                            ->visible(fn () => $this->showModalValidasi),

                        TextInput::make('no_verifikasi')
                            ->label('Nomor Berkas Verifikasi')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'string',
                                'max:255',
                                'regex:/^[A-Za-z0-9\/\.\- ]+$/'
                            ])
                            ->visible(fn () => $this->showModalValidasi),

                        DatePicker::make('tgl_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->required()
                            ->rules(['date'])
                            ->visible(fn () => $this->showModalValidasi),

                        TextInput::make('no_sk')
                            ->label('Nomor SK Izin Operasional')
                            ->required()
                            ->numeric()
                            ->maxLength(255)
                            ->visible(fn () => $this->showModalValidasi),

                        FileUpload::make('file_validasi_lapangan')
                            ->label('Upload Berkas Validasi Lapangan')
                            ->directory('lampiran')
                            ->disk('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048)
                            ->extraAttributes(['class' => 'custom-file-upload'])
                            ->required()
                            ->visible(fn () => $this->showModalValidasi),
                ]),
            ])
        ];
    }

    public function submitVerifikasi()
    {
        $this->record->update([
                'status_permohonan' => 'menunggu_validasi_lapangan'
            ]);
        
        Notification::make()
            ->success()
            ->title('Proses Berhasil')
            ->body('Status permohonan telah diverifikasi')
            ->send();

        return redirect()->to('permohonans');
    }

    public function save()
    {
        $data = $this->form->getState();

        try {
            if (!empty($data['file_validasi_lapangan'])) {
                $filePath = is_array($data['file_validasi_lapangan'])
                    ? reset($data['file_validasi_lapangan'])
                    : $data['file_validasi_lapangan'];

                $this->record->lampiran()->create([
                    'lampiran_type' => 'file_validasi_lapangan',
                    'lampiran_path' => $filePath,
                ]);
            }

            $this->record->update([
                'status_permohonan' => 'proses_penerbitan_izin',
                'no_verifikasi' => $data['no_verifikasi'],
                'tgl_verifikasi' => $data['tgl_verifikasi'],
            ]);

            
            Notification::make()
                ->success()
                ->title('Proses Berhasil')
                ->body('Status validasi lapangan berhasil disimpan')
                ->send();
            
            return redirect()->to('permohonans');
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Proses Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
            
            return null;
        }
    }

    public function confirmIzinProcess()
    {
        $this->record->update(['status_permohonan' => 'izin_diterbitkan']);
        
        Notification::make()
            ->success()
            ->title('Proses Berhasil')
            ->body('Izin telah berhasil diterbitkan')
            ->send();

        return redirect()->to('permohonans');
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
            ->body('Penolakan berhasil disimpan dengan catatan yang diberikan')
            ->send();

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

    public function openModalVerifikasi()
    {
        $this->showModalVerifikasi = true;

        $this->dispatch('open-modal', id: 'konfirmasi-verifikasi');
    }

    public function closeModalVerifikasi()
    {
        $this->showModalVerifikasi = false;

        $this->dispatch('close-modal', id: 'konfirmasi-verifikasi');
    }

    public function openModalValidasi()
    {
        $this->showModalValidasi = true;

        $this->dispatch('open-modal', id: 'validasi-lapangan');
    }

    public function closeModalValidasi()
    {
        $this->showModalValidasi = false;

        $this->dispatch('close-modal', id: 'validasi-lapangan');
    }

}