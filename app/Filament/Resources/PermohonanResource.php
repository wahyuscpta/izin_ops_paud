<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermohonanResource\Pages;
use App\Filament\Resources\PermohonanResource\RelationManagers;
use App\Models\District;
use App\Models\Permohonan;
use App\Models\Regency;
use App\Models\Village;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
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
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class PermohonanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Permohonan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Permohonan';

    public static function getNavigationGroup(): ?string
    {
        return Auth::user()?->hasRole('pemohon')
            ? 'Permohonan Saya'
            : 'Manajemen Data';
    }

    protected static ?string $breadcrumb = 'Permohonan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'verifikasi',
            'validasi',
            'proses_izin'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('wizard_step')->dehydrated(false),
                
                Wizard::make()
                ->steps([                
                    
                    Step::make('Identitas')
                    ->schema([

                        TextInput::make('no_permohonan')
                            ->label('Nomor Surat Permohonan')
                            ->placeholder('Contoh: 0017/X/YPKB/2022')
                            ->rule('regex:/^[0-9]{4}\/[A-Z]{1,5}\/[A-Z]{1,10}\/[0-9]{4}$/')
                            ->required()
                            ->maxLength(50),
                            
                        Group::make([
                            Grid::make(2)->schema([
                                TextInput::make('nama_lembaga')
                                    ->label('Nama Lembaga')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('no_telepon_identitas')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
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
                                    ->placeholder('Contoh: Pendidikan Anak Usia Dini (PAUD)')
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
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set('wizard_step', $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set('wizard_step', $wizard->getCurrentStepIndex()+1);
                    }),

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

                                TextInput::make('telepon_badan')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                    ->required()
                                    ->maxLength(20),

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
                                    ->required()
                                    ->maxLength(50),

                                TextInput::make('nomor_badan')
                                    ->label('Nomor')
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

                                TextInput::make('telepon_perorangan')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                    ->required()
                                    ->maxLength(20),

                                Select::make('kabupaten_badan')
                                    ->label('Kabupaten/Kota')
                                    ->live()
                                    ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                    ->required(),
                            ])

                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 1;
                        })                        
                        ->relationship('penyelenggara')
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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
                                        ->label('No Telepon')
                                        ->tel()
                                        ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                        ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                        ->required()
                                        ->maxLength(20),

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
                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 2;
                        })
                        ->relationship('pengelola')
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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

                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 3;
                        })
                        ->relationship('peserta_didik')
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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
                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 4;
                        })
                        ->relationship('personalia')
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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
                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 5;
                        })
                        ->relationship('program_pendidikan')
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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
                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 6;
                        })
                        ->relationship('prasarana'),
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

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
                        ])
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 7;
                        })
                        ->relationship('sarana'),
                    ])
                    ->beforeValidation(function (string $context, Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex());
                    })
                    ->afterValidation(function (Set $set, $state, $livewire) {
                        $wizard = $livewire->form->getComponent(
                            fn (Component $component): bool => $component instanceof Wizard
                        );
                        $set("wizard_step", $wizard->getCurrentStepIndex()+1);
                    }),

                    Step::make('Lampiran')
                    ->schema([
                        Grid::make(2)
                        ->schema(
                            collect([
                                'ktp_ketua' => 'KTP Ketua Yayasan/Kepsek PAUD/Kursus',
                                'ijasah_penyelenggara' => 'Ijasah Penyelenggara/Ketua Yayasan',
                                'struktur_yayasan' => 'Struktur Lembaga Kursus/PAUD',
                                'ijasah_kepsek' => 'Ijasah Kepsek/Pengelola PAUD/Kursus',
                                'ijasah_pendidik' => 'Ijasah Pendidik/Guru/Instruktur LKP',
                                'kurikulum' => 'Kurikulum Kursus/PAUD',
                                'sarana_prasarana' => 'Daftar Sarana dan Prasarana Lembaga',
                                'tata_tertib' => 'Tata Tertib Kursus/PAUD',
                                'peta_lokasi' => 'Peta Lokasi Kursus/PAUD',
                                'daftar_guru' => 'Daftar Guru/Pendidik',
                                'daftar_peserta' => 'Daftar Peserta Didik Kursus/PAUD',
                                'akte_notaris' => 'Akte Notaris Yayasan dan Kemenhumham',
                                'rek_ke_lurah' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Lurah (Diketahui Kepala Lingkungan Setempat)',
                                'rek_ke_korwil' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Korwil Disdikpora Setempat',
                                'rek_dari_lurah' => 'Surat Rekomendasi dari Lurah/Kepala Desa Menunjuk Permohonan Rekomendasi dari Lembaga',
                                'rek_dari_korwil' => 'Surat Rekomendasi dari Korwil Disdikpora Setempat',
                                'rip' => '(RIP) Rencana Induk Pengembangan',
                                'perjanjian_sewa' => 'Perjanjian Sewa Menyewa',
                                'imb' => '(IMB) Ijin Mendirikan Bangunan',
                                'nib' => '(NIB) No Induk Berusaha'
                            ])
                            ->chunk(2)
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
                                        ->required(function ($record) use ($field) {
                                            // Hanya required jika tidak ada file yang sudah diunggah sebelumnya
                                            if ($record && $record->lampiran) {
                                                $lampiran = $record->lampiran->where('lampiran_type', $field)->first();
                                                return !($lampiran && $lampiran->lampiran_path);
                                            }
                                            return true; // Required untuk record baru
                                        })
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
                                ->required(function ($record) {
                                    // Hanya required jika tidak ada file yang sudah diunggah sebelumnya
                                    if ($record && $record->lampiran) {
                                        $lampiran = $record->lampiran->where('lampiran_type', 'permohonan_izin')->first();
                                        return !($lampiran && $lampiran->lampiran_path);
                                    }
                                    return true; // Required untuk record baru
                                })
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
                ->skippable()
                ->columnSpanFull()
                ->columns(1)
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

         if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery()->whereIn('status_permohonan', ['menunggu_verifikasi', 'menunggu_validasi_lapangan', 'proses_penerbitan_izin', 'izin_diterbitkan', 'ditolak']);
        }

        if ($user->hasRole('kepala_dinas')) {
            return parent::getEloquentQuery()->whereIn('status_permohonan', ['proses_penerbitan_izin', 'izin_diterbitkan']);
        }

        return parent::getEloquentQuery()->where('user_id', $user->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Belum ada permohonan')
            ->columns([
                TextColumn::make('no_permohonan')
                ->label('No Permohonan')
                ->sortable()
                ->searchable(),
                
                TextColumn::make('user.name')
                ->label('Nama Pemohon')
                ->sortable()
                ->visible(fn () => Filament::auth()->user()->hasRole('admin'))
                ->searchable(),

                TextColumn::make('identitas.nama_lembaga')
                ->label('Nama Lembaga')
                ->sortable()
                ->searchable(),

                TextColumn::make('tgl_permohonan')
                ->label('Tanggal Diajukan')
                ->date()
                ->sortable(),

                TextColumn::make('tgl_status_terakhir')
                ->label('Tanggal Status Terakhir')
                ->date()
                ->sortable(),

                TextColumn::make('status_permohonan')
                ->label('Status Permohonan')
                ->badge()
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'menunggu_verifikasi' => 'warning',
                    'menunggu_validasi_lapangan' => 'success',
                    'proses_penerbitan_izin' => 'success',
                    'izin_diterbitkan' => 'primary',
                    'permohonan_ditolak' => 'danger',
                })
            ])
            ->filters([
                Filter::make('tgl_permohonan')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),

                SelectFilter::make('identitas.jenis_lembaga')
                    ->label('Jenis Lembaga')
                    ->options([
                        'paud' => 'PAUD',
                        'tk' => 'TK',
                        'kb' => 'Kelompok Bermain',
                        'tpa' => 'Tempat Penitipan Anak',
                    ])
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()
                ->visible(fn (Model $record) => in_array($record->status_permohonan, ['draft', 'ditolak'])),

                DeleteAction::make()
                ->visible(fn (Model $record) => in_array($record->status_permohonan, ['draft', 'ditolak']))
                ->after(function (Model $record) {
                    foreach ($record->lampiran as $lampiran) {
                        Storage::disk('public')->delete($lampiran->lampiran_path);
                    }
                    $record->lampiran()->delete();
                })
                ->successNotification(
                    Notification::make()
                    ->title('Berhasil Dihapus')
                    ->body('Data permohonan telah dihapus dari sistem')
                    ->success()
                )
                ->failureNotification(
                    Notification::make()
                    ->title('Gagal Dihapus')
                    ->body('Data gagal dihapus. Silakan coba lagi.')
                    ->danger()
                ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->title('Berhasil Dihapus')
                            ->body('Semua data permohonan terpilih telah dihapus')
                            ->success()
                    )
                    ->failureNotification(
                        Notification::make()
                            ->title('Gagal Dihapus')
                            ->body('Beberapa data permohonan gagal dihapus')
                            ->danger()
                    )
                ])
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
            'view' => Pages\ViewPermohonan::route('/{record}'),
            'edit' => Pages\EditPermohonan::route('/{record}/edit'),
        ];
    }
}
