<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermohonanResource\Pages;
use App\Filament\Resources\PermohonanResource\RelationManagers;
use App\Models\District;
use App\Models\Permohonan;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
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
