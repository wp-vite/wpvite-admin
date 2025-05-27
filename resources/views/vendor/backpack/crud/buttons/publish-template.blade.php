@if ($crud->hasAccess('publish') && !$entry->is_published)
    <div class="d-flex align-items-center">
        <div class="form-check me-2">
            <input type="checkbox" class="form-check-input" id="overrideVersion{{ $entry->getKey() }}" name="override_version">
            <label class="form-check-label" for="overrideVersion{{ $entry->getKey() }}">Override existing version</label>
        </div>
        <a href="{{ route('template.getPublish', $entry->getKey()) }}" class="btn btn-sm btn-success" onclick="return validatePublish(event, {{ $entry->getKey() }})">
            <i class="la la-upload"></i> Publish
        </a>
    </div>

    <script>
        function validatePublish(event, entryId) {
            const overrideCheckbox = document.getElementById('overrideVersion' + entryId);
            
            // Get the current version number from the page
            const currentVersion = '{{ $entry->version ?? "" }}';
            
            // Check if this version already exists (you'll need to implement this check)
            const versionExists = checkVersionExists(currentVersion);
            
            if (versionExists && !overrideCheckbox.checked) {
                alert('This version number already exists. Please check the "Override existing version" checkbox to proceed.');
                event.preventDefault();
                return false;
            }
            
            if (overrideCheckbox.checked) {
                return confirm('Are you sure you want to override the existing version?');
            }
            
            return true;
        }

        function checkVersionExists(version) {
            // This is a placeholder - you'll need to implement the actual version check
            // You could either:
            // 1. Make an AJAX call to check if the version exists
            // 2. Pass the version existence status from the backend to the view
            // 3. Use a data attribute on the element to store this information
            
            // For now, we'll use a data attribute approach
            const versionExists = document.querySelector('[data-version-exists]')?.dataset.versionExists === 'true';
            return versionExists;
        }
    </script>
@endif
