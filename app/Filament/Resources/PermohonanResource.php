<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
        $user = Auth::user();
    
        if ($user->hasRole('pemohon')) {
            return static::getModel()::where('user_id', $user->id)->count();
        }
        
        return static::getModel()::where('status_permohonan', '!=', 'draft')->count();
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
                            ->placeholder('Contoh: 0001/ADM/PAUD/2025')
                            ->required()
                            ->maxLength(50)
                            ->validationMessages([
                                'required' => 'Nomor surat permohonan wajib diisi.',
                                'regex' => 'Format nomor surat harus seperti: 0001/ADM/PAUD/2025.',
                                'max' => 'Maksimal 50 karakter.',
                            ]),
                            
                        Group::make([
                            Grid::make(2)->schema([
                                TextInput::make('nama_lembaga')
                                    ->label('Nama Lembaga')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Nama lembaga tidak boleh kosong.',
                                        'max' => 'Nama lembaga terlalu panjang.',
                                    ]),

                                TextInput::make('no_telepon_identitas')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                    ->required()
                                    ->maxLength(20)
                                    ->validationMessages([
                                        'required' => 'Nomor telepon wajib diisi.',
                                        'regex' => 'Format nomor telepon tidak valid.',
                                        'max' => 'Nomor telepon maksimal 20 karakter.',
                                    ])
                            ]),

                            Textarea::make('alamat_identitas')
                                ->label('Alamat Lengkap')
                                ->required()
                                ->maxLength(500)
                                ->validationMessages([
                                    'required' => 'Alamat jalan wajib diisi.',
                                    'max' => 'Alamat jalan maksimal 500 karakter.',
                                ]),

                            Select::make('kabupaten_identitas')
                                ->label('Kabupaten/Kota')
                                ->live()
                                ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                ->required()
                                ->validationMessages([
                                    'required' => 'Kabupaten/Kota wajib dipilih.',
                                ]),

                            Grid::make(2)->schema([
                                Select::make('kecamatan_identitas')
                                ->label('Kecamatan')
                                ->options(fn (Get $get): Collection => District::query()
                                    ->where('regency_id', $get('kabupaten_identitas'))
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->validationMessages([
                                    'required' => 'Kecamatan wajib dipilih.',
                                ]),

                                Select::make('desa_identitas')
                                    ->label('Desa')
                                    ->options(fn (Get $get): Collection => Village::query()
                                        ->where('district_id', $get('kecamatan_identitas'))
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Desa wajib dipilih.',
                                    ]),

                                DatePicker::make('tgl_didirikan')
                                    ->label('Didirikan Pada Tanggal')
                                    ->rule('before_or_equal:today')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Tanggal pendirian wajib diisi.',
                                        'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                                    ]),

                                DatePicker::make('tgl_terdaftar')
                                    ->label('Status Penyelenggaraan Terdaftar Sejak')
                                    ->rule('before_or_equal:today')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Tanggal terdaftar wajib diisi.',
                                        'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                                    ]),

                                TextInput::make('no_registrasi')
                                    ->label('No Registrasi')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Nomor registrasi wajib diisi.',
                                    ]),

                                TextInput::make('no_surat_keputusan')
                                    ->label('No Surat Keputusan')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Nomor surat keputusan wajib diisi.',
                                    ]),

                                TextInput::make('rumpun_pendidikan')
                                    ->placeholder('Contoh: Pendidikan Anak Usia Dini (PAUD)')
                                    ->label('Rumpun Pendidikan')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Rumpun pendidikan wajib diisi.',
                                        'max' => 'Rumpun pendidikan maksimal 255 karakter.',
                                    ]),

                                Select::make('jenis_pendidikan')
                                    ->label('Jenis Pendidikan')
                                    ->options([
                                        'tk' => 'Taman Kanak-Kanak',
                                        'kb' => 'Kelompok Bermain',
                                        'tpa' => 'Tempat Penitipan Anak',
                                        'sps' => 'Satuan PAUD Sejenis',
                                        'kursus' => 'Kursus',
                                    ])
                                ->required()
                                ->validationMessages([
                                    'required' => 'Jenis pendidikan wajib dipilih.',
                                ]),
                            ]),

                            Select::make('jenis_lembaga')
                                ->label('Jenis Lembaga')
                                ->options([
                                    'induk' => 'Induk',
                                    'cabang' => 'Cabang',
                                ])
                                ->required()
                                ->live()
                                ->validationMessages([
                                    'required' => 'Jenis lembaga wajib dipilih.',
                                ]),

                            TextInput::make('nama_lembaga_induk')
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->label('Nama Lembaga Induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->maxLength(255)
                                ->validationMessages([
                                    'required' => 'Nama lembaga induk wajib diisi.',
                                    'max' => 'Nama lembaga induk maksimal 255 karakter.',
                                ]),

                            Textarea::make('alamat_lembaga_induk')
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->label('Alamat Lembaga Induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'cabang')
                                ->maxLength(500)
                                ->validationMessages([
                                    'required' => 'Alamat lembaga induk wajib diisi.',
                                    'max' => 'Alamat lembaga induk maksimal 500 karakter.',
                                ]),

                            Select::make('has_cabang')
                                ->label('Apakah Mempunyai Cabang')
                                ->options([
                                    '1' => 'Ya',
                                    '0' => 'Tidak',
                                ])
                                ->visible(fn (Get $get) => $get('jenis_lembaga') === 'induk')
                                ->required(fn (Get $get) => $get('jenis_lembaga') === 'induk')
                                ->live()
                                ->validationMessages([
                                    'required' => 'Alamat lembaga induk wajib diisi.',
                                    'max' => 'Alamat lembaga induk maksimal 500 karakter.',
                                ]),

                            TextInput::make('jumlah_cabang')
                                ->label('Jumlah Cabang')
                                ->visible(fn (Get $get) => $get('has_cabang') === '1')
                                ->required(fn (Get $get) => $get('has_cabang') === '1')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->live()
                                ->validationMessages([
                                    'required' => 'Jumlah cabang wajib diisi.',
                                    'numeric' => 'Jumlah cabang harus berupa angka.',
                                    'min' => 'Jumlah cabang minimal 1.',
                                    'max' => 'Jumlah cabang maksimal 100.',
                                ])
                                ->extraInputAttributes([
                                    'min' => '0',
                                    'pattern' => '[0-9]*',
                                    'inputmode' => 'numeric'
                                ]),

                            Repeater::make('cabang')
                                ->label('Data Lembaga Cabang')
                                ->schema([
                                    TextInput::make('nama_lembaga_cabang')
                                        ->label('Nama Cabang ke')
                                        ->required()
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'required' => 'Nama cabang wajib diisi.',
                                            'max' => 'Nama cabang maksimal 255 karakter.',
                                        ]),

                                    TextInput::make('alamat_lembaga_cabang')
                                        ->label('Alamat Cabang')
                                        ->required()
                                        ->maxLength(500)
                                        ->validationMessages([
                                            'required' => 'Alamat cabang wajib diisi.',
                                            'max' => 'Alamat cabang maksimal 500 karakter.',
                                        ]),
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
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Nama lengkap wajib diisi.',
                                        'max' => 'Maksimal 255 karakter.',
                                    ]),

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
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Agama wajib dipilih.',
                                    ]),

                                TextInput::make('kewarganegaraan_perorangan')
                                    ->label('Kewarganegaraan')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Kewarganegaraan wajib diisi.',
                                        'max' => 'Maksimal 255 karakter.',
                                    ]),

                                TextInput::make('ktp_perorangan')
                                    ->label('No KTP')
                                    ->numeric()
                                    ->required()
                                    ->minLength(16)
                                    ->maxLength(16)
                                    ->validationMessages([
                                        'required' => 'Nomor KTP wajib diisi.',
                                        'numeric' => 'Nomor KTP harus berupa angka.',
                                        'min' => 'Nomor KTP harus 16 digit.',
                                        'max' => 'Nomor KTP harus 16 digit.',
                                    ])
                                    ->extraInputAttributes([
                                        'min' => '0',
                                        'pattern' => '[0-9]*',
                                        'inputmode' => 'numeric'
                                    ]),

                                DatePicker::make('tanggal_perorangan')
                                    ->label('Tanggal')
                                    ->required()
                                    ->rule('before_or_equal:today')
                                    ->validationMessages([
                                        'required' => 'Tanggal wajib diisi.',
                                        'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                                    ]),                                

                                TextInput::make('alamat_perorangan')
                                    ->label('Alamat Lengkap')
                                    ->required()
                                    ->maxLength(500)
                                    ->validationMessages([
                                        'required' => 'Alamat wajib diisi.',
                                        'max' => 'Maksimal 500 karakter.',
                                    ]),                                

                                TextInput::make('telepon_perorangan')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                    ->required()
                                    ->maxLength(20)
                                    ->validationMessages([
                                        'required' => 'Nomor telepon wajib diisi.',
                                        'regex' => 'Format nomor telepon tidak valid.',
                                        'max' => 'Maksimal 20 karakter.',
                                    ]),

                                Select::make('kabupaten_perorangan')
                                    ->label('Kabupaten/Kota')
                                    ->live()
                                    ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Kabupaten/Kota wajib dipilih.',
                                    ]),
                            ]),

                            Section::make('Badan Hukum')
                            ->columns(2)
                            ->schema([
                                TextInput::make('nama_badan')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Nama lengkap wajib diisi.',
                                        'max' => 'Maksimal 255 karakter.',
                                    ]),

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
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Agama wajib dipilih.',
                                    ]),

                                TextInput::make('akte_badan')
                                    ->label('Akte')
                                    ->required()
                                    ->maxLength(50)
                                    ->validationMessages([
                                        'required' => 'Akte wajib diisi.',
                                        'max' => 'Maksimal 50 karakter.',
                                    ]),

                                TextInput::make('nomor_badan')
                                    ->label('Nomor')
                                    ->required()
                                    ->maxLength(50)
                                    ->validationMessages([
                                        'required' => 'Nomor wajib diisi.',
                                        'max' => 'Maksimal 50 karakter.',
                                    ]),

                                DatePicker::make('tanggal_badan')
                                    ->label('Tanggal')
                                    ->required()
                                    ->rule('before_or_equal:today')
                                    ->validationMessages([
                                        'required' => 'Tanggal wajib diisi.',
                                        'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                                    ]),

                                TextInput::make('alamat_badan')
                                    ->label('Alamat Lengkap')
                                    ->required()
                                    ->maxLength(500)
                                    ->validationMessages([
                                        'required' => 'Alamat wajib diisi.',
                                        'max' => 'Maksimal 500 karakter.',
                                    ]),

                                TextInput::make('telepon_badan')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                    ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                    ->required()
                                    ->maxLength(20)
                                    ->validationMessages([
                                        'required' => 'Nomor telepon wajib diisi.',
                                        'regex' => 'Format nomor telepon tidak valid.',
                                        'max' => 'Maksimal 20 karakter.',
                                    ]),

                                Select::make('kabupaten_badan')
                                    ->label('Kabupaten/Kota')
                                    ->live()
                                    ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Kabupaten/Kota wajib dipilih.',
                                    ]),
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
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'required' => 'Nama lengkap wajib diisi.',
                                            'max' => 'Maksimal 255 karakter.',
                                        ]),

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
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Agama wajib dipilih.',
                                        ]),

                                    Select::make('jenis_kelamin_pengelola')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'l' => 'Laki - Laki',
                                            'p' => 'Perempuan',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Jenis kelamin wajib dipilih.',
                                        ]),

                                    TextInput::make('kewarganegaraan_pengelola')
                                        ->label('Kewarganegaraan')
                                        ->required()
                                        ->maxLength(100)
                                        ->validationMessages([
                                            'required' => 'Kewarganegaraan wajib diisi.',
                                            'max' => 'Maksimal 100 karakter.',
                                        ]),

                                    TextInput::make('ktp_pengelola')
                                        ->label('Nomor KTP')
                                        ->numeric()
                                        ->required()
                                        ->minLength(16)
                                        ->maxLength(16)
                                        ->validationMessages([
                                            'required' => 'Nomor KTP wajib diisi.',
                                            'numeric' => 'Nomor KTP harus berupa angka.',
                                            'min' => 'Nomor KTP harus 16 digit.',
                                            'max' => 'Nomor KTP harus 16 digit.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    DatePicker::make('tanggal_pengelola')
                                        ->label('Tanggal')
                                        ->required()
                                        ->rule('before_or_equal:today')
                                        ->validationMessages([
                                            'required' => 'Tanggal wajib diisi.',
                                            'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                                        ]),

                                    TextInput::make('telepon_pengelola')
                                        ->label('No Telepon')
                                        ->tel()
                                        ->placeholder('Contoh: 081234567890, (0361) 123456, 0361-123456')
                                        ->rule('regex:/^(\+62|62)?[\s-]?(\(0[0-9]{2,3}\)[\s-]?|0[0-9]{2,3}[\s-]?)[0-9]{6,8}$|^(\+62|62|0)8[1-9][0-9]{6,9}$/')
                                        ->required()
                                        ->maxLength(20)
                                        ->validationMessages([
                                            'required' => 'Nomor telepon wajib diisi.',
                                            'regex' => 'Format nomor telepon tidak valid.',
                                            'max' => 'Maksimal 20 karakter.',
                                        ]),

                                    Select::make('kabupaten_pengelola')
                                        ->label('Kabupaten/Kota')
                                        ->live()
                                        ->options(Regency::where('province_id', 51)->pluck('name', 'id'))
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Kabupaten/Kota wajib dipilih.',
                                        ]),
                                ]),

                            Textarea::make('alamat_pengelola')
                                ->label('Alamat Lengkap')
                                ->required()
                                ->maxLength(500)
                                ->validationMessages([
                                    'required' => 'Alamat wajib diisi.',
                                    'max' => 'Maksimal 500 karakter.',
                                ]),
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
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Pilih apakah penerimaan melalui test.',
                                        ]),

                                    Select::make('tata_usaha_penerimaan')
                                        ->label('Tata Usaha Penerimaan')
                                        ->options([
                                            'ada' => 'Ada',
                                            'tidak' => 'Tidak',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Pilih apakah ada tata usaha penerimaan.',
                                        ]),

                                    TextInput::make('jumlah_tiap_angkatan')
                                        ->label('Jumlah Setiap Kelompok/Angkatan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->prefix('Rata - Rata')
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Jumlah peserta tiap angkatan wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('jumlah_menyelesaikan')
                                        ->label('Yang Menyelesaikan Program Pendidikan Sampai Akhir')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required()
                                        ->prefix('Rata - Rata')
                                        ->suffix('%')
                                        ->validationMessages([
                                            'required' => 'Jumlah yang menyelesaikan program wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0%.',
                                            'max' => 'Jumlah tidak boleh lebih dari 100%.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah laki-laki sekarang wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah perempuan sekarang wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('jumlah_sekarang_total')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->disabled()
                                        ->dehydrated()
                                        ->validationMessages([
                                            'required' => 'Jumlah total peserta sekarang wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah laki-laki tamat wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),                                       

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah perempuan tamat wajib diisi.',
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('jumlah_tamat_total')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'numeric' => 'Jumlah harus berupa angka.',
                                            'min' => 'Jumlah tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
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
                                        ->label('Guru (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_pr = (int) $get('guru_wni_pr');
                                            $set('guru_wni_jumlah', (int)$state + $guru_pr);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Guru laki-laki WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('guru_wni_pr')
                                        ->label('Guru (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_lk = (int) $get('guru_wni_lk');
                                            $set('guru_wni_jumlah', (int)$state + $guru_lk);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Guru perempuan WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('guru_wni_jumlah')
                                        ->label('Total Guru (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Guru WNI harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wni_lk')
                                        ->label('Asisten Guru (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_pr = (int) $get('asisten_wni_pr');
                                            $set('asisten_wni_jumlah', (int)$state + $asisten_pr);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Asisten Guru laki-laki WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wni_pr')
                                        ->label('Asisten Guru (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_lk = (int) $get('asisten_wni_lk');
                                            $set('asisten_wni_jumlah', (int)$state + $asisten_lk);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Asisten Guru perempuan WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wni_jumlah')
                                        ->label('Total Asisten Guru (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Asisten Guru WNI harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha Laki-Laki WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha Perempuan WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                    
                                    TextInput::make('tata_usaha_wni_jumlah')
                                        ->label('Total Tata Usaha (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha WNI harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh Laki-Laki WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),                                                                

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh Perempuan WNI harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),                                    

                                    TextInput::make('pesuruh_wni_jumlah')
                                        ->label('Total Pesuruh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh WNI harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),

                            Section::make('Warga Negara Asing')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('guru_wna_lk')
                                        ->label('Guru (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_pr = (int) $get('guru_wna_pr');
                                            $set('guru_wna_jumlah', (int)$state + $guru_pr);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Guru laki-laki WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('guru_wna_pr')
                                        ->label('Guru (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $guru_lk = (int) $get('guru_wna_lk');
                                            $set('guru_wna_jumlah', (int)$state + $guru_lk);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Guru perempuan WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('guru_wna_jumlah')
                                        ->label('Total Guru (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Guru WNA harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wna_lk')
                                        ->label('Asisten Guru (Laki-Laki)')
                                        ->prefix('Laki-Laki')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_pr = (int) $get('asisten_wna_pr');
                                            $set('asisten_wna_jumlah', (int)$state + $asisten_pr);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Asisten Guru laki-laki WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wna_pr')
                                        ->label('Asisten Guru (Perempuan)')
                                        ->prefix('Perempuan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('orang')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $asisten_lk = (int) $get('asisten_wna_lk');
                                            $set('asisten_wna_jumlah', (int)$state + $asisten_lk);
                                        })
                                        ->validationMessages([
                                            'required' => 'Jumlah Asisten Guru perempuan WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

                                    TextInput::make('asisten_wna_jumlah')
                                        ->label('Total Asisten Guru/ (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Asisten Guru WNA harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha laki-laki WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha perempuan WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                    
                                    TextInput::make('tata_usaha_wna_jumlah')
                                        ->label('Total Tata Usaha (WNA)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Tata Usaha WNA harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh laki-laki WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),                                                                

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
                                        })
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh perempuan WNA harus diisi.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ]),                                                                                              
                                    TextInput::make('pesuruh_wna_jumlah')
                                        ->label('Total Pesuruh (WNI)')
                                        ->prefix('Jumlah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->suffix('orang')
                                        ->validationMessages([
                                            'required' => 'Total Pesuruh WNA harus dihitung.',
                                            'numeric' => 'Input harus berupa angka.',
                                            'min' => 'Nilai minimal adalah 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),            
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
                                                ->validationMessages([
                                                    'required' => 'Bahan pembelajaran wajib dipilih.',
                                                    'array' => 'Format bahan pembelajaran tidak valid.',
                                                    'min' => 'Pilih minimal satu bahan pembelajaran.',
                                                ]),
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
                                                ->validationMessages([
                                                    'required' => 'Cara penyampaian wajib dipilih.',
                                                    'array' => 'Format cara penyampaian tidak valid.',
                                                    'min' => 'Pilih minimal satu cara penyampaian.',
                                                ]),
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
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang belajar milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang belajar milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang belajar milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_belajar.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang belajar kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang belajar kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang belajar kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_belajar.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang belajar sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang belajar sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang belajar sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_belajar.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang belajar pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang belajar pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang belajar pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_belajar.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang belajar beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang belajar beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang belajar beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_belajar.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang belajar wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang belajar harus berupa angka.',
                                            'min' => 'Jumlah luas ruang belajar tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Ruang Bermain')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_bermain.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang bermain milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang bermain milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang bermain milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_bermain.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang bermain kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang bermain kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang bermain kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_bermain.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang bermain sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang bermain sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang bermain sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_bermain.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang bermain pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang bermain pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang bermain pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_bermain.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang bermain beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang bermain beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang bermain beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_bermain.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang bermain wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang bermain harus berupa angka.',
                                            'min' => 'Jumlah luas ruang bermain tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Ruang Pimpinan')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_pimpinan.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang pimpinan milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang pimpinan milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang pimpinan milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_pimpinan.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang pimpinan kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang pimpinan kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang pimpinan kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_pimpinan.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang pimpinan sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang pimpinan sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang pimpinan sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_pimpinan.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang pimpinan pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang pimpinan pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang pimpinan pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_pimpinan.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang pimpinan beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang pimpinan beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang pimpinan beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_pimpinan.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang pimpinan wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang pimpinan harus berupa angka.',
                                            'min' => 'Jumlah luas ruang pimpinan tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Ruang Sumber Belajar')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_sumber_belajar.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang sumber belajar milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang sumber belajar milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang sumber belajar milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_sumber_belajar.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang sumber belajar kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang sumber belajar kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang sumber belajar kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_sumber_belajar.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang sumber belajar sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang sumber belajar sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang sumber belajar sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_sumber_belajar.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang sumber belajar pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang sumber belajar pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang sumber belajar pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_sumber_belajar.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang sumber belajar beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang sumber belajar beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang sumber belajar beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_sumber_belajar.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang sumber belajar wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang sumber belajar harus berupa angka.',
                                            'min' => 'Jumlah luas ruang sumber belajar tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Ruang Guru')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_guru.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang guru milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang guru milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang guru milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_guru.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang guru kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang guru kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang guru kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_guru.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang guru sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang guru sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang guru sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_guru.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang guru pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang guru pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang guru pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_guru.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang guru beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang guru beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang guru beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_guru.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang guru wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang guru harus berupa angka.',
                                            'min' => 'Jumlah luas ruang guru tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Ruang Tata Usaha')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('ruang_tata_usaha.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang tata usaha milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah ruang tata usaha milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah ruang tata usaha milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_tata_usaha.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang tata usaha kontrak wajib diisi.',
                                            'numeric' => 'Jumlah ruang tata usaha kontrak harus berupa angka.',
                                            'min' => 'Jumlah ruang tata usaha kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_tata_usaha.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang tata usaha sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang tata usaha sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang tata usaha sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_tata_usaha.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang tata usaha pinjam wajib diisi.',
                                            'numeric' => 'Jumlah ruang tata usaha pinjam harus berupa angka.',
                                            'min' => 'Jumlah ruang tata usaha pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_tata_usaha.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah ruang tata usaha beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah ruang tata usaha beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah ruang tata usaha beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('ruang_tata_usaha.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas ruang tata usaha wajib diisi.',
                                            'numeric' => 'Jumlah luas ruang tata usaha harus berupa angka.',
                                            'min' => 'Jumlah luas ruang tata usaha tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Kamar Mandi')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kamar_mandi.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar mandi milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah kamar mandi milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah kamar mandi milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_mandi.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar mandi kontrak wajib diisi.',
                                            'numeric' => 'Jumlah kamar mandi kontrak harus berupa angka.',
                                            'min' => 'Jumlah kamar mandi kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_mandi.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar mandi sewa wajib diisi.',
                                            'numeric' => 'Jumlah kamar mandi sewa harus berupa angka.',
                                            'min' => 'Jumlah kamar mandi sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_mandi.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar mandi pinjam wajib diisi.',
                                            'numeric' => 'Jumlah kamar mandi pinjam harus berupa angka.',
                                            'min' => 'Jumlah kamar mandi pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_mandi.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar mandi beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah kamar mandi beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah kamar mandi beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_mandi.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas kamar mandi wajib diisi.',
                                            'numeric' => 'Jumlah luas kamar mandi harus berupa angka.',
                                            'min' => 'Jumlah luas kamar mandi tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                                ]),
                
                            Section::make('Kamar Kecil')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kamar_kecil.milik_sendiri')
                                        ->label('Milik Sendiri')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar kecil milik sendiri wajib diisi.',
                                            'numeric' => 'Jumlah kamar kecil milik sendiri harus berupa angka.',
                                            'min' => 'Jumlah kamar kecil milik sendiri tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_kecil.kontrak')
                                        ->label('Kontrak')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar kecil kontrak wajib diisi.',
                                            'numeric' => 'Jumlah kamar kecil kontrak harus berupa angka.',
                                            'min' => 'Jumlah kamar kecil kontrak tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_kecil.sewa')
                                        ->label('Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar kecil sewa wajib diisi.',
                                            'numeric' => 'Jumlah kamar kecil sewa harus berupa angka.',
                                            'min' => 'Jumlah kamar kecil sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_kecil.pinjam')
                                        ->label('Pinjam')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar kecil pinjam wajib diisi.',
                                            'numeric' => 'Jumlah kamar kecil pinjam harus berupa angka.',
                                            'min' => 'Jumlah kamar kecil pinjam tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_kecil.beli_sewa')
                                        ->label('Beli - Sewa')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('buah')
                                        ->validationMessages([
                                            'required' => 'Jumlah kamar kecil beli-sewa wajib diisi.',
                                            'numeric' => 'Jumlah kamar kecil beli-sewa harus berupa angka.',
                                            'min' => 'Jumlah kamar kecil beli-sewa tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
                
                                    TextInput::make('kamar_kecil.jumlah_luas')
                                        ->label('Jumlah Luas Ruangan')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('m²')
                                        ->validationMessages([
                                            'required' => 'Jumlah luas kamar kecil wajib diisi.',
                                            'numeric' => 'Jumlah luas kamar kecil harus berupa angka.',
                                            'min' => 'Jumlah luas kamar kecil tidak boleh kurang dari 0.',
                                        ])
                                        ->extraInputAttributes([
                                            'min' => '0',
                                            'pattern' => '[0-9]*',
                                            'inputmode' => 'numeric'
                                        ]),
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
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan buku pelajaran wajib diisi.',
                                        ]),
                
                                    Select::make('alat_permainan_edukatif')
                                        ->label('Alat Permainan Edukatif')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan alat permainan edukatif wajib diisi.',
                                        ]),
                
                                    Select::make('meja_kursi')
                                        ->label('Meja+Kursi/Bangku untuk Belajar')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan meja dan kursi belajar wajib diisi.',
                                        ]),
                
                                    Select::make('papan_tulis')
                                        ->label('Papan Tulis')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan papan tulis wajib diisi.',
                                        ]),
                
                                    Select::make('alat_tata_usaha')
                                        ->label('Alat Perlengkapan Tata Usaha')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan alat perlengkapan tata usaha wajib diisi.',
                                        ]),
                
                                    Select::make('listrik')
                                        ->label('Listrik')
                                        ->options([
                                            'lebih_dari_cukup' => 'Lebih Dari Cukup',
                                            'cukup' => 'Cukup',
                                            'sedang' => 'Sedang',
                                            'kurang' => 'Kurang',
                                            'tidak_ada' => 'Tidak Ada',
                                        ])
                                        ->required()
                                        ->validationMessages([
                                            'required' => 'Status ketersediaan listrik wajib diisi.',
                                        ]),
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
                                ->required()
                                ->validationMessages([
                                    'required' => 'Status ketersediaan air bersih wajib diisi.',
                                ]),
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
                        Group::make([
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
                                    'rek_ke_lurah' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Lurah',
                                    'rek_ke_korwil' => 'Surat Permohonan Rekomendasi Ijin Operasional ke Korwil Disdikpora Setempat',
                                    'rek_dari_lurah' => 'Surat Rekomendasi dari Lurah Menunjuk Rekomendasi dari Lembaga',
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
                                                if ($record && $record->lampiran) {
                                                    $lampiran = $record->lampiran->where('lampiran_type', $field)->first();
    
                                                    if ($lampiran && $lampiran->lampiran_path) {
                                                        $fileUrl = asset('storage/' . $lampiran->lampiran_path);
                                                        $fileName = basename($lampiran->lampiran_path);
    
                                                        return new HtmlString(<<<HTML
                                                        <div class="text-primary-500 flex items-center">
                                                            <a href="{$fileUrl}" target="_blank" class="text-sm font-semibold flex items-center gap-1 hover:text-primary-600 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v9a2 2 0 01-2 2z" />
                                                                </svg>

                                                                {$fileName}

                                                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-4 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    HTML);
                                                    } else {
                                                        return new HtmlString('<div><p class="text-gray-500">Unggah File PDF maks. 10MB</p></div>');
                                                    }
                                                } else {
                                                    return new HtmlString('<div><p class="text-gray-500">Unggah File PDF maks. 10MB</p></div>');
                                                }
                                            }),
    
                                        FileUpload::make($field)
                                            ->label('')
                                            ->directory('lampiran')
                                            ->disk('public')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->required(function ($record, $livewire) use ($field) {
                                                if ($livewire->isKirimPermohonan) {
                                                    if ($record && $record->lampiran) {
                                                        $lampiran = $record->lampiran->where('lampiran_type', $field)->first();
                                                        return !($lampiran && $lampiran->lampiran_path);
                                                    }
                                                    return true;
                                                }
                                                
                                                return false;
                                            })
                                            ->previewable(true)
                                            ->validationMessages([
                                                'required' => 'Dokumen wajib diunggah.',
                                            ]),
                                    ])->flatten(1)->toArray()
                                ))->toArray()
                            ),
    
                            Group::make([
                                Placeholder::make('preview_pdf')
                                ->label('Surat Permohonan ijin operasional Kursus/PAUD Ditujukan Kepada Kepala Dinas Pendidikan, Kepemudaan dan Olah Raga Kabupaten Badung')
                                ->content(function ($record) {
                                    if ($record && $record->lampiran) {
                                        $lampiran = $record->lampiran->where('lampiran_type', 'permohonan_izin')->first();
    
                                        if ($lampiran && $lampiran->lampiran_path) {
                                            $fileUrl = asset('storage/' . $lampiran->lampiran_path);
                                            $fileName = basename($lampiran->lampiran_path);
    
                                            return new HtmlString(<<<HTML
                                                <div class="text-primary-500 flex items-center">
                                                    <a href="{$fileUrl}" target="_blank" class="text-sm font-semibold flex items-center gap-1 hover:text-primary-600 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v9a2 2 0 01-2 2z" />
                                                        </svg> 

                                                        {$fileName}

                                                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-4 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            HTML);
                                        } else {
                                            return new HtmlString('<div><p class="text-gray-500">Unggah File PDF maks. 10MB</p></div>');
                                        }
                                    } else {
                                        return new HtmlString('<div><p class="text-gray-500">Unggah File PDF maks. 10MB</p></div>');
                                    }
                                }),
    
                                FileUpload::make('permohonan_izin')
                                    ->label('')
                                    ->directory('lampiran')
                                    ->disk('public')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(2048)
                                    ->required(function ($record, $livewire) {
                                        // Jika mode kirim permohonan, periksa keberadaan lampiran
                                        if ($livewire->isKirimPermohonan) {
                                            if ($record && $record->lampiran) {
                                                $lampiran = $record->lampiran->where('lampiran_type', 'permohonan_izin')->first();
                                                return !($lampiran && $lampiran->lampiran_path);
                                            }
                                            return true; // Required jika tidak ada lampiran dan mode submit
                                        }
                                        
                                        // Jika mode draft, tidak required
                                        return false;
                                    })
                                    ->previewable(true)
                                    ->validationMessages([
                                        'required' => 'Dokumen wajib diunggah.',
                                    ]),
                            ])
                        ])    
                        ->hidden(function (Get $get): bool {
                            return $get("wizard_step") < 8;
                        })            
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

                ])
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                <x-filament::button
                        size="sm"
                        class="py-2"
                        x-data
                        x-on:click="\$dispatch('open-modal', { id: 'confirm-submit' })">
                        Kirim Permohonan
                </x-filament::button>

                <x-filament::modal id="confirm-submit" width="md">
                    <x-slot name="heading">
                        Konfirmasi Pengiriman
                    </x-slot>
                    
                    <div class="space-y-2 my-4">
                        <p>Apakah Anda yakin ingin mengirim permohonan ini?</p>
                        <p class="text-sm text-gray-500">Data yang sudah dikirim tidak dapat diubah.</p>
                    </div>
                    
                    <x-slot name="footer">
                        <div class="flex gap-3 mt-2">
                            <x-filament::button
                                color="gray"
                                x-on:click="\$dispatch('close-modal', { id: 'confirm-submit' })">
                                Batal
                            </x-filament::button>
                            
                            <x-filament::button
                                type="submit"
                                wire:click="\$set('isKirimPermohonan', true)"
                                x-on:click="\$dispatch('close-modal', { id: 'confirm-submit' })">
                                Ya, Kirim!
                            </x-filament::button>
                        </div>
                    </x-slot>
                </x-filament::modal>
                BLADE)))
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
            return parent::getEloquentQuery()->whereIn('status_permohonan', ['menunggu_verifikasi', 'menunggu_validasi_lapangan', 'proses_penerbitan_izin', 'izin_diterbitkan', 'permohonan_ditolak']);
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
                ->date('d F Y')
                ->sortable(),

                TextColumn::make('tgl_status_terakhir')
                ->label('Tanggal Status Terakhir')
                ->date('d F Y')
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

                SelectFilter::make('jenis_pendidikan')
                    ->label('Jenis Pendidikan')
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            return $query->whereHas('identitas', function ($q) use ($data) {
                                $q->where('jenis_pendidikan', $data['value']);
                            });
                        }
                        return $query;
                    })
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

    protected function getCreatedNotification(): ?Notification
    {
        return null; // Menonaktifkan notifikasi default dari CreateRecord
    }
}
