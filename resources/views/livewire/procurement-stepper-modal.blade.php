<div x-data="{ open: @entangle('isOpen') }">
    <!-- Modal -->
    <div x-show="open" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-[600px]">
            <!-- Modal Header -->
            <div class="flex justify-between items-center border-b pb-2">
                <h2 class="text-lg font-semibold">Procurement Form</h2>
                <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>

            <!-- Stepper -->
            <ul class="flex justify-center gap-4 my-4">
                <li class="{{ $step == 1 ? 'text-blue-600 font-bold' : 'text-gray-400' }}">Step 1</li>
                <li class="{{ $step == 2 ? 'text-blue-600 font-bold' : 'text-gray-400' }}">Step 2</li>
            </ul>

            <!-- Form Content -->
            <div class="mt-4">
                @if ($step == 1)
                    <div>
                        <label class="block text-sm font-medium">PR No.</label>
                        <input type="text" wire:model="pr_number"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                        @error('pr_number')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror

                        <label class="block text-sm font-medium mt-2">Procurement Program</label>
                        <textarea wire:model="procurement_program_project" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        @error('procurement_program_project')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                @elseif ($step == 2)
                    <div>
                        <label class="block text-sm font-medium">Mode of Procurement</label>
                        <select wire:model="mode_of_procurement_id" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1">Mode 1</option>
                            <option value="2">Mode 2</option>
                        </select>

                        <label class="block text-sm font-medium mt-2">IB No.</label>
                        <input type="text" wire:model="ib_number"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                @endif
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-4 flex justify-between">
                <button wire:click="prevStep" class="py-2 px-3 border bg-white text-gray-800"
                    {{ $step == 1 ? 'disabled' : '' }}>
                    Back
                </button>
                @if ($step < 2)
                    <button wire:click="nextStep" class="py-2 px-3 border bg-blue-600 text-white">Next</button>
                @else
                    <button wire:click="save" class="py-2 px-3 border bg-green-600 text-white">Save</button>
                @endif
            </div>
        </div>
    </div>
</div>
