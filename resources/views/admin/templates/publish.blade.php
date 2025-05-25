@extends(backpack_view('layouts.vertical'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">Publish Template</h1>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        <form method="POST" action="{{ route('template.publish', $template->template_id) }}" enctype="multipart/form-data">
            @csrf

            <div class="card mb-3">
                <div class="card-body">

                    <div class="mb-3">
                        <p class="fs-2 fw-bold">Template: <a href="{{ route('template.show', $template->template_id) }}">{{ $template->title }}</a></p>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Version</label>
                                <input type="text" name="new_version" value="{{ $newVersion }}" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div id="preview-group">
                        <div class="mb-3 preview-entry">
                            <div class="preview-content">
                                <div class="form-group">
                                    <label>Page Title</label>
                                    <input type="text" name="previews[0][title]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Screenshot</label>
                                    <input type="file" name="previews[0][screenshot]" class="form-control-file" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-md btn-outline-primary mb-3" id="add-preview">+ Add More</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <a href="{{ route('template.show', $template->template_id) }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-success"><i class="la la-upload"></i> Publish Template</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after_scripts')
<script>
let previewIndex = 1;
document.getElementById('add-preview').addEventListener('click', function () {
    const group = document.createElement('div');
    group.classList.add('mb-3', 'preview-entry');
    group.innerHTML = `
        <hr />
        <div class="preview-content">
            <div class="form-group">
                <label>Page Title</label>
                <input type="text" name="previews[${previewIndex}][title]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Screenshot</label>
                <input type="file" name="previews[${previewIndex}][screenshot]" class="form-control-file" accept="image/*" required>
            </div>
        </div>`;
    document.getElementById('preview-group').appendChild(group);
    previewIndex++;
});
</script>
@endpush
