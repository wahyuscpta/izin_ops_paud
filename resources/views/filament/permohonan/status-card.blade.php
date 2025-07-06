@push('styles')
<style>
    /* div[wire\:id] .filepond--root {
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
    }    */

    @media (min-width: 996px) and (max-width: 1366px) {
        .badge-status{
            display: block;
        }

        .info-status p{
            font-size: 12px !important;
        }
    }
</style>
@endpush

<div class="root">

    {{-- Tanggal Kunjungan --}}
    @if ($record->status_permohonan === 'menunggu_validasi_lapangan')
        <div class="flex items-start justify-between border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 self-start" style="border-radius: 10px; padding: 10px 30px; margin-bottom: 20px">
            <div>
                <p class="mt-1 text-sm font-semibold text-primary-700">Tanggal Kunjungan Lapangan</p>
                <p class="text-gray-600 dark:text-gray-500">
                    {{ $record->tanggal_kunjungan ? \Carbon\Carbon::parse($record->tanggal_kunjungan)->locale('id')->format('d M Y') : '-' }}
                </p>
            </div>

            @if (Auth::user()->hasRole('admin'))
                <x-filament::button
                    size="sm"
                    color="primary"
                    wire:click="openModalEditTanggal"
                    style="margin-top: 12px" 
                    :disabled="\Carbon\Carbon::parse($record->tanggal_kunjungan)->isToday()"
                    title="{{ \Carbon\Carbon::parse($record->tanggal_kunjungan)->isToday() ? 'Tanggal hari ini tidak dapat diubah' : 'Ubah tanggal kunjungan' }}"
                >
                    <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4"/>
                </x-filament::button>
            @endif
        </div>
    @endif

    {{-- Status Permohonan --}}
    <div class="border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 self-start" style="border-radius: 10px; padding: 30px">
        <div class="flex justify-between items-center badge-status">
            <p class="text-sm font-semibold mb-4">Status :</p>
            <x-filament::badge
                :color="match($record->status_permohonan) {
                    'draft' => 'secondary',
                    'menunggu_verifikasi' => 'warning',
                    'menunggu_validasi_lapangan', 'proses_penerbitan_izin' => 'success',
                    'izin_diterbitkan' => 'primary',
                    'permohonan_ditolak' => 'danger',
                    default => 'secondary',
                }"
                style="margin-top: -10px"
                class="text-uppercase"
            >
                {{ ucwords(str_replace('_', ' ', $record->status_permohonan)) }}
            </x-filament::badge>
        </div>  

        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <div class="space-y-4 pt-2 info-status">
            <div>
                <p class="mt-2 text-sm font-semibold">Nama Pemohon</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->user->name }}</p>
            </div>

            <div>
                <p class="mt-2 text-sm font-semibold">Nama Lembaga</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->identitas->nama_lembaga }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Tanggal Diajukan</p>
                <p class="text-gray-600 dark:text-gray-500">{{ \Carbon\Carbon::parse($record->tgl_permohonan)->locale('id')->format('d M Y') }}</p>
            </div>

            @php
                $status = $record->status_permohonan;
                $tanggal = \Carbon\Carbon::parse($record->tgl_status_terakhir)->locale('id')->format('d M Y');
            
                $labelTanggal = match ($status) {
                    'menunggu_validasi_lapangan' => 'Tanggal Verifikasi Permohonan',
                    'proses_penerbitan_izin' => 'Tanggal Validasi Lapangan',
                    'ditolak' => 'Tanggal Penolakan',
                    'izin_diterbitkan' => 'Tanggal Diterbitkan',
                    default => null, // untuk 'menunggu_verifikasi', dll
                };
            @endphp
            
            @if ($labelTanggal)
                <div>
                    <p class="text-sm font-semibold">{{ $labelTanggal }}</p>
                    <p class="text-gray-600 dark:text-gray-500">{{ $tanggal }}</p>
                </div>
            @endif

            <div>
                <p class="text-sm font-semibold">Catatan</p>
                <p class="text-gray-600 dark:text-gray-500">{{ $record->catatan  ?? '-'}}</p>
            </div>

            @if ($record->status_permohonan === 'izin_diterbitkan')
                <div>
                    <p class="text-sm font-semibold">SK Izin Operasional</p>
                    <x-filament::button
                        tag="a"
                        href="{{ route('download.sk-izin', ['id' => $record->id]) }}"
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
                        href="{{ route('download.sertifikat', ['id' => $record->id]) }}"
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

    {{-- Tombol Ajukan Kembali --}}
    @if (auth()->user()->hasRole('pemohon') && $record->status_permohonan === 'permohonan_ditolak')
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.permohonans.edit', $record->id) }}"
            color="primary"
            class="w-full p-4 mt-6"
        >
            Ajukan Kembali
        </x-filament::button>
    @endif

    {{-- Tombol Verifikasi --}}
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

    {{-- Tombol Validasi --}}
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

    {{-- Tombol Penerbitan Izin --}}
    @if (auth()->user()->hasRole('admin') && $record->status_permohonan === 'proses_penerbitan_izin')
        <div class="flex md:flex-row justify-between gap-4 pt-6">
            <x-filament::button
                tag="a"
                href="{{ route('sk-izin.generate-pdf', $record->id) }}"
                target="_blank"
                icon="heroicon-m-arrow-down-tray"
                color="gray"
                class="w-full p-2"
            >
                Draft SK
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ route('sertifikat.pdf', $record->id) }}"
                target="_blank"
                icon="heroicon-m-arrow-down-tray"
                color="gray"
                class="w-full p-2"
            >
                Draft Sertifikat
            </x-filament::button>
        </div>

        <div class="flex md:flex-row justify-between gap-4 pt-6">
            <x-filament::button color="primary" class="w-full h-full" wire:click="openModalPenerbitanIzin">
                Unggah SK & Sertifikat (TTD)
            </x-filament::button>
        </div>
    @endif

    {{-- Modal Edit Tangaal --}}
    <x-filament::modal id="edit-tanggal" width="2xl" wire:model="showModalEditTanggal" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-6 w-6 text-primary-500" />
                <span>Ubah Tanggal Kunjungan Lapangan</span>
            </div>
        </x-slot>

        <div class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 pt-10 p-6 space-y-6">                        
            <div class="space-y-4 p-4">                    
                {{ $this->form }}
            </div>
        </div>

        <x-slot name="description">
            Silakan pilih tanggal baru untuk kunjungan validasi lapangan. Pastikan tanggal yang dipilih sudah disepakati dan bukan hari ini atau sebelumnya.
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeModalEditTanggal">
                    Batal
                </x-filament::button>

                <x-filament::button color="primary" wire:click="submitTanggalBaru" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitTanggalBaru">Simpan Perubahan</span>
                    <span wire:loading wire:target="submitTanggalBaru">Memproses...</span>
                </x-filament::button>  
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Modal Tolak --}}
    <x-filament::modal id="catatan-tolak" width="2xl" wire:model="showModalTolak" :close-by-clicking-away="false">
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

    {{-- Modal Verifikasi --}}
    <x-filament::modal id="konfirmasi-verifikasi" width="2xl" wire:model="showModalVerifikasi" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-6 w-6 text-primary-500" />
                @if ($wizardStep === 1)
                    <span>Konfirmasi Verifikasi</span>
                @elseif ($wizardStep === 2)
                    <span>Atur Tanggal Kunjungan Lapangan</span>
                @endif
            </div>
        </x-slot>

        <div class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 pt-10 p-6 space-y-6">
                        
            @if ($wizardStep === 1)
            {{-- STEP 1: KONFIRMASI --}}
            <div class="space-y-2">
                <div class="flex items-start gap-3">
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

            <!-- Peringatan -->
            <div>
                <div class="flex items-start gap-3">
                    <p class="text-sm text-amber-800 dark:text-amber-100 italic" style="color: #f59e0b;" >
                        Tindakan ini tidak dapat dibatalkan. Pastikan semua dokumen telah diperiksa dengan benar sebelum melanjutkan.
                    </p>
                </div>
            </div>

            @elseif ($wizardStep === 2)
                {{-- STEP 2: INPUT TANGGAL --}}
                <div class="space-y-4 p-4">                    
                    {{ $this->form }}
                </div>

                <x-slot name="description">
                    Silakan pilih tanggal baru untuk kunjungan validasi lapangan. Pastikan tanggal yang dipilih sudah disepakati dan bukan hari ini atau sebelumnya.
                </x-slot>
            @endif

        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeModalVerifikasi">
                    Batal
                </x-filament::button>

                @if ($wizardStep === 1)
                    <x-filament::button color="primary" wire:click="nextStep">
                        Lanjutkan
                    </x-filament::button>
                @elseif ($wizardStep === 2)
                    <x-filament::button color="primary" wire:click="submitVerifikasi" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitVerifikasi">Simpan dan Verifikasi</span>
                        <span wire:loading wire:target="submitVerifikasi">Memproses...</span>
                    </x-filament::button>  
                @endif
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Modal Validasi Lapangan --}}
    <x-filament::modal id="validasi-lapangan" width="5xl" wire:model="showModalValidasi" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-check-badge" class="h-5 w-5 text-primary-500" />
                <span>Validasi Lapangan</span>
            </div>
        </x-slot>
        <x-slot name="description">Upload berkas hasil validasi lapangan dan informasi verifikasi untuk penerbitan SK dan Sertifikat.</x-slot>

        {{ $this->form }}

        <x-slot name="footer">
            <div class="flex md:flex-row justify-end gap-4">
                <x-filament::button color="gray" wire:click="closeModalValidasi">
                    Batal
                </x-filament::button>

                <x-filament::button color="primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Validasi Permohonan</span>
                    <span wire:loading wire:target="save">Memproses...</span>
                </x-filament::button>          
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Modal Penerbitan Izin --}}
    <x-filament::modal id="penerbitan-izin" width="3xl" wire:model="showModalPenerbitanIzin" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-check-badge" class="h-5 w-5 text-primary-500" />
                <span>Penerbitan Izin</span>
            </div>
        </x-slot>
        <x-slot name="description">Upload SK dan Sertifikat Izin Operasional yang telah disetujui Kepala Dinas</x-slot>

        {{ $this->form }}

        <x-slot name="footer">
            <div class="flex md:flex-row justify-end gap-4">
                <x-filament::button color="gray" wire:click="closeModalValidasi">
                    Batal
                </x-filament::button>

                <x-filament::button color="primary" wire:click="submitPenerbitanIzin" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitPenerbitanIzin">Upload & Terbitkan Izin</span>
                    <span wire:loading wire:target="submitPenerbitanIzin">Memproses...</span>
                </x-filament::button> 
            </div>
        </x-slot>
    </x-filament::modal>

</div>