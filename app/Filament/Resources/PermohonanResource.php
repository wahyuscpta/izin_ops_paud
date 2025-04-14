<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermohonanResource\Pages;
use App\Filament\Resources\PermohonanResource\RelationManagers;
use App\Models\District;
use App\Models\Permohonan;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PermohonanResource extends Resource
{
    protected static ?string $model = Permohonan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Permohonan';

    protected static ?string $breadcrumb = 'Permohonan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Wizard::make()
                ->steps([                
                    
                    Step::make('Identitas')
                    ->schema([
                        Group::make([
                            Grid::make(2)->schema([
                                TextInput::make('nama_lembaga')
                                    ->label('Nama Lembaga')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('no_telepon_identitas')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->rule('regex:/^08[0-9]{8,11}$/')
                                    ->required()
                                    ->maxLength(20),
                            ]),

                            Textarea::make('alamat_identitas')
                                ->label('Alamat Jalan')
                                ->required()
                                ->maxLength(500),

                            Select::make('kabupaten_identitas')
                                ->label('Kabupaten/Kota')
                                ->live()
                                ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                ->required(),

                            Grid::make(2)->schema([
                                Select::make('kecamatan_identitas')
                                ->label('Kecamatan')
                                ->options(fn (Get $get): Collection => District::query()
                                    ->where('regency_id', $get('kabupaten_identitas'))
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(),

                                Select::make('desa_identitas')
                                    ->label('Desa')
                                    ->options(fn (Get $get): Collection => Village::query()
                                        ->where('district_id', $get('kecamatan_identitas'))
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required(),

                                DatePicker::make('tgl_didirikan')
                                    ->label('Didirikan Pada Tanggal')
                                    ->rule('before_or_equal:today')
                                    ->required(),

                                DatePicker::make('tgl_terdaftar')
                                    ->label('Status Penyelenggaraan Terdaftar Sejak')
                                    ->rule('before_or_equal:today')
                                    ->required(),

                                TextInput::make('no_registrasi')
                                    ->label('No Registrasi')
                                    ->required(),

                                TextInput::make('no_surat_keputusan')
                                    ->label('No Surat Keputusan')
                                    ->required(),

                                TextInput::make('rumpun_pendidikan')
                                    ->label('Rumpun Pendidikan')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('jenis_pendidikan')
                                    ->label('Jenis Pendidikan')
                                    ->options([
                                        'tk' => 'Taman Kanak-Kanak',
                                        'kb' => 'Kelompok Bermain',
                                        'tpa' => 'Tempat Penitipan Anak',
                                        'sps' => 'Satuan PAUD Sejenis',
                                        'kursus' => 'Kursus',
                                    ])
                                ->required(),
                            ]),

                            Select::make('jenis_lembaga')
                                ->label('Jenis Lembaga')
                                ->options([
                                    'induk' => 'Induk',
                                    'cabang' => 'Cabang',
                                ])
                                ->required()
                                ->live(),

                            TextInput::make('nama_lembaga_induk')
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->label('Nama Lembaga Induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->maxLength(255),

                            Textarea::make('alamat_lembaga_induk')
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->label('Alamat Lembaga Induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->maxLength(500),

                            Select::make('has_cabang')
                                ->label('Apakah Mempunyai Cabang')
                                ->options([
                                    '1' => 'Ya',
                                    '0' => 'Tidak',
                                ])
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'induk')
                                ->live(),

                            TextInput::make('jumlah_cabang')
                                ->label('Jumlah Cabang')
                                ->visible(fn (Get $get) => $get('has_cabang') === '1')
                                ->required(fn (Get $get) => $get('has_cabang') === '1')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->live(),

                            Repeater::make('cabang')
                                ->label('Data Lembaga Cabang')
                                ->schema([
                                    TextInput::make('nama_lembaga_cabang')
                                        ->label('Nama Cabang ke')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('alamat_lembaga_cabang')
                                        ->label('Alamat Cabang')
                                        ->required()
                                        ->maxLength(500),
                                ])
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'induk' && $get('has_cabang') === '1')
                                ->minItems(fn (Get $get) => (int) $get('jumlah_cabang') ?: 0)
                                ->maxItems(fn (Get $get) => (int) $get('jumlah_cabang') ?: 0)
                                ->relationship('cabangs')                                
                        ])
                        ->relationship('identitas')
                    ]),

                    Step::make('Penyelenggara')
                    ->schema([
                        Group::make([
                            Section::make('Perorangan')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_perorangan')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255),

                                    Select::make('agama_perorangan')
                                        ->label('Agama')
                                        ->options([
                                            'hindu' => 'Hindu',
                                            'islam' => 'Islam',
                                            'katolik' => 'Katolik',
                                            'kristen' => 'Kristen',
                                            'budha' => 'Budha',
                                            'konghuchu' => 'Kong Hu Chu',
                                        ])
                                        ->required(),

                                    TextInput::make('kewarganegaraan_perorangan')
                                        ->label('Kewarganegaraan')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('ktp_perorangan')
                                        ->label('No KTP')
                                        ->numeric()
                                        ->required()
                                        ->minLength(16)
                                        ->maxLength(16),

                                    DatePicker::make('tanggal_perorangan')
                                        ->label('Tanggal')
                                        ->required()
                                        ->rule('before_or_equal:today'),

                                    TextInput::make('alamat_perorangan')
                                        ->label('Alamat Lengkap Jalan')
                                        ->required()
                                        ->maxLength(500),

                                    TextInput::make('telepon_perorangan')
                                        ->label('Telepon')
                                        ->numeric()
                                        ->required()
                                        ->rule('regex:/^08[0-9]{8,11}$/')
                                        ->maxLength(13),

                                    Select::make('kabupaten_perorangan')
                                        ->label('Kabupaten/Kota')
                                        ->live()
                                        ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                        ->required(),
                                ]),

                            Section::make('Badan Hukum')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_badan')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255),

                                    Select::make('agama_badan')
                                        ->label('Agama')
                                        ->options([
                                            'hindu' => 'Hindu',
                                            'islam' => 'Islam',
                                            'katolik' => 'Katolik',
                                            'kristen' => 'Kristen',
                                            'budha' => 'Budha',
                                            'konghuchu' => 'Kong Hu Chu',
                                        ])
                                        ->required(),

                                    TextInput::make('akte_badan')
                                        ->label('Akte')
                                        ->numeric()
                                        ->required()
                                        ->maxLength(50),

                                    TextInput::make('nomor_badan')
                                        ->label('Nomor')
                                        ->numeric()
                                        ->required()
                                        ->maxLength(50),

                                    DatePicker::make('tanggal_badan')
                                        ->label('Tanggal')
                                        ->required()
                                        ->rule('before_or_equal:today'),

                                    TextInput::make('alamat_badan')
                                        ->label('Alamat Lengkap Jalan')
                                        ->required()
                                        ->maxLength(500),

                                    TextInput::make('telepon_badan')
                                        ->label('Telepon')
                                        ->numeric()
                                        ->required()
                                        ->rule('regex:/^08[0-9]{8,11}$/')
                                        ->maxLength(13),

                                    Select::make('kabupaten_badan')
                                        ->label('Kabupaten/Kota')
                                        ->live()
                                        ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                        ->required(),
                                ]),
                        ])
                        ->columns(2)
                        ->relationship('penyelenggara')
                    ]),

                    Step::make('Pengelola')
                    ->schema([
                        Group::make([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('nama_pengelola')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255),

                                    Select::make('agama_pengelola')
                                        ->label('Agama')
                                        ->options([
                                            'hindu' => 'Hindu',
                                            'islam' => 'Islam',
                                            'katolik' => 'Katolik',
                                            'kristen' => 'Kristen',
                                            'budha' => 'Budha',
                                            'konghuchu' => 'Kong Hu Chu',
                                        ])
                                        ->required(),

                                    Select::make('jenis_kelamin_pengelola')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'l' => 'Laki - Laki',
                                            'p' => 'Perempuan',
                                        ])
                                        ->required(),

                                    TextInput::make('kewarganegaraan_pengelola')
                                        ->label('Kewarganegaraan')
                                        ->required()
                                        ->maxLength(100),

                                    TextInput::make('ktp_pengelola')
                                        ->label('Nomor KTP')
                                        ->numeric()
                                        ->required()
                                        ->minLength(16)
                                        ->maxLength(16),

                                    DatePicker::make('tanggal_pengelola')
                                        ->label('Tanggal')
                                        ->required()
                                        ->rule('before_or_equal:today'),

                                    TextInput::make('telepon_pengelola')
                                        ->label('Telepon')
                                        ->numeric()
                                        ->required()
                                        ->rule('regex:/^08[0-9]{8,11}$/')
                                        ->maxLength(13),

                                    Select::make('kabupaten_pengelola')
                                        ->label('Kabupaten/Kota')
                                        ->live()
                                        ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                        ->required(),
                                ]),

                            Textarea::make('alamat_pengelola')
                                ->label('Alamat Lengkap')
                                ->required()
                                ->maxLength(500),
                        ])->relationship('pengelola')
                    ]),

                    Step::make('Peserta Didik')
                    ->schema([
                        Group::make([

                            Section::make('Warga Belajar')
                                ->columns(2)
                                ->schema([

                                    Select::make('jalur_penerimaan_tes')
                                        ->label('Penerimaan Melalui Test')
                                        ->options([
                                            'ya' => 'Ya',
                                            'tidak' => 'Tidak',
                                        ])
                                        ->required(),

                                    Select::make('tata_usaha_penerimaan')
                                        ->label('Tata Usaha Penerimaan')
                                        ->options([
                                            'ada' => 'Ada',
                                            'tidak' => 'Tidak',
                                        ])
                                        ->required(),

                                    TextInput::make('jumlah_tiap_angkatan')
                                        ->label('Jumlah Setiap Kelompok/Angkatan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->prefix('Rata - Rata')
                                        ->suffix('orang'),

                                    TextInput::make('jumlah_menyelesaikan')
                                        ->label('Yang Menyelesaikan Program Pendidikan Sampai Akhir')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required()
                                        ->prefix('Rata - Rata')
                                        ->suffix('%'),

                                ]),

                            Section::make('Keadaan Peserta Belajar Sekarang')
                                ->columns(3)
                                ->schema([

                                    TextInput::make('jumlah_sekarang_lk')
                                        ->label('Laki - Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pr = (int) $get('jumlah_sekarang_pr');
                                            $set('jumlah_sekarang_total', $state + $pr);
                                        }),

                                    TextInput::make('jumlah_sekarang_pr')
                                        ->label('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $lk = (int) $get('jumlah_sekarang_lk');
                                            $set('jumlah_sekarang_total', $state + $lk);
                                        }),

                                    TextInput::make('jumlah_sekarang_total')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->disabled()
                                        ->dehydrated(),

                                ]),

                            Section::make('Keadaan Peserta Didik Yang Telah Tamat')
                                ->columns(3)
                                ->schema([

                                    TextInput::make('jumlah_tamat_lk')
                                        ->label('Laki - Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pr = (int) $get('jumlah_tamat_pr');
                                            $set('jumlah_tamat_total', $state + $pr);
                                        }),


                                    TextInput::make('jumlah_tamat_pr')
                                        ->label('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $lk = (int) $get('jumlah_tamat_lk');
                                            $set('jumlah_tamat_total', $state + $lk);
                                        }),

                                    TextInput::make('jumlah_tamat_total')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),
                                ]),

                        ])->relationship('peserta_didik')
                    ]),

                    Step::make('Personalia')
                    ->schema([
                        Group::make([
                            Section::make('Warga Negara Indonesia')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('guru_wni_lk')
                                        ->label('Guru/Pengasuh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_pr = (int) $get('guru_wni_pr');
                                            $set('guru_wni_jumlah', (int)$state + $guru_pr);
                                        }),

                                    TextInput::make('guru_wni_pr')
                                        ->label('Guru/Pengasuh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_lk = (int) $get('guru_wni_lk');
                                            $set('guru_wni_jumlah', (int)$state + $guru_lk);
                                        }),

                                    TextInput::make('guru_wni_jumlah')
                                        ->label('Total Guru/Pengasuh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('asisten_wni_lk')
                                        ->label('Asisten Guru/Pengasuh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_pr = (int) $get('asisten_wni_pr');
                                            $set('asisten_wni_jumlah', (int)$state + $asisten_pr);
                                        }),

                                    TextInput::make('asisten_wni_pr')
                                        ->label('Asisten Guru/Pengasuh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_lk = (int) $get('asisten_wni_lk');
                                            $set('asisten_wni_jumlah', (int)$state + $asisten_lk);
                                        }),

                                    TextInput::make('asisten_wni_jumlah')
                                        ->label('Total Asisten Guru/Pengasuh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('tata_usaha_wni_lk')
                                        ->label('Tata Usaha (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $tu_pr = (int) $get('tata_usaha_wni_pr');
                                            $set('tata_usaha_wni_jumlah', (int)$state + $tu_pr);
                                        }),

                                    TextInput::make('tata_usaha_wni_pr')
                                        ->label('Tata Usaha (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $tu_lk = (int) $get('tata_usaha_wni_lk');
                                            $set('tata_usaha_wni_jumlah', (int)$state + $tu_lk);
                                        }),
                                    
                                    TextInput::make('tata_usaha_wni_jumlah')
                                        ->label('Total Tata Usaha (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('pesuruh_wni_lk')
                                        ->label('Pesuruh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pesuruh_pr = (int) $get('pesuruh_wni_pr');
                                            $set('pesuruh_wni_jumlah', (int)$state + $pesuruh_pr);
                                        }),                                                                

                                    TextInput::make('pesuruh_wni_pr')
                                        ->label('Pesuruh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pesuruh_lk = (int) $get('pesuruh_wni_lk');
                                            $set('pesuruh_wni_jumlah', (int)$state + $pesuruh_lk);
                                        }),                                    

                                    TextInput::make('pesuruh_wni_jumlah')
                                        ->label('Total Pesuruh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),
                                ]),

                            Section::make('Warga Negara Asing')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('guru_wna_lk')
                                        ->label('Guru/Pengasuh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_pr = (int) $get('guru_wna_pr');
                                            $set('guru_wna_jumlah', (int)$state + $guru_pr);
                                        }),

                                    TextInput::make('guru_wna_pr')
                                        ->label('Guru/Pengasuh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_lk = (int) $get('guru_wna_lk');
                                            $set('guru_wna_jumlah', (int)$state + $guru_lk);
                                        }),

                                    TextInput::make('guru_wna_jumlah')
                                        ->label('Total Guru/Pengasuh (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('asisten_wna_lk')
                                        ->label('Asisten Guru/Pengasuh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_pr = (int) $get('asisten_wna_pr');
                                            $set('asisten_wna_jumlah', (int)$state + $asisten_pr);
                                        }),

                                    TextInput::make('asisten_wna_pr')
                                        ->label('Asisten Guru/Pengasuh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_lk = (int) $get('asisten_wna_lk');
                                            $set('asisten_wna_jumlah', (int)$state + $asisten_lk);
                                        }),

                                    TextInput::make('asisten_wna_jumlah')
                                        ->label('Total Asisten Guru//Pengasuh (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('tata_usaha_wna_lk')
                                        ->label('Tata Usaha (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $tu_pr = (int) $get('tata_usaha_wna_pr');
                                            $set('tata_usaha_wna_jumlah', (int)$state + $tu_pr);
                                        }),

                                    TextInput::make('tata_usaha_wna_pr')
                                        ->label('Tata Usaha (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $tu_lk = (int) $get('tata_usaha_wna_lk');
                                            $set('tata_usaha_wna_jumlah', (int)$state + $tu_lk);
                                        }),
                                    
                                    TextInput::make('tata_usaha_wna_jumlah')
                                        ->label('Total Tata Usaha (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),

                                    TextInput::make('pesuruh_wna_lk')
                                        ->label('Pesuruh (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pesuruh_pr = (int) $get('pesuruh_wna_pr');
                                            $set('pesuruh_wna_jumlah', (int)$state + $pesuruh_pr);
                                        }),                                                                

                                    TextInput::make('pesuruh_wna_pr')
                                        ->label('Pesuruh (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pesuruh_lk = (int) $get('pesuruh_wna_lk');
                                            $set('pesuruh_wna_jumlah', (int)$state + $pesuruh_lk);
                                        }),                                    

                                    TextInput::make('pesuruh_wna_jumlah')
                                        ->label('Total Pesuruh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang'),
                                ]),
                        ])->relationship('personalia')
                    ]),

                    Step::make('Program Pendidikan')
                    ->schema([
                        Group::make([
                            Grid::make(2)
                                ->schema([

                                    Section::make('Bahan Pembelajaran Berdasarkan Program')
                                        ->schema([

                                            CheckboxList::make('bahan_pembelajaran')
                                                ->label('')
                                                ->options([
                                                    'depikbud' => 'Depikbud',
                                                    'instansi_lain' => 'Instansi Lain',
                                                    'lembaga_sendiri' => 'Lembaga Sendiri',
                                                    'lembaga_lain' => 'Lembaga Lain',
                                                ])
                                                ->columns(2)
                                                ->required()
                                                ->rules(['array', 'min:1'])

                                        ]),

                                    Section::make('Cara Penyampaian/Penyajian Pelajaran')
                                        ->schema([

                                            CheckboxList::make('cara_penyampaian')
                                                ->label('')
                                                ->options([
                                                    'secara_langsung' => 'Secara Langsung (Dengan Sumber Belajar Guru)',
                                                    'korespondensi' => 'Korespondensi (Tertulis)',
                                                ])
                                                ->columns(2)
                                                ->required()
                                                ->rules(['array', 'min:1'])

                                        ])

                                ])
                        ])->relationship('program_pendidikan')
                    ]),

                    Step::make('Prasarana')
                    ->schema([
                        Group::make([
                            Section::make('Ruang Belajar')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_belajar.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                    
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_belajar.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_belajar.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_belajar.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_belajar.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_belajar.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Ruang Bermain')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_bermain.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_bermain.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_bermain.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_bermain.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_bermain.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_bermain.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Ruang Pimpinan')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_pimpinan.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_pimpinan.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_pimpinan.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_pimpinan.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_pimpinan.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_pimpinan.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Ruang Sumber Belajar')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_sumber_belajar.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_sumber_belajar.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_sumber_belajar.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_sumber_belajar.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_sumber_belajar.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_sumber_belajar.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Ruang Guru')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_guru.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_guru.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_guru.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_guru.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_guru.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_guru.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Ruang Tata Usaha')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_tata_usaha.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_tata_usaha.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_tata_usaha.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_tata_usaha.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_tata_usaha.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('ruang_tata_usaha.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Kamar Mandi')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kamar_mandi.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_mandi.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_mandi.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_mandi.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_mandi.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_mandi.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),

                            Section::make('Kamar Kecil')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kamar_kecil.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_kecil.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_kecil.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_kecil.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_kecil.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah'),

                                    TextInput::make('kamar_kecil.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²'),
                                ]),
                        ])->relationship('prasarana'),
                    ]),

                    Step::make('Sarana')
                    ->schema([
                        Group::make([
                            Grid::make(2)
                                ->schema([
                                    Select::make('buku_pelajaran')
                                        ->label('Buku Pelajaran/Sesuai Kurikulum')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),

                                    Select::make('alat_permainan_edukatif')
                                        ->label('Alat Permainan Edukatif')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),

                                    Select::make('meja_kursi')
                                        ->label('Meja+Kursi/Bangku untuk Belajar')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),

                                    Select::make('papan_tulis')
                                        ->label('Papan Tulis')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),

                                    Select::make('alat_tata_usaha')
                                        ->label('Alat Perlengkapan Tata Usaha')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),

                                    Select::make('listrik')
                                        ->label('Listrik')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required(),
                                ]),

                            Select::make('air_bersih')
                                ->label('Air Bersih')
                                ->options([
                                    'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                    'cukup' => 'Cukup',
                                    'sedang' => 'Sedang',
                                    'kurang' => 'Kurang',
                                    'tidak_ada' => 'Tidak Ada',
                                ])
                                ->required(),
                        ])->relationship('sarana'),
                    ]),

                    Step::make('Lampiran')
                    ->schema([
                        Grid::make(2)
                        ->schema(
                            collect([
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
                                'rek_ke_lurah' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Lurah (Diketahui Kepala Lingkungan Setempat)',
                                'rek_dari_lurah' => 'Surat Rekomendasi dari Lurah/Kepala Desa Menunjuk Permohonan Rekomendasi dari Lembaga',
                                'rek_ke_korwil' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Korwil Disdikpora Setempat',
                                'rek_dari_korwil' => 'Surat Rekomendasi dari Korwil Disdikpora Setempat Menunjuk Permohonan Rekomendasi dari Lembaga',
                                'rip' => '(RIP) Rencana Induk Pengembangan',
                                'imb' => '(IMB) Ijin Mendirikan Bangunan',
                                'perjanjian_sewa' => 'Perjanjian Sewa Menyewa',
                                'nib' => '(NIB) No Induk Berusaha'
                            ])
                            ->chunk(2) // Membagi array menjadi kelompok berisi 2 item
                            ->map(fn ($pair) => Group::make(
                                collect($pair)->map(fn ($label, $field) => [
                                    Placeholder::make("preview_{$field}")
                                        ->label($label)
                                        ->content(function ($record) use ($field) {
                                            // Cek jika record ada dan dalam mode update/view
                                            if ($record && $record->lampiran) {
                                                $lampiran = $record->lampiran->where('lampiran_type', $field)->first();

                                                if ($lampiran && $lampiran->lampiran_path) {
                                                    $fileUrl = asset('storage/' . $lampiran->lampiran_path);
                                                    $fileName = basename($lampiran->lampiran_path);

                                                    return new HtmlString(<<<HTML
                                                        <div class="text-gray-500">
                                                            <a href="{$fileUrl}" target="_blank" class="text-sm font-semibold">{$fileName}</a>
                                                        </div>
                                                    HTML);
                                                } else {
                                                    return new HtmlString('<div><p class="text-gray-500">Belum ada dokumen yang diunggah</p></div>');
                                                }
                                            } else {
                                                return new HtmlString('<div><p class="text-gray-500">Belum ada dokumen yang diunggah</p></div>');
                                            }
                                        }),

                                    FileUpload::make($field)
                                        ->label('')
                                        ->directory('lampiran')
                                        ->disk('public')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->maxSize(2048)
                                        ->required()
                                        ->helperText('Unggah file PDF maks. 2MB'),
                                ])->flatten(1)->toArray()
                            ))->toArray()
                        ),

                        Group::make([
                            Placeholder::make('preview_pdf')
                            ->label('Surat Permohonan ijin operasional Kursus/PAUD Ditujukan Kepada Kepala Dinas Pendidikan, Kepemudaan dan Olah Raga Kabupaten Badung')
                            ->content(function ($record) {
                                // Cek jika record ada dan dalam mode update/view
                                if ($record && $record->lampiran) {
                                    $lampiran = $record->lampiran->where('lampiran_type', 'permohonan_izin')->first();

                                    if ($lampiran && $lampiran->lampiran_path) {
                                        $fileUrl = asset('storage/' . $lampiran->lampiran_path);
                                        $fileName = basename($lampiran->lampiran_path);

                                        return new HtmlString(<<<HTML
                                            <div class="text-gray-500">
                                                <a href="{$fileUrl}" target="_blank" class="text-sm font-semibold">{$fileName}</a>
                                            </div>
                                        HTML);
                                    } else {
                                        return new HtmlString('<div><p class="text-gray-500">Belum ada dokumen yang diunggah</p></div>');
                                    }
                                } else {
                                    return new HtmlString('<div><p class="text-gray-500">Belum ada dokumen yang diunggah</p></div>');
                                }
                            }),

                            FileUpload::make('permohonan_izin')
                                ->label('')
                                ->directory('lampiran')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(2048)
                                ->required()
                                ->previewable(true)
                                ->helperText('Unggah file PDF maks. 2MB'),
                        ])

                    ])

                ])
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                <x-filament::button
                        type="submit"
                        size="sm"
                        class="py-2"
                        wire:click="\$set('isKirimPermohonan', true)">
                        Kirim Permohonan
                </x-filament::button>
                BLADE)))
                ->columnSpanFull()
                ->columns(1)
                ->skippable()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Belum ada permohonan')
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermohonans::route('/'),
            'create' => Pages\CreatePermohonan::route('/create'),
            'edit' => Pages\EditPermohonan::route('/{record}/edit'),
        ];
    }
}
