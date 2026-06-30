@props([
    'id',
    'label',
    'model',
    'form' => [],
    'viewOnly' => false,
    'required' => false,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'colspan' => '',
    'wireModifier' => 'live',
    'searchable' => true,
])

@php
    $fieldKey = str($model)->replace('form.', '');
    $value = data_get($form, $fieldKey);
    $isAssoc = is_array($options) && array_keys($options) !== range(0, count($options) - 1);

    // Normalize options for easier processing in both view-only mode and the searchable component.
    $normalizedOptions = collect($options)
        ->map(function ($label, $value) use ($isAssoc, $optionValue, $optionLabel) {
            if ($isAssoc) {
                return ['value' => $value, 'label' => $label];
            } else {
                return ['value' => data_get($label, $optionValue), 'label' => data_get($label, $optionLabel)];
            }
        })
        ->values()
        ->all();

    // Determine the display value for the 'viewOnly' mode.
    $displayValue = collect($normalizedOptions)->firstWhere('value', $value)['label'] ?? '—';

    // Livewire 3 entangle: deferred by default, ".live" for live binding.
    // (".defer" is Livewire 2 syntax and breaks the searchable component.)
    $entangleSuffix = $wireModifier === 'live' ? '.live' : '';
@endphp

<div class="flex flex-col {{ $colspan }}">
    @if ($label)
        <label for="{{ $id }}"
            class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500 dark:text-neutral-400' : 'text-gray-700 dark:text-gray-200' }} mb-1">
            @if ($required && !$viewOnly)
                <span class="text-red-500 mr-1">*</span>
            @endif
            {!! str($label)->contains('|')
                ? explode('|', $label)[0] . ' <span class="text-xs text-gray-500">(' . explode('|', $label)[1] . ')</span>'
                : $label !!}
        </label>
    @endif

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $displayValue }}
        </div>
    @else
        {{-- Conditionally render based on the 'searchable' prop --}}
        @if ($searchable)
            {{-- Searchable Alpine.js Component --}}
            <div x-data="{
                // ... x-data object remains the same
                open: false,
                suppressOpen: false,
                search: '',
                highlight: 0,
                options: {{ json_encode($normalizedOptions) }},
                value: $wire.entangle('{{ $model }}'){{ $entangleSuffix }},
                get selectedLabel() {
                    if (!this.value && this.value !== 0) return 'Select';
                    const selected = this.options.find(opt => opt.value == this.value);
                    return selected ? selected.label : 'Select';
                },
                get filteredOptions() {
                    if (this.search === '') return this.options;
                    return this.options.filter(opt =>
                        String(opt.label).toLowerCase().includes(this.search.toLowerCase())
                    );
                },
                selectOption(option) {
                    this.value = option ? option.value : null;
                    this.open = false;
                    this.search = '';
                    this.highlight = 0;
                    this.suppressOpen = true; // returning focus to the trigger must not re-open the panel
                    this.$nextTick(() => this.$refs.trigger?.focus());
                },
                move(direction) {
                    const count = this.filteredOptions.length;
                    if (count === 0) return;
                    this.highlight = (this.highlight + direction + count) % count;
                    this.$nextTick(() => {
                        const el = this.$refs.list?.querySelector(`[data-index=&quot;${this.highlight}&quot;]`);
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                },
                selectHighlighted() {
                    const option = this.filteredOptions[this.highlight];
                    if (option) this.selectOption(option);
                }
            }" x-init="$watch('open', isOpen => { if (isOpen) { highlight = 0; $nextTick(() => $refs.searchInput.focus()) } }); $watch('search', () => highlight = 0)" class="relative mt-1" @click.outside="open = false" @focusout="if (!$el.contains($event.relatedTarget)) open = false">

                {{-- Trigger + clear, wrapped so the clear button is a sibling (valid HTML), not nested --}}
                <div class="relative">
                    <button type="button" x-ref="trigger" @mousedown="suppressOpen = true"
                        @focus="if (!suppressOpen) { open = true } suppressOpen = false"
                        @click="open = !open" @keydown.escape.stop="open = false"
                        @keydown.enter.prevent="open = true" @keydown.arrow-down.prevent="open = true" id="{{ $id }}"
                        class="relative w-full cursor-default rounded-md border bg-white py-2 pl-3 pr-10 text-left text-sm dark:bg-neutral-700 dark:text-white focus:outline-none focus:ring-1 @error($model) border-red-500 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror">
                        <span class="block truncate" x-text="selectedLabel"></span>

                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 3a.75.75 0 01.53.22l3.5 3.5a.75.75 0 01-1.06 1.06L10 4.81 6.53 8.28a.75.75 0 01-1.06-1.06l3.5-3.5A.75.75 0 0110 3zm-3.72 9.28a.75.75 0 011.06 0L10 15.19l3.47-3.47a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    {{-- Clear button: sibling of the trigger, kept out of the tab order --}}
                    <button x-show="value" x-transition @click.prevent.stop="value = null" type="button" tabindex="-1"
                        class="absolute inset-y-0 right-7 flex items-center rounded-full p-0.5 text-gray-500 hover:bg-gray-200 hover:text-gray-700 focus:outline-none dark:hover:bg-neutral-600 dark:hover:text-white"
                        style="display: none;">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>

                {{-- Dropdown Panel (Unchanged) --}}
                <div x-show="open" x-transition
                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-neutral-800 sm:text-sm"
                    style="display: none;">
                    <div class="p-2">
                        <input type="search" x-ref="searchInput" x-model.debounce.300ms="search"
                            @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
                            @keydown.enter.prevent="selectHighlighted()" @keydown.escape.prevent.stop="open = false"
                            placeholder="Search options..."
                            class="block w-full rounded-md border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                    </div>
                    <ul class="max-h-40 overflow-y-auto" x-ref="list">
                        {{-- A clickable "blank" option --}}
                        <li @click="selectOption(null)"
                            class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-indigo-50 dark:text-white dark:hover:bg-emerald-700">
                            <span class="block truncate italic text-gray-500">Select</span>
                        </li>
                        <template x-for="(option, index) in filteredOptions" :key="option.value">
                            <li @click="selectOption(option)" @mousemove="highlight = index" :data-index="index"
                                :class="{
                                    'bg-indigo-100 dark:bg-indigo-900': value == option.value,
                                    'bg-indigo-50 dark:bg-emerald-700': highlight === index
                                }"
                                class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 dark:text-white">
                                <span class="block truncate" :class="{ 'font-semibold': value == option.value }"
                                    x-text="option.label"></span>
                            </li>
                        </template>
                        <template x-if="filteredOptions.length === 0 && search !== ''">
                            <li class="relative cursor-default select-none py-2 px-3 text-gray-500">No options found.
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        @else
            {{-- Original Standard Select Component --}}
            <select id="{{ $id }}" wire:model.{{ $wireModifier }}="{{ $model }}"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm dark:bg-neutral-700 dark:text-white dark:[color-scheme:dark]
                    @error($model) border-red-500 focus:border-red-500 focus:ring-red-500
                    @else border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror"
                {{ $required ? 'required' : '' }}>

                <option value="">Select</option>

                @if ($isAssoc)
                    @foreach ($options as $key => $text)
                        <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                @else
                    @foreach ($options as $option)
                        <option value="{{ $option[$optionValue] }}">{{ $option[$optionLabel] }}</option>
                    @endforeach
                @endif
            </select>
        @endif

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
