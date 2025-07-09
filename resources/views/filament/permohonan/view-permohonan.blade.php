@push('styles')
<style>
    @media (min-width: 996px) and (max-width: 1366px) {
        p{
            font-size: 12px !important;
        }
    }
</style>
@endpush

<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Dokumen Lampiran -->
        <div class="md:col-span-2 shadow-md rounded-2xl">            

            <x-filament::section label="Dokumen Lampiran">

                @if ($record->lampiran->isNotEmpty() && $record->status_permohonan !== 'izin_diterbitkan')
                    <div class="mb-6">
                        <a href="{{ route('permohonan.download-all', $record->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-md text-sm" style="margin-bottom: 30px">
                            <x-filament::icon icon="heroicon-o-archive-box-arrow-down" class="h-5 w-5"/>
                            <span>Download Semua Dokumen (.zip)</span>
                        </a>
                    </div>
                @endif

                <!-- Dokumen Validasi Lapangan (jika ada) -->
                @php
                    $validasiLapangan = $record->lampiran->firstWhere('lampiran_type', 'file_validasi_lapangan');
                @endphp
                
                @if($validasiLapangan)
                    <div class="border dark:border-gray-700 rounded-lg py-4 px-6 mb-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-6 w-6 text-primary-600" />
                                <div>
                                    <p class="font-semibold">Dokumen Validasi Lapangan</p>
                                    <p class="text-sm text-gray-600">{{ basename($validasiLapangan->lampiran_path) }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-4">
                                <a href="{{ asset('storage/' . $validasiLapangan->lampiran_path) }}" target="_blank" 
                                class="px-3 py-2 bg-primary-600 text-white text-xs rounded-md flex items-center gap-1 transition hover:bg-primary-700">
                                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4"/>    
                                    <span>Lihat</span>
                                </a>
                                <a href="{{ asset('storage/' . $validasiLapangan->lampiran_path) }}" download 
                                class="px-3 py-2 text-xs rounded-md flex items-center gap-1 transition hover:bg-gray-700">
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4"/>    
                                    <span>Simpan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Formulir Permohonan -->
                <div class="border dark:border-gray-700 rounded-lg py-4 px-6 mb-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::icon icon="heroicon-o-document" class="h-6 w-6 text-primary-600" />
                            <div>
                                <p class="font-semibold">Formulir Permohonan</p>
                                <p class="text-sm text-gray-600">{{ $record->no_permohonan }}.pdf</p>
                            </div>
                        </div>
                        <div class="flex space-x-4">
                            <a href="{{ route('permohonan.export.pdf', $record->id) }}" target="_blank" 
                            class="px-3 py-2 bg-primary-600 text-white text-xs rounded-md flex items-center gap-1 transition hover:bg-primary-700">
                                <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4"/>    
                                <span>Lihat</span>
                            </a>
                            <a href="{{ route('permohonan.export.pdf', ['id' => $record->id, 'download' => true]) }}" 
                            class="px-3 py-2 text-xs rounded-md flex items-center gap-1 transition hover:bg-gray-700">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4"/>    
                                <span>Simpan</span>
                            </a>
                        </div>
                    </div>
                </div>

                @php
                    $labels = collect([
                        'ktp_ketua' => 'KTP Ketua Yayasan/Kepsek PAUD/Kursus',
                        'struktur_yayasan' => 'Struktur Lembaga Kursus/PAUD',
                        'ijasah_penyelenggara' => 'Ijasah Penyelenggara/Ketua Yayasan',
                        'ijasah_kepsek' => 'Ijasah Kepsek/Pengelola PAUD/Kursus',
                        'ijasah_pendidik' => 'Ijasah Pendidik/Guru/Instruktur LKP',
                        'sarana_prasarana' => 'Daftar Sarana dan Prasarana Lembaga',
                        'kurikulum' => 'Kurikulum Kursus/PAUD',
                        'tata_tertib' => 'Tata Tertib Kursus/PAUD',
                        'peta_lokasi' => 'Peta Lokasi Kursus/PAUD',
                        'daftar_peserta' => 'Daftar Peserta Didik Kursus/PAUD',
                        'daftar_guru' => 'Daftar Guru/Pendidik',
                        'akte_notaris' => 'Akte Notaris Yayasan dan Kemenhumham',
                        'rek_ke_lurah' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Lurah',
                        'rek_dari_lurah' => 'Surat Rekomendasi dari Lurah/Kepala Desa',
                        'rek_ke_korwil' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Korwil',
                        'rek_dari_korwil' => 'Surat Rekomendasi dari Korwil Disdikpora',
                        'permohonan_izin' => 'Surat Permohonan Izin Operasional Kursus/PAUD',
                        'rip' => '(RIP) Rencana Induk Pengembangan',
                        'imb' => '(IMB) Ijin Mendirikan Bangunan',
                        'perjanjian_sewa' => 'Perjanjian Sewa Menyewa',
                        'nib' => '(NIB) No Induk Berusaha',
                        'file_validasi_lapangan' => 'Dokumen Validasi Lapangan',
                    ]);
                    
                    $categories = [
                        'Identitas & Struktur' => ['ktp_ketua', 'struktur_yayasan', 'akte_notaris', 'nib'],
                        'Dokumen Akademik' => ['ijasah_penyelenggara', 'ijasah_kepsek', 'ijasah_pendidik', 'kurikulum', 'tata_tertib', 'daftar_peserta', 'daftar_guru'],
                        'Infrastruktur' => ['sarana_prasarana', 'peta_lokasi', 'imb', 'perjanjian_sewa', 'rip'],
                        'Surat Rekomendasi' => ['rek_ke_lurah', 'rek_dari_lurah', 'rek_ke_korwil', 'rek_dari_korwil', 'permohonan_izin'],
                    ];
                    
                    $categorizedDocs = [];
                    foreach ($categories as $category => $types) {
                        $docs = $record->lampiran->filter(function($item) use ($types) {
                            return in_array($item->lampiran_type, $types);
                        });
                        if ($docs->isNotEmpty()) {
                            $categorizedDocs[$category] = $docs;
                        }
                    }
                @endphp

                <!-- Dokumen Lampiran -->
                @if (!empty($categorizedDocs))
                    <div class="space-y-6 mt-4" style="margin-top: 30px">
                        @foreach ($categorizedDocs as $category => $documents)
                            <x-filament::fieldset :label="$category">
                                @foreach ($documents as $lampiran)
                                    <div class="flex justify-between items-center py-3">
                                        <div class="flex items-center gap-3">                                            
                                            <x-filament::icon icon='heroicon-o-document-text' class="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p class="text-sm font-medium">{{ $labels->get($lampiran->lampiran_type, 'Lampiran') }}</p>
                                                <p class="text-xs text-gray-500">{{ basename($lampiran->lampiran_path) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <!-- Tombol Lihat -->
                                            <a href="{{ route('view-document', $lampiran->id) }}" target="_blank" 
                                            class="px-3 py-2 bg-primary-600 text-white rounded-lg flex items-center gap-1.5 transition text-xs">
                                                <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4"/>
                                                <span>Lihat</span>
                                            </a>
                                            <!-- Tombol Download -->
                                            <a href="{{ asset('storage/' . $lampiran->lampiran_path) }}" download
                                            class="px-3 py-2 rounded-lg flex items-center gap-1.5 transition text-xs">
                                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4"/>
                                                <span>Simpan</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </x-filament::fieldset>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <x-filament::icon icon="heroicon-o-document" class="h-7 w-7 my-4 text-gray-400" />
                        <p class="mt-4 text-gray-500">Tidak ada lampiran yang tersedia.</p>
                    </div>
                @endif
            </x-filament::section>

        </div>
        
        <!-- Status Card -->
        <div class="md:col-span-1">
            @livewire(\App\Filament\Resources\PermohonanResource\Widgets\StatusCard::class, ['record' => $record])
        </div>
    </div>
</x-filament::page>