@push('styles')
<style>
    div[wire\:id] .filepond--root {
        min-height: 37px !important;
    }
    
    div[wire\:id] .filepond--panel-root {
        min-height: 37px !important;
    }
    
    div[wire\:id] .filepond--drop-label {
        min-height: 37px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }    
</style>
@endpush

<div class="root">

    <div class="border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 self-start" style="border-radius: 10px; padding: 30px">
        <div class="flex justify-between items-center">
            <p class="text-sm font-semibold mb-4">Status :</p>
            <x-filament::badge
                :color="match($record->status_permohonan) {
                    'draft' => 'secondary',
                    'menunggu_verifikasi' => 'warning',
                    'menunggu_validasi_lapangan', 'proses_penerbitan_izin' => 'success',
                    'izin_diterbitkan' => 'primary',
                    'ditolak' => 'danger',
                    default => 'secondary',
                }"
                style="margin-top: -10px"
            >
                {{ ucwords(str_replace('_', ' ', $record->status_permohonan)) }}
            </x-filament::badge>
        </div>  

        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <div class="space-y-4 pt-2">
            <div>
                <p class="mt-2 text-sm font-semibold">Nama Pemohon</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->penyelenggara->nama_perorangan }}</p>
            </div>

            <div>
                <p class="mt-2 text-sm font-semibold">Nama Lembaga</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->identitas->nama_lembaga }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Tanggal Diajukan</p>
                <p class="text-gray-600 dark:text-gray-500">{{ \Carbon\Carbon::parse($record->tgl_permohonan)->format('d M Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Tanggal Status Diubah Terakhir</p>
                <p class="text-gray-600 dark:text-gray-500">{{ \Carbon\Carbon::parse($record->tgl_status_terakhir)->format('d M Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Catatan</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->catatan  ?? '-'}}</p>
            </div>

            @if ($record->status_permohonan === 'izin_diterbitkan')
                <div>
                    <p class="text-sm font-semibold">SK Izin Operasional</p>
                    <x-filament::button
                        tag="a"
                        href="{{ route('sk-izin.generate-pdf', $record->id) }}"
                        target="_blank"
                        icon="heroicon-m-arrow-down-tray"
                        color="primary"
                        class="w-full p-2 mt-2"
                    >
                        Unduh SK
                    </x-filament::button>
                </div>

                <div>
                    <p class="text-sm font-semibold">Sertifikat Izin Operasional:</p>
                    <x-filament::button
                        tag="a"
                        href="{{ route('sertifikat.pdf', $record->id) }}"
                        target="_blank"
                        icon="heroicon-m-arrow-down-tray"
                        color="primary"
                        class="w-full p-2 mt-2"
                    >
                        Unduh Sertifikat
                    </x-filament::button>
                </div>            
            @endif

        </div>
    </div>

    @if (auth()->user()->hasRole('admin') && $record->status_permohonan === 'menunggu_verifikasi')
        <div class="flex md:flex-row justify-between gap-4 pt-6">
            <x-filament::button color="gray" class="w-full p-2" wire:click="openModalTolak">
                Tolak
            </x-filament::button>
            <x-filament::button color="primary" class="w-full p-2" wire:click="openModalVerifikasi">
                Verifikasi
            </x-filament::button>
        </div>
    @endif

    @if (auth()->user()->hasRole('admin') && $record->status_permohonan === 'menunggu_validasi_lapangan')
        <div class="flex md:flex-row justify-between gap-4 pt-6">
            <x-filament::button color="gray" class="w-full p-2" wire:click="openModalTolak">
                Tolak
            </x-filament::button>
            <x-filament::button color="primary" class="w-full p-2" wire:click="openModalValidasi">
                Validasi
            </x-filament::button>
        </div>
    @endif

    @if (auth()->user()->hasRole('kepala_dinas') && $record->status_permohonan === 'proses_penerbitan_izin')
        <div class="flex md:flex-row justify-between gap-4 pt-6">
            <x-filament::button color="gray" class="w-full p-2" wire:click="openModalTolak">
                Tolak
            </x-filament::button>
            <x-filament::button color="primary" class="w-full p-2" wire:click="confirmIzinProcess">
                Proses Izin
            </x-filament::button>
        </div>
    @endif

    <x-filament::modal id="catatan-tolak" width="2xl">
        <x-slot name="heading">Tolak Permohonan</x-slot>
        <x-slot name="description">Berikan catatan atau alasan penolakan atas permohonan ini.</x-slot>

        {{ $this->form }}

        <x-slot name="footer">
            <div class="flex md:flex-row justify-end gap-4">
                <x-filament::button color="gray" wire:click="closeModalTolak">
                    Batal
                </x-filament::button>
                <x-filament::button color="danger" wire:click="submitPenolakan">
                    Tolak Permohonan
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="konfirmasi-verifikasi" class="modal-centered" width="xl" wire:model="isModalVerifikasiOpen">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-6 w-6 text-primary-500" />
                <span>Konfirmasi Verifikasi</span>
            </div>
        </x-slot>
        
    <div class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 p-4 space-y-6">
        <!-- Informasi Verifikasi -->
        <div class="space-y-2">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                    <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-primary-600 dark:text-primary-300" />
                </div>
                <div>
                    <h3 class="text-base font-medium text-primary-900 dark:text-primary-600">
                        Apakah Anda yakin akan memverifikasi permohonan ini?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-500">
                        Permohonan yang terverifikasi akan diproses ke tahap selanjutnya.
                    </p>
                </div>
            </div>

            <hr class="border-t border-gray-200 dark:border-gray-700 w-3/4 mt-4 mx-auto h-.5px" style="margin-top: 25px">
        </div>

        <!-- Detail Pemohon -->
        <div class="space-y-2">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <x-filament::icon icon="heroicon-o-user-circle" class="h-5 w-5 text-gray-700 dark:text-gray-500" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-500">Detail Pemohon:</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-500 my-2">{{ $record->penyelenggara->nama_perorangan }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-500">{{ $record->identitas->nama_lembaga }}</p>
                </div>
            </div>
            
            <hr class="border-t border-gray-200 dark:border-gray-700 w-3/4 mt-4 mx-auto h-.5px" style="margin-top: 25px">
        </div>

        <!-- Peringatan -->
        <div>
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-amber-500 dark:text-amber-300" style="color: #f59e0b;" />
                </div>
                <div>
                    <p class="text-sm text-amber-800 dark:text-amber-100" style="color: #f59e0b;" >
                        Tindakan ini tidak dapat dibatalkan. Pastikan semua dokumen telah diperiksa dengan benar sebelum melanjutkan.
                    </p>
                </div>
            </div>
        </div>
    </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-x-2">
                <x-filament::button color="gray" wire:click="closeModalVerifikasi">
                    Batal
                </x-filament::button>
                <x-filament::button color="primary" wire:click="submitVerifikasi" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitVerifikasi">Verifikasi</span>
                    <span wire:loading wire:target="submitVerifikasi">Memproses...</span>
                </x-filament::button>            
            </div>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="validasi-lapangan" width="3xl">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-check-badge" class="h-5 w-5 text-primary-500" />
                <span>Validasi Lapangan</span>
            </div>
        </x-slot>
        <x-slot name="description">Upload berkas hasil validasi lapangan dan informasi verifikasi untuk permohonan ini sebagai dasar penerbitan SK dan Sertifikat.</x-slot>

        {{ $this->form }}

        <x-slot name="footer">
            <div class="flex md:flex-row justify-end gap-4">
                <x-filament::button color="gray" wire:click="closeModalValidasi">
                    Batal
                </x-filament::button>

                <x-filament::button color="primary" wire:click="save">
                    Validasi
                </x-filament::button>            
            </div>
        </x-slot>
    </x-filament::modal>

</div>