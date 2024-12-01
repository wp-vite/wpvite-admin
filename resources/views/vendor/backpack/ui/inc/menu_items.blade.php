{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Users" icon="la la-user" :link="backpack_url('user')" />
<x-backpack::menu-item title="User sites" icon="la la-sitemap" :link="backpack_url('user-site')" />

<x-backpack::menu-dropdown title="Templates" icon="la la-folder">
    <x-backpack::menu-dropdown-item title="Templates" icon="la la-file-alt" :link="backpack_url('template')" />
    <x-backpack::menu-dropdown-item title="Template categories" icon="la la-list" :link="backpack_url('template-category')" />
</x-backpack::menu-dropdown>
<x-backpack::menu-item title="Hosting servers" icon="la la-server" :link="backpack_url('hosting-server')" />
{{-- <x-backpack::menu-item title="Countries" icon="la la-globe" :link="backpack_url('country')" /> --}}
