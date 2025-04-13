<select wire:model.defer="{{ $wireModel }}" class="border p-2 rounded w-full">
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
