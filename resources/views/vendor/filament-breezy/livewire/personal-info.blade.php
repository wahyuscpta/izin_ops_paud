<x-filament-breezy::grid-section :title="__('filament-breezy::default.profile.personal_info.heading')" :description="__('filament-breezy::default.profile.personal_info.subheading')">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    {{ __('filament-breezy::default.profile.personal_info.submit.label') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>

{{-- <x-filament-breezy::grid-section 
    :title="__('Ubah Data Diri')"
>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}
        
        <div class="flex justify-center">
            <x-filament::button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-breezy::grid-section> --}}
