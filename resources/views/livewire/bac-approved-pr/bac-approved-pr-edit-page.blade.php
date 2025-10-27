<div class="space-y-6 p-2 pb-[5rem]">

    {{-- Form Fields --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div class="grid grid-cols-7 md:grid-cols-10 gap-6">

            {{-- PR Number Dropdown --}}

            <x-forms.input id="pr_number" textAlign='right' label="PR No." model="form.pr_number" :required="true"
                colspan="col-span-1" :disabled="true" />

            {{-- Procurement Program / Project --}}

            <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                model="form.procurement_program_project" :required="true" :rows="$textareaRows" colspan="col-span-9"
                :readonly="true" />

            <x-forms.input id="document_url" type="text" label="Approved PR Document URL" model="form.filepath"
                placeholder="http://example.com/path/to/document.pdf" :required="true" colspan="col-span-5" />

            {{-- Remarks --}}
            <x-forms.textarea id="remarks" label="Remarks" model="form.remarks" :required="false" :rows="1"
                colspan="col-span-2" />

            <div class="col-span-3">
                {{-- Wrapped in a flex container for proper alignment --}}
                <div class="flex items-center gap-x-2">
                    <span class="font-medium text-gray-700 dark:text-gray-200">Current Document:</span>

                    <a href="{{ $form['filepath'] }}" target="_blank" rel="noopener noreferrer"
                        class="text-emerald-600 hover:text-emerald-700 focus:outline-none" title="View Document">

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd"
                                d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75h-3.44a.75.75 0 0 1 0-1.5Zm-10.5 4.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h10.5a1.5 1.5 0 0 0 1.5-1.5V10.5a.75.75 0 0 1 1.5 0v8.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V8.25a3 3 0 0 1 3-3h8.25a.75.75 0 0 1 0 1.5H5.25Z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

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
