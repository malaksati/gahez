@props([
    'importBatches' => collect(),
    'exportBatches' => collect(),
    'showRoutePrefix' => null,
    'downloadRoutePrefix' => null,
    'showLogs' => false,
])

<div @class(['import-export-layout', 'import-export-layout--no-sidebar' => ! $showLogs])>
    <div class="import-export-main">
        {{ $slot }}
    </div>
    @if ($showLogs)
        <div class="import-export-sidebar">
            @include('v1.admin.partials.transfer-sidebar', [
                'importBatches' => $importBatches,
                'exportBatches' => $exportBatches,
                'showRoutePrefix' => $showRoutePrefix,
                'downloadRoutePrefix' => $downloadRoutePrefix,
            ])
        </div>
    @endif
</div>
