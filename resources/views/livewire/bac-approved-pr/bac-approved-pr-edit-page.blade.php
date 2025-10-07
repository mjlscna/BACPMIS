<div class="space-y-6 p-8 pb-[5rem]">

    {{-- Form Fields --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
        <div class="grid grid-cols-7 md:grid-cols-7 gap-6">

            {{-- PR Number Dropdown --}}
            <div class="col-span-1">
                <x-forms.select id="pr_number" label="PR No." model="form.pr_number" :options="$procurements" optionValue="id"
                    optionLabel="pr_number" :required="true" :disabled="true" {{-- <-- Add this to disable the dropdown --}}
                    colspan="col-span-1" />
            </div>

            {{-- Procurement Program / Project --}}
            <div class="col-span-6">
                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                    model="form.procurement_program_project" :required="true" :rows="1" :readonly="true" />
            </div>

            {{-- Document File Upload --}}

            <div class="col-span-2">
                <x-forms.file id="document_file" label="Replace Document (Optional)" model="document_file"
                    :required="false" accept="application/pdf" />
            </div>
            {{-- Remarks --}}
            <div class="col-span-4">
                <x-forms.textarea id="remarks" label="Remarks" model="form.remarks" :required="false"
                    :rows="1" />
            </div>
            <div class="col-span-3">
                <span class="font-medium text-gray-700 dark:text-gray-200">Current Document:</span>
                <button type="button" wire:click="viewPdf" class="text-emerald-600 hover:underline focus:outline-none">
                    {{ basename($bacapprovedpr->filepath) }}
                </button>
            </div>
        </div>
    </div>

    {{-- Fixed Action Bar with an "Update" Button --}}
    <div class="fixed bottom-5 right-0 left-0 lg:ml-[13.75rem] flex justify-end p-2 ...">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end">
            <button wire:click="update" wire:loading.attr="disabled" class="...">
                <div wire:loading wire:target="update" class="..."></div>
                Save
            </button>
        </div>
    </div>
    <x-forms.pdf-viewer />
</div>
