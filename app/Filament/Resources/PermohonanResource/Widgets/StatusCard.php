<?php

namespace App\Filament\Resources\PermohonanResource\Widgets;

use App\Models\Permohonan;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatusCard extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.permohonan.status-card';

    public $record;

    public int $wizardStep = 1;

    public $showModalTolak = false;

    public $showModalEditTanggal = false;

    public $showModalVerifikasi = false;

    public $showModalValidasi = false;

    public $showModalPenerbitanIzin = false;

    public $formData = [];

    public function mount($record)
    {
        $this->record = $record;        
    }

    // Menentukan path penyimpanan state form di dalam komponen
    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    public function getFormSchema(): array
    {
        return [                                
            DatePicker::make('tanggal_kunjungan')
                ->label('Tanggal Kunjungan Lapangan')
                ->required()
                ->columnSpanFull()
                ->rules(['after:today'])
                ->validationMessages([
                    'after' => 'Tanggal kunjungan harus lebih dari hari ini.',
                    'required' => 'Tanggal kunjungan harus diisi.'
                ])
                ->visible(fn () => $this->showModalVerifikasi || $this->showModalEditTanggal),

            Fieldset::make('')
            ->schema([
                Textarea::make('catatan')
                ->label('')                
                ->rows(4)
                ->columnSpanFull()
                ->validationMessages([
                    'required' => 'Catatan harus diisi.'
                ])
                ->visible(fn () => $this->showModalTolak),            

                TextInput::make('pemberi_rekomendasi')
                    ->label('Pemberi Rekomendasi')
                    ->required()
                    ->maxLength(255)
                    ->rules(['string', 'max:255',])
                    ->validationMessages([
                        'required' => 'Pemberi Rekomendasi harus diisi.',
                        'rules' => 'Maksimal 255 kata'
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => $this->showModalValidasi),

                TextInput::make('no_surat_rekomendasi')
                    ->label('Nomor Surat Rekomendasi')
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        'string', 
                        'max:255', 
                        'regex:/^[A-Za-z0-9\/\.\- ]+$/'
                    ])
                    ->validationMessages([
                        'required' => 'Nomor Surat Rekomendasi harus diisi.',
                        'rules' => 'Inputan tidak sesuai kebijakan berlaku.'
                    ])
                    ->visible(fn () => $this->showModalValidasi),

                DatePicker::make('tgl_surat_rekomendasi')
                    ->label('Tanggal Surat Rekomendasi')
                    ->required()
                    ->rules(['date'])
                    ->validationMessages([
                        'required' => 'Tanggal Surat Rekomendasi harus diisi.',
                        'rules' => 'Inputan tidak sesuai kebijakan berlaku.'
                    ])
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
                    ->validationMessages([
                        'required' => 'Nomor Berkas Verifikasi harus diisi.',
                        'rules' => 'Inputan tidak sesuai kebijakan berlaku.'
                    ])
                    ->visible(fn () => $this->showModalValidasi),

                DatePicker::make('tgl_verifikasi')
                    ->label('Tanggal Verifikasi')
                    ->required()
                    ->rules(['date'])
                    ->validationMessages([
                        'required' => 'Tanggal Verifikasi harus diisi.',
                        'rules' => 'Inputan tidak sesuai kebijakan berlaku.'
                    ])
                    ->visible(fn () => $this->showModalValidasi),


                FileUpload::make('file_validasi_lapangan')
                    ->label('Upload Berkas Validasi Lapangan')
                    ->columnSpanFull()
                    ->directory('lampiran')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->extraAttributes(['class' => 'custom-file-upload'])
                    ->required()
                    ->validationMessages([
                        'required' => 'Berkas Validasi Lapangan harus diisi.',
                        'max' => 'Ukuran berkas tidak boleh lebih dari 10 MB.'
                    ])
                    ->hint('Unggah File PDF maks. 10MB')
                    ->visible(fn () => $this->showModalValidasi),

                FileUpload::make('sk_izin')
                    ->label('Upload SK Izin Operasional')
                    ->columnSpanFull()
                    ->directory('lampiran')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->required()
                    ->validationMessages([
                        'required' => 'SK Izin Operasional harus diisi.'
                    ])
                    ->visible(fn () => $this->showModalPenerbitanIzin),

                FileUpload::make('sertifikat_izin')
                    ->label('Upload Sertifikasi Izin Operasional')
                    ->columnSpanFull()
                    ->directory('lampiran')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->required()
                    ->validationMessages([
                        'required' => 'Sertifikat Izin Operasional harus diisi.'
                    ])
                    ->visible(fn () => $this->showModalPenerbitanIzin),
            ])
            ->visible(fn () => !$this->showModalVerifikasi && !$this->showModalEditTanggal),
        ];
    }

    public function nextStep()
    {
        $this->wizardStep++;
    }

    // Method untuk memverifikasi permohonan
    public function submitTanggalBaru()
    {
        // Ambil seluruh data form (termasuk file dan field input lainnya)
        $data = $this->form->getState();

        // Update status permohonan di database
        $this->record->update([
            'tanggal_kunjungan' => $data['tanggal_kunjungan'],
        ]);        

        // Catat aktivitas verifikasi
        activity()
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'attributes' => [
                    'status_permohonan' => $this->record->status_permohonan,
                    'nama_pemohon' => $this->record->nama_pemohon,
                ],
                'role' => Auth::user()?->getRoleNames()?->first(),
            ])
            ->event('updated')
            ->useLog('Permohonan') 
            ->log('Telah mengubah tanggal kunjungan lapangan menjadi ' . Carbon::parse($this->record->tanggal_kunjungan)->locale('id')->translatedFormat('d F Y') . '.');

        // Tampilkan notifikasi berhasil ke pengguna
        Notification::make()
            ->success()
            ->title('Proses Berhasil')
            ->body('Tanggal kunjungan lapangan berhasil diubah.')
            ->send();

        // Redirect pengguna ke halaman daftar permohonan
        return redirect()->to('permohonans');
    }

    // Method untuk memverifikasi permohonan
    public function submitVerifikasi()
    {
        // Ambil seluruh data form (termasuk file dan field input lainnya)
        $data = $this->form->getState();

        // Update status permohonan di database
        $this->record->update([
            'tanggal_kunjungan' => $data['tanggal_kunjungan'],
            'status_permohonan' => 'menunggu_validasi_lapangan'
        ]);        

        // Catat aktivitas verifikasi
        activity()
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'attributes' => [
                    'status_permohonan' => $this->record->status_permohonan,
                    'nama_pemohon' => $this->record->nama_pemohon,
                ],
                'role' => Auth::user()?->getRoleNames()?->first(),
            ])
            ->event('updated')
            ->useLog('Permohonan') 
            ->log('Telah memverifikasi permohonan milik ' . $this->record->identitas->nama_lembaga . '. Status diperbarui menjadi: MENUNGGU VALIDASI LAPANGAN.');

        // Tampilkan notifikasi berhasil ke pengguna
        Notification::make()
            ->success()
            ->title('Proses Berhasil')
            ->body('Status permohonan telah diverifikasi.')
            ->send();

        // Redirect pengguna ke halaman daftar permohonan
        return redirect()->to('permohonans');
    }

    // Method untuk menyimpan hasil validasi lapangan
    public function save()
    {
        // Ambil seluruh data form (termasuk file dan field input lainnya)
        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($data) {
                // Jika ada file validasi lapangan yang diunggah
                if (!empty($data['file_validasi_lapangan'])) {
                    // Ambil path file (support untuk array/multiple dan single)
                    $filePath = is_array($data['file_validasi_lapangan'])
                        ? reset($data['file_validasi_lapangan']) // ambil file pertama jika multiple
                        : $data['file_validasi_lapangan'];
    
                    // Simpan file sebagai lampiran ke tabel lampiran terkait permohonan
                    $this->record->lampiran()->create([
                        'lampiran_type' => 'file_validasi_lapangan',
                        'lampiran_path' => $filePath,
                    ]);
                }

                // Nilai awal acuan jika belum ada SK sebelumnya
                $defaultSk = 351;
                // Ambil nomor SK terakhir dari database (jika ada)
                $latestSk = Permohonan::whereNotNull('no_sk')
                    ->orderByDesc('no_sk')
                    ->value('no_sk');
                // Konversi nilai ke integer, lalu tambahkan 1
                $newSk = intval($latestSk ?? $defaultSk) + 1;

                // Update data permohonan
                $this->record->update([
                    'status_permohonan' => 'proses_penerbitan_izin',
                    'no_surat_rekomendasi' => $data['no_surat_rekomendasi'],
                    'tgl_surat_rekomendasi' => $data['tgl_surat_rekomendasi'],
                    'pemberi_rekomendasi' => $data['pemberi_rekomendasi'],
                    'no_verifikasi' => $data['no_verifikasi'],
                    'tgl_verifikasi' => $data['tgl_verifikasi'],
                    'no_sk' => $newSk
                ]);
            });

            // Catat aktivitas bahwa proses validasi lapangan telah dilakukan
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this->record)
                ->withProperties([
                    'attributes' => [
                        'status_permohonan' => $this->record->status_permohonan,
                        'nama_pemohon' => $this->record->nama_pemohon,
                    ],
                    'role' => Auth::user()?->getRoleNames()?->first(),
                ])
                ->event('updated')
                ->useLog('Permohonan') 
                ->log('Telah melakukan proses validasi lapangan untuk permohonan milik ' . $this->record->identitas->nama_lembaga . '. Status diperbarui menjadi: PROSES PENERBITAN IZIN.');
            
            // Tampilkan notifikasi sukses ke pengguna
            Notification::make()
                ->success()
                ->title('Proses Berhasil')
                ->body('Status validasi lapangan berhasil disimpan')
                ->send();
            // Redirect kembali ke halaman daftar permohonan
            return redirect()->to('permohonans');
        } catch (\Exception $e) {
            // Tampilkan notifikasi jika terjadi error saat menyimpan
            Notification::make()
                ->danger()
                ->title('Proses Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
            
            return null;
        }
    }

    public function submitPenerbitanIzin()
    {
        $data = $this->form->getState();
        
        try {
            foreach (['sk_izin', 'sertifikat_izin'] as $type) {
                if (!empty($data[$type])) {
                    $filePath = is_array($data[$type]) ? reset($data[$type]) : $data[$type];
                    
                    // Ambil file original dari temporary storage
                    $tmpPath = storage_path('app/public/' . $filePath);
                    
                    // Generate nama file baru
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $newFileName = strtoupper(str_replace(' ', '_', $type . '_' . $this->record->identitas->nama_lembaga)) . '.' . $extension;
                    
                    // Path tujuan untuk file dengan nama baru
                    $newPath = 'lampiran/' . $newFileName;
                    $newFullPath = storage_path('app/public/' . $newPath);
                    
                    // Pindahkan file dengan nama baru
                    if (File::exists($tmpPath)) {
                        // Pastikan direktori tujuan ada
                        File::ensureDirectoryExists(dirname($newFullPath));
                        
                        // Salin file ke lokasi baru dengan nama baru
                        File::copy($tmpPath, $newFullPath);
                        
                        // Hapus file temporary (opsional)
                        File::delete($tmpPath);
                        
                        // Simpan path baru ke database
                        $this->record->lampiran()->create([
                            'lampiran_type' => $type,
                            'lampiran_path' => $newPath,
                        ]);
                    }
                }
            }
        
            // Update data permohonan
            $this->record->update([
                'status_permohonan' => 'izin_diterbitkan',
                'tgl_status_terakhir' => now()
            ]);

            // Catat aktivitas bahwa proses validasi lapangan telah dilakukan
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this->record)
                ->withProperties([
                    'attributes' => [
                        'status_permohonan' => $this->record->status_permohonan,
                        'nama_pemohon' => $this->record->nama_pemohon,
                    ],
                    'role' => Auth::user()?->getRoleNames()?->first(),
                ])
                ->event('updated')
                ->useLog('Permohonan') 
                ->log('Telah menerbitkan izin operasional untuk permohonan milik ' . $this->record->identitas->nama_lembaga . '. Status diperbarui menjadi: IZIN DITERBITKAN.');
        
            // Tampilkan notifikasi sukses ke pengguna
            Notification::make()
                ->success()
                ->title('Proses Berhasil')
                ->body('Izin Operasional telah berhasil diterbitkan')
                ->send();
        
            return redirect()->to('permohonans');
        } catch (\Exception $e) {
            // Tampilkan notifikasi gagal ke pengguna
            Notification::make()
                ->danger()
                ->title('Proses Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
        
            return null;
        }
        
    }

    // Method untuk penolakan permohonan
    public function submitPenolakan()
    {
        $data = $this->form->getState();

        $this->record->update([
            'status_permohonan' => 'permohonan_ditolak',
            'catatan' => $data['catatan'],
        ]);

        // Mengirim event Livewire untuk menutup modal dengan ID 'catatan-tolak'
        $this->dispatch('close-modal', id: 'catatan-tolak');

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'attributes' => [
                    'status_permohonan' => $this->record->status_permohonan,
                    'nama_pemohon' => $this->record->nama_pemohon,
                ],
                'role' => Auth::user()?->getRoleNames()?->first(),
            ])
            ->event('updated')
            ->useLog('Permohonan') 
            ->log('Telah menolak permohonan izin operasional milik ' . $this->record->identitas->nama_lembaga . '. Status diperbarui menjadi: PERMOHONAN DITOLAK.');

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

    public function openModalPenerbitanIzin()
    {
        $this->showModalPenerbitanIzin = true;

        $this->dispatch('open-modal', id: 'penerbitan-izin');
    }

    public function closeModalPenerbitanIzin()
    {
        $this->showModalPenerbitanIzin = false;

        $this->dispatch('close-modal', id: 'penerbitan-izin');
    }

    public function openModalEditTanggal()
    {
        if (Carbon::parse($this->record->tanggal_kunjungan)->isToday()) {
            Notification::make()
                ->warning()
                ->title('Tanggal tidak dapat diubah')
                ->body('Tanggal kunjungan hari ini tidak bisa diubah.')
                ->send();
            return;
        }
    
        $this->showModalEditTanggal = true;

        $this->dispatch('open-modal', id: 'edit-tanggal');
    }

    public function closeModalEditTanggal()
    {
        $this->showModalEditTanggal = false;

        $this->dispatch('close-modal', id: 'edit-tanggal');
    }

}