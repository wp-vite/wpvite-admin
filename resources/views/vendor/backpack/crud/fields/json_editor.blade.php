{{-- resources/views/vendor/backpack/crud/fields/json_editor.blade.php --}}
@php
    $field['value'] = old($field['name']) ?? $field['value'] ?? '{}';

    // Convert ArrayObject to array or leave as string
    if ($value instanceof \Illuminate\Database\Eloquent\Casts\ArrayObject) {
        $value = $value->jsonSerialize();
    }

    if (is_array($value)) {
        $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
@endphp

<div class="form-group col-md-12">
    <label>{!! $field['label'] ?? ucfirst($field['name']) !!}</label>

    <textarea
        name="{{ $field['name'] }}"
        id="json-editor-{{ $field['name'] }}"
        rows="12"
        class="form-control json-editor"
        style="font-family: monospace;"
        placeholder='{!! json_encode(["key1" => "value1", "key2" => "value2"], JSON_PRETTY_PRINT) !!}',
    >{{ $value }}</textarea>

    <small class="form-text text-muted json-validation-message text-danger" style="display: none;">
        ❌ Invalid JSON format.
    </small>
</div>

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('json-editor-{{ $field['name'] }}');
    const errorMsg = textarea.parentElement.querySelector('.json-validation-message');

    function validateJson() {
        try {
            JSON.parse(textarea.value);
            errorMsg.style.display = 'none';
        } catch (e) {
            errorMsg.style.display = 'block';
        }
    }

    textarea.addEventListener('input', validateJson);
    validateJson(); // initial validation
});
</script>
@endpush
