@php
    $value = $entry->{$column['name']};

    if ($value instanceof \Illuminate\Database\Eloquent\Casts\ArrayObject) {
        $value = $value->jsonSerialize(); // Convert to array
    } elseif (is_string($value)) {
        $value = json_decode($value, true) ?? [];
    } elseif (!is_array($value)) {
        $value = [];
    }
@endphp

@if (empty($value))
    <div class="text-muted">No data</div>
@else
    <table class="table table-bordered table-sm mb-0" style="font-size: 90%;">
        <thead class="thead-light">
            <tr>
                <th style="width: 40%">Key</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($value as $key => $val)
                <tr>
                    <td><code>{{ $key }}</code></td>
                    <td>
                        @if(is_array($val))
                            <pre style="margin: 0;">{{ json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            {{ $val }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
