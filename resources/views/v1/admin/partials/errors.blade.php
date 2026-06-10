@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <p class="font-medium">{{ __('messages.Please fix the following errors:') }}</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
