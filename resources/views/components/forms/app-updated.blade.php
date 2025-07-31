@props([
    'model' => 'form.app_updated',
    'othersModel' => 'otherAPP',
    'form' => [], // required for viewOnly mode
    'viewOnly' => false,
])

@php
    $value = data_get($attributes->wire('model')->value, '');
@endphp

<div class="flex flex-col col-span-4">
    <label class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        APP Updated?
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            @if (!in_array($form['app_updated'], ['Yes', 'No']) && filled($otherAPP ?? ''))
                <strong>Others:</strong> {{ $otherAPP }}
            @else
                {{ $form['app_updated'] ?? '—' }}
            @endif
        </div>
    @else
        <div class="flex gap-x-6">
            @foreach (['Yes', 'No', 'Others'] as $option)
                <div class="flex items-center space-x-2">
                    <input type="radio" id="approved-ppmp-{{ strtolower($option) }}" name="app-updated-group"
                        wire:model.live="{{ $model }}" value="{{ $option }}"
                        class="shrink-0 mt-0.5 border-gray-300 rounded-full text-blue-600 focus:ring-blue-500
                checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700
                dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">

                    <label for="aapp-updated-{{ strtolower($option) }}"
                        class="text-sm text-gray-500 dark:text-neutral-400">
                        {{ $option }}
                    </label>
                </div>
            @endforeach
        </div>
        @if ($option === 'Others' && data_get($form, 'app_updated') === 'Others')
            <textarea wire:model.defer="{{ $othersModel }}" placeholder="Please specify" rows="3"
                class="mt-2 ml-2 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700
                    dark:border-neutral-600 dark:text-white resize-y w-96 h-[50px]"></textarea>
        @endif
    @endif
</div>
