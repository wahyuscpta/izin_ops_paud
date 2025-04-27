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
                <p class="text-gray-400">{{ $record->penyelenggara->nama_perorangan }}</p>
            </div>

            <div>
                <p class="mt-2 text-sm font-semibold">Nama Lembaga</p>
                <p class="text-gray-400">{{ $record->identitas->nama_lembaga }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Tanggal Diajukan</p>
                <p class="text-gray-400">{{ \Carbon\Carbon::parse($record->tgl_permohonan)->format('d M Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Tanggal Status Diubah Terakhir</p>
                <p class="text-gray-400">{{ \Carbon\Carbon::parse($record->tgl_status_terakhir)->format('d M Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold">Catatan</p>
                <p class="text-gray-400">{{ $record->catatan  ?? '-'}}</p>
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
                    <x-filament::button color="primary" class="w-full p-2 mt-2">
                        {{-- <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-5"/> --}}
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

    @if (auth()->user()->hasRole('kepala_dinas'))
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

    <x-filament::modal id="verifikasi-administrasi" width="2xl">
        <x-slot name="heading">Verifikasi Administrasi</x-slot>
        <x-slot name="description">Masukkan informasi terkait surat rekomendasi yang diterima untuk permohonan ini.</x-slot>

        {{ $this->form }}

        <x-slot name="footer">
            <div class="flex md:flex-row justify-end gap-4">
                <x-filament::button color="gray" wire:click="closeModalVerifikasi">
                    Batal
                </x-filament::button>

                <x-filament::button color="primary" wire:click="submitVerifikasi">
                    Verifikasi
                </x-filament::button>            
            </div>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="validasi-lapangan" width="2xl">
        <x-slot name="heading">Validasi Lapangan</x-slot>
        <x-slot name="description">Upload berkas hasil validasi lapangan dan informasi verifikasi untuk permohonan ini.</x-slot>

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