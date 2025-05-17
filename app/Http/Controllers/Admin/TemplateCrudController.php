<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Operations\PublishOperation;
use App\Http\Requests\TemplateRequest;
use App\Jobs\TemplateSiteSetupJob;
use App\Models\HostingServer;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Repositories\TemplateRepository;
use App\Services\Template\TemplateService;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

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
    use PublishOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Template::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/template');
        CRUD::setEntityNameStrings('template', 'templates');

        $this->crud->allowAccess('publish');
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
            'status',
            'setup_progress',
            'domain',
            'root_directory',
            'site_owner_username',
            'dns_provider',
            'dns_record_id',
            'auth_data',
            'published_at',
        ]);

        // Status
        $this->crud->column([
            'name' => 'status',
            'type'      => 'Text',
            'value' => function ($entry) {
                return $entry->status;
            },
        ]);

        // setup_progress
        $this->crud->column([
            'name' => 'setup_progress',
            'type'      => 'Text',
            'value' => function ($entry) {
                return $entry->setup_progress;
            },
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
            'status',
            'setup_progress',
            'auth_data',
        ]);

        // Status
        $this->crud->addColumn([
            'name' => 'status',
            'type'      => 'Text',
            'value' => function ($entry) {
                return Template::status($entry->status);
            },
        ])->afterColumn('category_id');

        // setup_progress
        $this->crud->addColumn([
            'name' => 'setup_progress',
            'type'      => 'Text',
            'value' => function ($entry) {
                return Template::setupProgress($entry->setup_progress, '-');
            },
        ])->afterColumn('status');

        // Auth data
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
                    <table class="table table-bordered table-sm mb-0" style="font-size: 90%;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40%">Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                HER;

                foreach ($authData as $key => $val) {
                    if(in_array($key, ['db_password', 'db_username', 'admin_password'])) {
                        $val    = "*******";
                    }
                    $formattedData  .= "<tr><td>{$key}</td><td>{$val}</td></tr>";
                }

                $formattedData  .= "</tbody></table>";

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
            'status',
            'setup_progress',
            'domain',
            'root_directory',
            'site_owner_username',
            'dns_provider',
            'dns_record_id',
            'auth_data',
            'published_at',
            'current_version',
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

        // Status
        $this->crud->addField([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => Template::status(),
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // setup_progress
        $this->crud->addField([
            'name' => 'setup_progress',
            'type' => 'select_from_array',
            'options' => Template::setupProgress(),
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
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

        // Dispatch the TemplateSiteSetupJob
        TemplateSiteSetupJob::dispatch($template);

        // Redirect with success message
        return redirect()->to($this->crud->route)->with('success', 'Template created and domain setup successfully.');
    }

    public function update(Request $request)
    {
        // Step 1: Validate the form input
        $data = $request->all();

        $template  = $this->crud->getCurrentEntry();

        // setup_progress
        if(empty($data['setup_progress'])) {
            $data['setup_progress'] = null;
        }

        // Domain
        if(empty($template->domain)) {
            $data['domain'] = TemplateRepository::makeTemplateDomain($template);
        }

        // $request->merge($data);

        // Call Backpack’s default update logic
        $template->update($data);

        // Dispatch the TemplateSiteSetupJob
        TemplateSiteSetupJob::dispatch($template);

        // Redirect with success message
        Alert::success('Template updated successfully');
        return redirect()->to($this->crud->route."/{$template->template_id}/show");
    }

    public function handlePublish($id)
    {
        $template = Template::findOrFail($id);
        // $template  = $this->crud->getCurrentEntry();

        $result = TemplateService::publish($template);

        if ($result['status']) {
            Alert::success('Template published successfully. ' . $template->template_id)->flash();
        } else {
            Alert::error(($result['message'] ?? 'Failed to publish. ') . $template->template_id)->flash();
        }

        // Redirect
        return redirect()->to($this->crud->route."/{$id}/show");
    }
}
