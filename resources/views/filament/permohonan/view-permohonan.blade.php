<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Dokumen Lampiran -->
        <div class="md:col-span-2 shadow-md rounded-2xl">            

            <x-filament::section label="Dokumen Lampiran">

                <x-filament::fieldset label="Formulir Permohonan" class="space-y-0 mt-4 mb-4">
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-700 font-medium">{{ $record->no_permohonan }}.pdf</span>
                        <div class="flex space-x-2">
                            <!-- Tombol View (open in tab) -->
                            <a href="{{ route('permohonan.export.pdf', $record->id) }}"
                            target="_blank" class="px-3 py-1 bg-primary text-white rounded-lg flex items-center gap-1 hover:bg-blue-600">
                                <x-filament::icon icon="heroicon-o-eye"/>
                                <span>Lihat</span>
                            </a>
                            <!-- Tombol Download -->
                            <a href="{{ route('permohonan.export.pdf', ['id' => $record->id, 'download' => true]) }}" class="px-3 py-1 bg-green-500 text-white rounded-lg flex items-center gap-1 hover:bg-green-600">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray"/>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                </x-filament::fieldset>


                @if ($record->lampiran->isNotEmpty())
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
                            'rip' => '(RIP) Rencana Induk Pengembangan',
                            'imb' => '(IMB) Ijin Mendirikan Bangunan',
                            'perjanjian_sewa' => 'Perjanjian Sewa Menyewa',
                            'nib' => '(NIB) No Induk Berusaha',
                        ]);
                    @endphp

                    @foreach ($record->lampiran as $lampiran)
                        <x-filament::fieldset :label="$labels->get($lampiran->lampiran_type, 'Lampiran tidak dikenal')" class="space-y-0">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-700 font-medium">{{ basename($lampiran->lampiran_path) }}</span>
                                <div class="flex space-x-2">
                                    <!-- Tombol Lihat -->
                                    <a href="{{ asset('storage/' . $lampiran->lampiran_path) }}" target="_blank"
                                       class="px-3 py-1 bg-blue-500 text-white rounded-lg flex items-center gap-1 hover:bg-blue-600">
                                        <x-filament::icon icon="heroicon-o-eye"/>
                                        <span>Lihat</span>
                                    </a>
                                    <!-- Tombol Download -->
                                    <a href="#" class="px-3 py-1 bg-green-500 text-white rounded-lg flex items-center gap-1 hover:bg-green-600">
                                        <x-filament::icon icon="heroicon-o-arrow-down-tray"/>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </div>
                        </x-filament::fieldset>
                    @endforeach
                @else
                    <p class="text-gray-500">Tidak ada lampiran yang tersedia.</p>
                @endif
            </x-filament::section>
        </div>
        
        <!-- Status Card -->
        <div class="md:col-span-1">
            @livewire(\App\Filament\Resources\PermohonanResource\Widgets\StatusCard::class, ['record' => $record])
        </div>
    </div>
</x-filament::page>