@if ($crud->hasAccess('publish') && !$entry->is_published)
    <form method="POST" action="{{ url($crud->route.'/'.$entry->getKey().'/publish') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">
            <i class="la la-upload"></i> Publish
        </button>
    </form>
@endif
