{{-- resources/views/components/forms/pdf-viewer.blade.php --}}
<div x-data="{
    show: false,
    pdfUrl: ''
}" x-on:show-pdf-modal.window="
        show = true;
        pdfUrl = event.detail.url;
    "
    x-on:keydown.escape.window="show = false" x-show="show" {{-- These classes create the see-through background --}}
    class="fixed inset-0 z-[100] flex items-center justify-center bg-emerald-700/50 p-4" style="display: none;">
    <div @click.away="show = false"
        class="flex flex-col w-full max-w-4xl h-[90vh] bg-white dark:bg-neutral-800 rounded-lg shadow-xl overflow-hidden">

        <div class="flex-grow">
            <template x-if="show">
                <iframe :src="pdfUrl" class="w-full h-full" frameborder="0"></iframe>
            </template>
        </div>

    </div>
</div>
