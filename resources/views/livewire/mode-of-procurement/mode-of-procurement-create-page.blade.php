<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Mode of Procurement</h2>
                    @if ($procurement)
                        <p class="mt-1 text-sm text-gray-600">
                            PR Number: {{ $procurement->pr_number }} | ProcID: {{ $procurement->procID }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            Project: {{ $procurement->procurement_program_project }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white shadow rounded-lg p-6">
            <!-- Your form content here -->
        </div>
    </div>
</div>
