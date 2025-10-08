<div class="space-y-6 p-8 pb-[5rem]">

    {{-- Form Fields --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
        <div class="grid grid-cols-7 md:grid-cols-7 gap-6">

            {{-- PR Number Dropdown --}}
            <div class="col-span-1">
                {{-- Notice model="form.procurement_id" and options="$procurements" --}}
                <x-forms.select id="pr_number" label="PR No." model="form.pr_number" :options="$procurements" optionValue="id"
                    optionLabel="pr_number" :required="true" wireModifier="live" colspan="col-span-1"
                    :searchable="true" />
            </div>

            {{-- Procurement Program / Project --}}
            <div class="col-span-6">
                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                    model="form.procurement_program_project" :required="true" :rows="$textareaRows" colspan="col-span-8"
                    :readonly="true" />
            </div>

            {{-- Document File Upload --}}
            <div class="col-span-2">
                <x-forms.file id="document_file" label="Approved PR Document (PDF)" model="document_file"
                    :required="true" accept="application/pdf" colspan="col-span-3" />
            </div>
            {{-- Remarks --}}
            <div class="col-span-5">
                <x-forms.textarea id="remarks" label="Remarks" model="form.remarks" :required="false"
                    :rows="1" colspan="col-span-6" />
            </div>
        </div>
    </div>

    {{-- Fixed Action Bar with Save Button --}}
    <div
        class="fixed bottom-5 right-0 left-0 lg:ml-[13.75rem] flex justify-end p-2 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 z-49">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end">
            <button wire:click="save" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                <div wire:loading wire:target="save" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white">
                </div>
                Save
            </button>
        </div>
    </div>

</div>
