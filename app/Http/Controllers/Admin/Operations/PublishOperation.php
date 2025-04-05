<?php

namespace App\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

trait PublishOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupPublishRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/{id}/publish', [
            'as'        => $routeName.'.publish',
            'uses'      => $controller.'@publish',
            'operation' => 'publish',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupPublishDefaults()
    {
        CRUD::allowAccess('publish');

        CRUD::operation('publish', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
        });

        CRUD::operation('show', function () {
            // CRUD::addButton('top', 'publish', 'view', 'crud::buttons.publish');
            CRUD::addButton('line', 'publish', 'view', 'crud::buttons.publish');
        });
    }

    /**
     * Show the view for performing the operation.
     *
     * @return mixed
     */
    public function publish($id)
    {
        CRUD::hasAccessOrFail('publish');

        // // prepare the fields you need to show
        // $this->data['crud'] = $this->crud;
        // $this->data['title'] = CRUD::getTitle() ?? 'Publish '.$this->crud->entity_name;

        // // load the view
        // return view('crud::operations.publish', $this->data);

        if (method_exists($this, 'handlePublish')) {
            return $this->handlePublish($id);
        }

        abort(501, 'Publish operation not implemented.');
    }
}
