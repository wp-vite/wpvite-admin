@extends(backpack_view('layouts.vertical'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">Publish Template</h1>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('template.publish', $template->template_id) }}" enctype="multipart/form-data" id="publishForm">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <p class="fs-2 fw-bold">Template: <a href="{{ route('template.show', $template->template_id) }}">{{ $template->title }}</a></p>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Version <span class="text-danger">*</span></label>
                                <input type="text" name="new_version" value="{{ $newVersion }}" class="form-control @error('new_version') is-invalid @enderror" required>
                                <small class="form-text text-muted">Format: X.Y or X.YY (e.g. 1.0 or 1.01)</small>
                                @error('new_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="overrideVersion" name="override_version" value="1">
                                    <label class="form-check-label" for="overrideVersion">Override existing version</label>
                                </div>
                                <small class="form-text text-muted">Check this if you want to update an existing version</small>
                            </div>
                        </div>
                    </div>

                    <div id="preview-group">
                        <div class="mb-3 preview-entry">
                            <div class="preview-content">
                                <div class="form-group">
                                    <label>Page Title <span class="text-danger">*</span></label>
                                    <input type="text" name="previews[0][title]" class="form-control @error('previews.0.title') is-invalid @enderror" required>
                                    @error('previews.0.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Screenshot <span class="text-danger">*</span></label>
                                    <input type="file" name="previews[0][screenshot]" class="form-control-file preview-image @error('previews.0.screenshot') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg" required>
                                    <small class="form-text text-muted">Max size: 2MB. Supported formats: JPEG, PNG, JPG</small>
                                    @error('previews.0.screenshot')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="preview-image-container mt-2" style="display: none;">
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-md btn-outline-primary mb-3" id="add-preview">
                            <i class="la la-plus"></i> Add More Previews
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <a href="{{ route('template.show', $template->template_id) }}" class="btn btn-secondary">
                    <i class="la la-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success" id="publishButton">
                    <i class="la la-upload"></i> Publish Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after_scripts')
<script>
let previewIndex = 1;

// Handle image preview
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('preview-image')) {
        const file = e.target.files[0];
        const container = e.target.parentElement.querySelector('.preview-image-container');
        const img = container.querySelector('img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            container.style.display = 'none';
        }
    }
});

// Add new preview
document.getElementById('add-preview').addEventListener('click', function () {
    const group = document.createElement('div');
    group.classList.add('mb-3', 'preview-entry');
    group.innerHTML = `
        <hr />
        <div class="preview-content">
            <div class="form-group">
                <label>Page Title <span class="text-danger">*</span></label>
                <input type="text" name="previews[${previewIndex}][title]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Screenshot <span class="text-danger">*</span></label>
                <input type="file" name="previews[${previewIndex}][screenshot]" class="form-control-file preview-image" accept="image/jpeg,image/png,image/jpg" required>
                <small class="form-text text-muted">Max size: 2MB. Supported formats: JPEG, PNG, JPG</small>
                <div class="preview-image-container mt-2" style="display: none;">
                    <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-preview">
                <i class="la la-trash"></i> Remove Preview
            </button>
        </div>`;
    document.getElementById('preview-group').appendChild(group);
    previewIndex++;
});

// Remove preview
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-preview')) {
        e.target.closest('.preview-entry').remove();
    }
});

// Form submission
document.getElementById('publishForm').addEventListener('submit', function(e) {
    const button = document.getElementById('publishButton');
    button.disabled = true;
    button.innerHTML = '<i class="la la-spinner fa-spin"></i> Publishing...';
});
</script>
@endpush
