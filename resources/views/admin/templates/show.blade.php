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
            <h1 class="text-capitalize mb-0" bp-section="page-heading">{{ $template->title }}</h1>
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
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        {!! \App\Models\Template::statusBadge($template->status) !!}
                        @if($template->setup_progress)
                            {!! \App\Models\Template::setupProgressBadge($template->setup_progress) !!}
                        @endif
                    </div>
                    <div>
                        @if ($crud->hasAccess('update'))
                            <a href="{{ url($crud->route.'/'.$template->getKey().'/edit') }}" class="btn btn-sm btn-primary">
                                <i class="la la-edit"></i> Edit
                            </a>
                        @endif
                        @if ($crud->hasAccess('publish') && !$template->is_published)
                            <a href="{{ route('template.getPublish', $template->getKey()) }}" class="btn btn-sm btn-success">
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
                <ul class="nav nav-tabs" role="tablist">
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

                <div class="tab-content mt-3">
                    {{-- Overview Tab --}}
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Basic Information</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $template->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $template->description }}</td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <td>{{ $template->category_title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Domain</th>
                                        <td>
                                            @if($template->domain)
                                                <a href="https://{{ $template->domain }}" target="_blank">{{ $template->domain }}</a>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Published At</th>
                                        <td>{{ $template->published_at ? $template->published_at->format('Y-m-d H:i:s') : 'Not published' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Version</th>
                                        <td>{{ $template->current_version ?? 'Not set' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Tags</h5>
                                <div class="mb-3">
                                    @forelse($template->tags as $tag)
                                        <span class="badge bg-info me-1">{{ $tag->tag }}</span>
                                    @empty
                                        <span class="text-muted">No tags assigned</span>
                                    @endforelse
                                </div>

                                <h5>Setup Progress</h5>
                                <div class="progress mb-3">
                                    @php
                                        $progress = 0;
                                        switch($template->setup_progress) {
                                            case 1: $progress = 20; break;
                                            case 2: $progress = 40; break;
                                            case 3: $progress = 60; break;
                                            case 4: $progress = 80; break;
                                            case 100: $progress = 100; break;
                                        }
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" 
                                         aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $progress }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Previews Tab --}}
                    <div class="tab-pane fade" id="previews" role="tabpanel">
                        <div class="row">
                            @forelse($template->previews as $preview)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img src="{{ Storage::disk('s3_admin_public')->url('templates/'.$template->template_id.'/'.$template->current_version.'/previews/'.$preview->image_filename) }}" 
                                             class="card-img-top" alt="{{ $preview->title }}">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $preview->title }}</h5>
                                            @if($preview->description)
                                                <p class="card-text">{{ $preview->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No previews available for this template.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Versions Tab --}}
                    <div class="tab-pane fade" id="versions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Version</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($template->versions as $version)
                                        <tr>
                                            <td>{{ $version->version }}</td>
                                            <td>{{ $version->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No versions available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Server Info Tab --}}
                    <div class="tab-pane fade" id="server" role="tabpanel">
                        @if($template->server)
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Server Details</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ $template->server->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Provider</th>
                                            <td>{{ $template->server->provider }}</td>
                                        </tr>
                                        <tr>
                                            <th>Instance Type</th>
                                            <td>{{ $template->server->instance_type }}</td>
                                        </tr>
                                        <tr>
                                            <th>Public IP</th>
                                            <td>{{ $template->server->public_ip }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>{{ $template->server->status_label }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Resources</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>CPU</th>
                                            <td>{{ $template->server->cpu }}</td>
                                        </tr>
                                        <tr>
                                            <th>RAM</th>
                                            <td>{{ $template->server->ram }}</td>
                                        </tr>
                                        <tr>
                                            <th>Disk Size</th>
                                            <td>{{ $template->server->disk_size }}</td>
                                        </tr>
                                        <tr>
                                            <th>Max Sites</th>
                                            <td>{{ $template->server->max_sites }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No server information available.
                            </div>
                        @endif
                    </div>

                    {{-- Auth Data Tab --}}
                    <div class="tab-pane fade" id="auth" role="tabpanel">
                        @if($template->auth_data)
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Database Credentials</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Database Name</th>
                                            <td>{{ $template->auth_data['db_name'] ?? 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Database Username</th>
                                            <td>{{ $template->auth_data['db_username'] ?? 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Database Password</th>
                                            <td>••••••••</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Admin Credentials</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Admin Username</th>
                                            <td>{{ $template->auth_data['admin_user'] ?? 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Admin Password</th>
                                            <td>••••••••</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No authentication data available.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 