{{-- Keep the Filament schema view as a thin adapter to the reusable component. --}}
<x-open-graph-preview
    :meta-data="$metaData"
    :url="$url"
    :has-unsaved-changes="$hasUnsavedChanges"
    :platforms="$platforms"
    :show-note="$showNote"
/>