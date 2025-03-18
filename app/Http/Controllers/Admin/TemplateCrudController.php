<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TemplateRequest;
use App\Jobs\CreateDnsRecordJob;
use App\Jobs\CreateTemplateSiteJob;
use App\Jobs\InstallWordPressJob;
use App\Models\HostingServer;
use App\Models\TemplateCategory;
use App\Repositories\TemplateRepository;
use App\Services\Virtualmin\VirtualminSiteManager;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;

/**
 * Class TemplateCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TemplateCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Template::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/template');
        CRUD::setEntityNameStrings('template', 'templates');
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

        $this->crud->removeColumns([
            'domain',
            'root_directory',
            'site_owner_username',
            'dns_provider',
            'dns_record_id',
            'auth_data',
        ]);
    }

    /**
     * Define what happens when the Show operation is loaded.
     */
    protected function setupShowOperation()
    {
        // automatically add the columns
        $this->autoSetupShowOperation();

        $this->crud->removeColumns([
            'auth_data',
        ]);

        $this->crud->addColumn([
            'name'      => 'auth_data',
            'label'     => 'Authentication Data',
            'type'      => 'custom_html',
            'value' => function ($entry) {
                $authData = $entry->auth_data;

                if(!$authData) {
                    return '';
                }

                if ($authData instanceof \Illuminate\Database\Eloquent\Casts\ArrayObject) {
                    $authData = $authData->toArray();
                }

                // Convert associative array to key-value format
                $formattedData = <<<HER
                    <table>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                HER;

                foreach ($authData as $key => $val) {
                    if(in_array($key, ['db_password', 'db_username', 'admin_password'])) {
                        $val    = "[hidden]";
                    }
                    $formattedData  .= "<tr><td>{$key}</td><td>{$val}</td></tr>";
                }

                $formattedData  .= "</table>";

                return $formattedData;
            },
        ])->beforeColumn('created_at');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(TemplateRequest::class);
        $this->crud->setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */

        // UID
        $this->crud->removeFields([
            'template_uid',
            'domain',
            'root_directory',
            'site_owner_username',
            'dns_provider',
            'dns_record_id',
            'auth_data',
        ]);

        // Category
        $this->crud->addField([
            'name' => 'category_id',
            'type' => 'select_from_array',
            'options' => TemplateCategory::orderBy('category')->pluck('category', 'category_id')->toArray(),
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // Server
        $this->crud->addField([
            'name' => 'server_id',
            'type' => 'select_from_array',
            'options' => HostingServer::orderBy('name')->pluck('name', 'server_id')->toArray(),
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // Status
        // $this->crud->addField([
        //     'name' => 'status',
        //     'type' => 'select_from_array',
        //     'options' => [
        //         1 => 'Active',
        //         0 => 'Inactive',
        //         2 => 'Maintenance',
        //     ],
        //     'wrapperAttributes' => [
        //         'class' => 'form-group col-md-6',
        //     ],
        // ]);
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

    public function store(Request $request)
    {
        // Step 1: Validate the form input
        $data = $request->all();

        // Status
        $data['status'] = 10; // Setup Pending

        // Step 2: Create the template record in the database
        $template = $this->crud->create($data);
        $template->domain   = TemplateRepository::makeTemplateDomain($template);
        $template->save();

        // Dispatch the jobs in a chain
        // Laravel ensures the next job runs only if the previous job succeeds
        CreateDnsRecordJob::withChain([
            new CreateTemplateSiteJob($template),
            new InstallWordPressJob($template),
        ])->dispatch($template);

        // Redirect with success message
        return redirect()->to($this->crud->route)->with('success', 'Template created and domain setup successfully.');
    }
}
