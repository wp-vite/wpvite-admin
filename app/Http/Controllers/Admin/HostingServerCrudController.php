<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\HostingServerRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class HostingServerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class HostingServerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\HostingServer::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/hosting-server');
        CRUD::setEntityNameStrings('hosting server', 'hosting servers');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */

        $this->crud->removeColumns(['status', 'instance_id', 'virtualmin_url']);

        $tinyIntFields  = ['max_sites', 'cpu', 'ram', 'disk_size'];
        foreach($tinyIntFields as $field) {
            $this->crud->column($field)->type('number');
        }

        $this->crud->addColumn([
            'name' => 'status_label', // Use accessor attribute
            'label' => 'Status', // Column label
            'type' => 'text', // Display the resolved label as plain text
        ]);
        $this->crud->column('status_label')->after('name');
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        // automatically add the columns
        $this->autoSetupShowOperation();

        $this->crud->removeColumns(['status']);

        $tinyIntFields  = ['max_sites', 'cpu', 'ram', 'disk_size'];
        foreach($tinyIntFields as $field) {
            $this->crud->column($field)->type('number');
        }

        $this->crud->addColumn([
            'name' => 'status_label', // Use accessor attribute
            'label' => 'Status', // Column label
            'type' => 'text', // Display the resolved label as plain text
        ]);
        $this->crud->column('status_label')->after('name');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(HostingServerRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */

        // Provider
        $this->crud->addField([
            'name' => 'provider',
            'type' => 'select_from_array',
            'options' => ['aws' => 'AWS', 'gcp' => 'GCP'],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // Instance type
        $this->crud->addField([
            'name' => 'instance_type',
            'type' => 'select_from_array',
            'options' => ['ec2' => 'EC2', 'light_sail' => 'LightSail'],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // Public ip
        $this->crud->field('public_ip')->wrapper([
            'class' => 'form-group col-md-6',
        ]);

        // Private ip
        $this->crud->field('private_ip')
            ->attributes([
                'placeholder' => '(optional)',
            ])
            ->wrapper([
                'class' => 'form-group col-md-6',
            ]);

        // Instance id
        $this->crud->field('instance_id')->label('Instance id')
            ->attributes([
                'placeholder' => '(optional)',
            ])
            ->wrapper([
                'class' => 'form-group col-md-6',
            ]);

        // Virtualmin URL
        $this->crud->field('virtualmin_url')->wrapper([
            'class' => 'form-group col-md-6',
        ]);

        $tinyIntFields  = ['max_sites', 'cpu', 'ram', 'disk_size'];
        foreach($tinyIntFields as $field) {
            $this->crud->field($field)->type('number')->wrapper([
                'class' => 'form-group col-md-3',
            ]);
        }

        // Instance type
        $this->crud->addField([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => [
                1 => 'Active',
                0 => 'Inactive',
                2 => 'Maintenance',
            ],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // Authorization
        $this->crud->addField([
            'name' => 'authorization',
            'label' => 'Authorization (JSON only)',
            'type' => 'textarea',
            'attributes' => [
                // 'placeholder'  => "{\"key1\":\"value1\", \"key2\":\"value2\"}",
                'placeholder'  => json_encode(["key1" => "value1", "key2" => "value2"], JSON_PRETTY_PRINT),
                'rows'  => 5,
            ]
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
}
