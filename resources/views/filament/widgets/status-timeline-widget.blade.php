<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Status Permohonan
        </x-slot>
        
        <x-slot name="headerEnd">
            @if($permohonan)                
                <x-filament::badge color="gray">
                    {{ $permohonan->no_permohonan }}
                </x-filament::badge>
            @endif
        </x-slot>

        <x-slot name="description">
            Menampilkan status terkini permohonan yang diajukan.
        </x-slot>
        
        @if(!$permohonan)
            <div class="flex justify-center py-6">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.permohonans.create') }}"
                    icon="heroicon-m-plus"
                    color="primary"
                >
                    Ajukan Permohonan
                </x-filament::button>
            </div>

        @elseif($currentStatus === 'izin_diterbitkan')
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <div class="rounded-full p-2">
                        <x-filament::icon icon='heroicon-o-check-circle' class="text-primary-600 border-primary-600 p-2 h-10 w-10 border rounded-full"/>
                    </div>
                </div>
                
                <div class="flex-grow">
                    <h4 class="font-medium text-primary-600">Izin Operasional Telah Diterbitkan</h4>                    
                    <p class="text-sm text-gray-500">Izin operasional Anda telah disetujui dan diterbitkan pada {{ $permohonan->tgl_izin_terbit->format('d M Y') }}</p>
                </div>                
            </div>
            
            <div class="flex justify-between mt-6 gap-3">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.permohonans.create') }}"
                    class="w-full cursor-pointer"
                    color="gray"
                >
                    Buat Permohonan Baru
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.permohonans.view', $permohonan->id) }}"
                    class="w-full cursor-pointer"
                    color="primary"
                >
                    Lihat Permohonan
                </x-filament::button>
            </div>

        @elseif($currentStatus === 'permohonan_ditolak')
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <div class="rounded-full p-2">
                        <x-filament::icon icon='heroicon-o-x-circle' class="text-danger-600 border-danger-600 p-2 h-10 w-10 border rounded-full"/>
                    </div>
                </div>
                
                <div class="flex-grow">
                    <h4 class="font-medium text-success-600">Permohonan Ditolak</h4>                    
                    <p class="text-sm">Permohonan Anda ditolak dengan alasan :</p>
                    <p class="text-sm font-medium mt-1">{{ $permohonan->catatan }}</p>
                </div>                
            </div>
            
            <div class="flex justify-center mt-6">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.permohonans.edit', $permohonan->id) }}"
                    icon="heroicon-m-arrow-path"
                    color="primary"
                >
                    Ajukan Kembali
                </x-filament::button>
            </div>

        @else
            @php
                $allSteps = [
                    'draft' => [
                        'label' => 'Draft',
                        'description' => 'Permohonan masih dalam bentuk draft. Silakan lengkapi seluruh data terlebih dahulu.',
                        'icon' => 'heroicon-o-pencil-square',
                    ],

                    'menunggu_verifikasi' => [
                        'label' => 'Menunggu Verifikasi',
                        'description' => 'Permohonan sedang dalam tahap pemeriksaan dokumen',
                        'icon' => 'heroicon-o-document-magnifying-glass',
                    ],
                    'menunggu_validasi_lapangan' => [
                        'label' => 'Menunggu Validasi Lapangan',
                        'description' => 'Tim verifikator akan melakukan kunjungan lapangan',
                        'icon' => 'heroicon-o-clipboard-document-check',
                    ],
                    'proses_penerbitan_izin' => [
                        'label' => 'Proses Penerbitan Izin',
                        'description' => 'Permohonan telah divalidasi dan sedang dalam proses penerbitan Surat Keputusan dan Sertifikat Izin Operasional',
                        'icon' => 'heroicon-o-document-arrow-up',
                    ],
                    'izin_diterbitkan' => [
                        'label' => 'Izin Diterbitkan',
                        'description' => 'Izin operasional telah disetujui dan diterbitkan',
                        'icon' => 'heroicon-o-document-check',
                    ]
                ];

                $statusIndex = array_search($currentStatus, array_keys($allSteps));
                
                $steps = array_slice($allSteps, 0, $statusIndex + 1, true);

                $created = $permohonan->tgl_permohonan;
                $verifikasi = $permohonan->tgl_verifikasi_berkas;
                $validasi = $permohonan->tgl_validasi_lapangan;
                $penerbitan = $permohonan->tgl_proses_penerbitan_izin;
                $izinTerbit = $permohonan->tgl_izin_terbit;
                $ditolak = $permohonan->tgl_tolak;

                // Hitung durasi antar status jika ada
                $durasi = [];

                if ($verifikasi) {
                    $durasi['created_to_verifikasi'] = $created->diffInDays($verifikasi);
                }

                if ($validasi) {
                    $durasi['verifikasi_to_validasi'] = \Carbon\Carbon::parse($verifikasi)->diffInDays($validasi);
                }

                if ($penerbitan) {
                    $durasi['validasi_to_penerbitan'] = \Carbon\Carbon::parse($validasi)->diffInDays($penerbitan);
                }

                if ($izinTerbit) {
                    $durasi['penerbitan_to_izin_terbit'] = \Carbon\Carbon::parse($penerbitan)->diffInDays($izinTerbit);
                }

                if ($ditolak) {
                    // Jika ditolak, cari durasi terakhir sebelum penolakan
                    $terakhir = $penerbitan ?? $validasi ?? $verifikasi ?? $created;
                    $durasi['terakhir_to_ditolak'] = \Carbon\Carbon::parse($terakhir)->diffInDays($ditolak);
                }                

                $durasiMapping = [
                    'menunggu_verifikasi' => 'created_to_verifikasi',
                    'menunggu_validasi_lapangan' => 'verifikasi_to_validasi',
                    'proses_penerbitan_izin' => 'validasi_to_penerbitan',
                    'izin_diterbitkan' => 'penerbitan_to_izin_terbit',
                ];
            @endphp

            <div class="space-y-6">
                @foreach($steps as $key => $step)
                    @php
                        $isCurrentStatus = $key === $currentStatus;
                    @endphp

                    {{-- Tampilkan durasi --}}
                    @if(isset($durasiMapping[$key]) && isset($durasi[$durasiMapping[$key]]))
                        <x-filament::badge icon="heroicon-o-arrow-path" color="gray" style="width: 80px; margin-left: 68px; margin-top: 15px; margin-bottom: -10px;">
                            <span style="margin-left: 5px">{{ $durasi[$durasiMapping[$key]] }} hari</span>
                        </x-filament::badge>
                    @endif
                    
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-full p-2">
                                <x-filament::icon :icon="$step['icon']" class="{{ $isCurrentStatus ? 'text-primary-600 border-primary-600' : 'dark:text-white dark:border-white text-gray-500 border-gray-500' }} p-2 h-10 w-10 border rounded-full"/>
                            </div>
                        </div>
                        
                        <div class="flex-grow">
                            <div class="flex items-center">
                                <h4 class="{{ $isCurrentStatus ? 'font-medium text-primary-600' : 'dark:text-white text-gray-500' }}">
                                    {{ $step['label'] }}
                                </h4>
                                
                                @if(isset($statusDates[$key]))
                                    <span class="text-xs text-gray-500" style="margin-left: 10px">
                                        <span style="margin-right: 5px">-</span> {{ \Carbon\Carbon::parse($statusDates[$key])->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-sm text-gray-500">
                                {{ $step['description'] }}
                            </p>
                        </div>

                        
                        {{-- @if($isCurrentStatus)
                            <div class="flex-shrink-0">
                                <x-filament::badge color="primary">
                                    Saat Ini
                                </x-filament::badge>
                            </div>
                        @endif --}}
                    </div>
                @endforeach
            </div>

            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.permohonans.view', $permohonan->id) }}"
                class="w-full cursor-pointer"
                style="margin-top: 50px"
                color="primary"
            >
                Lihat Permohonan
            </x-filament::button>

        @endif
    </x-filament::section>
</x-filament-widgets::widget>