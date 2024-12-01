<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('first_name')->label('First Name');
        CRUD::column('last_name')->label('Last Name');
        CRUD::column('email')->label('Email Address')->type('email');
        CRUD::column('role')->label('Role')->type('enum');
        CRUD::column('status')->label('Status')->type('boolean')->options([
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Suspended',
        ]);
        CRUD::column('mobile')->label('Mobile Number');
        CRUD::addColumn([
            'name' => 'isd_code',
            'label' => 'ISD Code',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'name' => 'country_id',
            'label' => 'Country',
            'type' => 'relationship',
        ]);
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::addField([
            'name' => 'first_name',
            'label' => 'First Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'last_name',
            'label' => 'Last Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::field('email')->label('Email Address')->type('email');
        CRUD::addField([
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'password_confirmation',
            'label' => 'Confirm Password',
            'type' => 'password',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'role',
            'label' => 'Role',
            'type' => 'select_from_array',
            'options'   => ['admin' => 'Admin', 'user' => 'User'],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'options'   => [
                0 => 'Inactive',
                1 => 'Active',
                2 => 'Suspended',
            ],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'isd_code',
            'label' => 'ISD Code',
            'type' => 'number',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-3', // Half-width column
            ],
        ]);
        CRUD::addField([
            'name' => 'mobile',
            'label' => 'Mobile Number',
            'type' => 'number',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6', // Half-width column
            ],
        ]);
        CRUD::field('country_id')->label('Country')->type('select_from_array')->options([
            1 => 'India',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Show operation is loaded.
     */
    protected function setupShowOperation()
    {
        CRUD::column('first_name')->label('First Name');
        CRUD::column('last_name')->label('Last Name');
        CRUD::column('email')->label('Email Address')->type('email');
        CRUD::column('role')->label('Role');
        CRUD::column('status')->label('Status')->type('boolean')->options([
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Suspended',
        ]);
        CRUD::column('mobile')->label('Mobile Number');
        CRUD::column('isd_code')->label('ISD Code');
        CRUD::addColumn([
            'name' => 'country_id',
            'label' => 'Country',
            'type' => 'relationship',
        ]);
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }
}
