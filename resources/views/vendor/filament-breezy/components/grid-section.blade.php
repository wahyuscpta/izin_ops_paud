{{-- @props(['title','description'])
<x-filament::grid @class(["pt-6 gap-4 filament-breezy-grid-section"]) {{ $attributes }}>

    <x-filament::grid.column>
        <h3 @class(['text-lg font-medium filament-breezy-grid-title'])>{{$title}}</h3>

        <p @class(['mt-1 text-sm text-gray-500 filament-breezy-grid-description'])>
            {{$description}}
        </p>
    </x-filament::grid.column>

    <x-filament::grid.column>
        {{ $slot }}
    </x-filament::grid.column>

</x-filament::grid> --}}

@props(['title','description'])
<div class="col-span-1">
    <div class="bg-white dark:bg-gray-900 shadow rounded-xl h-full">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
            @if(isset($description))
                <p class="mt-1 text-sm text-gray-500">
                    {{ $description }}
                </p>
            @endif
        </div>
        <div class="px-4 py-5 sm:p-6 border-none">
            {{ $slot }}
        </div>
    </div>
</div>
