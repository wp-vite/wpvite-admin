@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        $crud->entity_name_plural => url($crud->route),
        trans('backpack::crud.preview') => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <div class="container-fluid d-flex justify-content-between my-3">
        <section class="header-operation animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
            <h1 class="text-capitalize mb-0" bp-section="page-heading">{{ $entry->title }}</h1>
            <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Template Details</p>
            @if ($crud->hasAccess('list'))
                <p class="ms-2 ml-2 mb-0" bp-section="page-subheading-back-button">
                    <small><a href="{{ url($crud->route) }}" class="font-sm"><i class="la la-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
                </p>
            @endif
        </section>
        <a href="javascript: window.print();" class="btn float-end float-right"><i class="la la-print"></i></a>
    </div>
@endsection

@section('content')
<div class="row" bp-section="crud-operation-show">
    <div class="{{ $crud->getShowContentClass() }}">
        {{-- Status and Actions --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        {!! \App\Models\Template::statusBadge($entry->status) !!}
                        @if($entry->setup_progress)
                            {!! \App\Models\Template::setupProgressBadge($entry->setup_progress) !!}
                        @endif
                    </div>
                    <div>
                        @if ($crud->hasAccess('update'))
                            <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-sm btn-primary">
                                <i class="la la-edit"></i> Edit
                            </a>
                        @endif
                        @if ($crud->hasAccess('publish') && !$entry->is_published)
                            <a href="{{ route('template.getPublish', $entry->getKey()) }}" class="btn btn-sm btn-success">
                                <i class="la la-upload"></i> Publish
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Tabs --}}
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab">
                            <i class="la la-info-circle"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#previews" role="tab">
                            <i class="la la-images"></i> Previews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#versions" role="tab">
                            <i class="la la-code-branch"></i> Versions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#server" role="tab">
                            <i class="la la-server"></i> Server Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#auth" role="tab">
                            <i class="la la-key"></i> Auth Data
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-4">
                    {{-- Overview Tab --}}
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-hover">
                                            <tr>
                                                <th style="width: 40%">Title</th>
                                                <td>{{ $entry->title }}</td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>{{ $entry->description }}</td>
                                            </tr>
                                            <tr>
                                                <th>Category</th>
                                                <td>{{ $entry->category_title }}</td>
                                            </tr>
                                            <tr>
                                                <th>Domain</th>
                                                <td>
                                                    @if($entry->domain)
                                                        <a href="https://{{ $entry->domain }}" target="_blank" class="text-primary">
                                                            <i class="la la-external-link"></i> {{ $entry->domain }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Published At</th>
                                                <td>
                                                    @if($entry->published_at)
                                                        {{ \Carbon\Carbon::parse($entry->published_at)->format('Y-m-d H:i:s') }}
                                                    @else
                                                        <span class="text-muted">Not published</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Current Version</th>
                                                <td>
                                                    @if($entry->current_version)
                                                        <span class="badge bg-primary">{{ $entry->current_version }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Tags</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse($entry->tags as $tag)
                                            <span class="badge bg-info me-1 mb-1">{{ $tag->tag }}</span>
                                        @empty
                                            <span class="text-muted">No tags assigned</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Setup Progress</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-2" style="height: 25px;">
                                            @php
                                                $progress = 0;
                                                switch($entry->setup_progress) {
                                                    case 1: $progress = 20; break;
                                                    case 2: $progress = 40; break;
                                                    case 3: $progress = 60; break;
                                                    case 4: $progress = 80; break;
                                                    case 100: $progress = 100; break;
                                                }
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" 
                                                 aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $progress }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            @switch($entry->setup_progress)
                                                @case(1)
                                                    Setup Initialized
                                                    @break
                                                @case(2)
                                                    DNS Setup Pending
                                                    @break
                                                @case(3)
                                                    DNS Setup Complete
                                                    @break
                                                @case(4)
                                                    Site Setup Pending
                                                    @break
                                                @case(100)
                                                    Setup Complete
                                                    @break
                                                @default
                                                    Setup Not Started
                                            @endswitch
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Previews Tab --}}
                    <div class="tab-pane fade" id="previews" role="tabpanel">
                        <div class="row">
                            @forelse($entry->previews as $preview)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <img src="{{ Storage::disk('s3_admin_public')->url('templates/'.$entry->template_id.'/'.$entry->current_version.'/previews/'.$preview->image_filename) }}" 
                                             class="card-img-top" alt="{{ $preview->title }}"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $preview->title }}</h5>
                                            @if($preview->description)
                                                <p class="card-text text-muted">{{ $preview->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="la la-info-circle"></i> No previews available for this template.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Versions Tab --}}
                    <div class="tab-pane fade" id="versions" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Version</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($entry->versions as $version)
                                                <tr>
                                                    <td><span class="badge bg-primary">{{ $version->version }}</span></td>
                                                    <td>{{ $version->created_at->format('Y-m-d H:i:s') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No versions available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Server Info Tab --}}
                    <div class="tab-pane fade" id="server" role="tabpanel">
                        @if($entry->server)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Server Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover">
                                                <tr>
                                                    <th style="width: 40%">Name</th>
                                                    <td>{{ $entry->server->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Provider</th>
                                                    <td>{{ $entry->server->provider }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Instance Type</th>
                                                    <td>{{ $entry->server->instance_type }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Public IP</th>
                                                    <td>
                                                        <a href="http://{{ $entry->server->public_ip }}" target="_blank" class="text-primary">
                                                            <i class="la la-external-link"></i> {{ $entry->server->public_ip }}
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>{!! $entry->server->status_label !!}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Resources</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover">
                                                <tr>
                                                    <th style="width: 40%">CPU</th>
                                                    <td>{{ $entry->server->cpu }}</td>
                                                </tr>
                                                <tr>
                                                    <th>RAM</th>
                                                    <td>{{ $entry->server->ram }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Disk Size</th>
                                                    <td>{{ $entry->server->disk_size }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Max Sites</th>
                                                    <td>{{ $entry->server->max_sites }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="la la-exclamation-triangle"></i> No server information available.
                            </div>
                        @endif
                    </div>

                    {{-- Auth Data Tab --}}
                    <div class="tab-pane fade" id="auth" role="tabpanel">
                        @if($entry->auth_data)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Database Credentials</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover">
                                                <tr>
                                                    <th style="width: 40%">Database Name</th>
                                                    <td>{{ $entry->auth_data['db_name'] ?? 'Not set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Database Username</th>
                                                    <td>
                                                        <span class="text-muted">
                                                            <i class="la la-lock"></i> Hidden
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Database Password</th>
                                                    <td>
                                                        <span class="text-muted">
                                                            <i class="la la-lock"></i> Hidden
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Admin Credentials</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover">
                                                <tr>
                                                    <th style="width: 40%">Admin Username</th>
                                                    <td>{{ $entry->auth_data['admin_user'] ?? 'Not set' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Admin Password</th>
                                                    <td>
                                                        <span class="text-muted">
                                                            <i class="la la-lock"></i> Hidden
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="la la-exclamation-triangle"></i> No authentication data available.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 