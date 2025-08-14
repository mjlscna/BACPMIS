@props([
    'model' => 'form.approved_ppmp',
    'othersModel' => 'otherPPMP',
    'form' => [],
    'viewOnly' => false,
])

<div class="flex flex-col col-span-4">
    <label class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        Approved PPMP?
    </label>

    @if ($viewOnly)
        <div class="text-sm font-bold text-gray-900 dark:text-white ms-3">
            @if (!in_array($form['approved_ppmp'], ['Yes', 'No']) && filled($form['otherPPMP'] ?? ''))
                <span class="font-semibold">Others:</span> {{ $form['otherPPMP'] }}
            @else
                {{ $form['approved_ppmp'] ?? '—' }}
            @endif
        </div>
    @else
        <div class="flex gap-x-6">
            @foreach (['Yes', 'No', 'Others'] as $option)
                <div class="flex items-center space-x-2">
                    <input type="radio" id="approved-ppmp-{{ strtolower($option) }}" name="approved-ppmp-group"
                        wire:model.live="{{ $model }}" value="{{ $option }}"
                        class="shrink-0 mt-0.5 border-gray-300 rounded-full text-blue-600 focus:ring-blue-500
                checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700
                dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">

                    <label for="approved-ppmp-{{ strtolower($option) }}"
                        class="text-sm text-gray-500 dark:text-neutral-400">
                        {{ $option }}
                    </label>
                </div>
            @endforeach


        </div>
        @if ($option === 'Others' && data_get($form, 'approved_ppmp') === 'Others')
            <textarea wire:model.defer="{{ $othersModel }}" placeholder="Please specify" rows="3"
                class="mt-2 ml-2 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700
                    dark:border-neutral-600 dark:text-white resize-y w-96 h-[50px]"></textarea>
        @endif
    @endif
</div>
