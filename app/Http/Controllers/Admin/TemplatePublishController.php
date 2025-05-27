<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplatePreview;
use App\Services\Template\TemplateService;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

class TemplatePublishController extends CrudController
{
    public function getPublish(Template $template)
    {
        $this->data['breadcrumbs'] = [
            'Admin' => backpack_url('dashboard'),
            'Templates' => route('template.index'),
            $template->title => route('template.show', $template->template_id),
            'Publish' => false,
        ];

        $this->data['template']     = $template;
        $this->data['newVersion']   = TemplateService::calculateNextTemplateVersion($template);

        return view('admin.templates.publish', $this->data);
    }

    public function publish(Request $request, Template $template)
    {
        $validated = $request->validate([
            'new_version' => 'required|numeric|min:1|regex:/^\d+(\.\d{1,2})?$/',
            'previews' => 'required|array|min:1',
            'previews.*.title' => 'required|string|max:255',
            'previews.*.screenshot' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'override_version' => 'boolean',
        ], [
            'new_version.regex' => 'Version must be in format X.Y or X.YY (e.g. 1.0 or 1.01)',
            'previews.*.screenshot.mimes' => 'Screenshot must be a JPEG, PNG or JPG image',
            'previews.*.screenshot.max' => 'Screenshot size must not exceed 2MB',
        ]);

        try {
            $result = TemplateService::publish($template, $validated);

            if ($result['status']) {
                Alert::success('Template published successfully. ' . $template->template_id)->flash();
                return redirect()->route('template.show', $template->template_id);
            } else {
                Alert::error(($result['message'] ?? 'Failed to publish. ') . $template->template_id)->flash();
            }
        } catch (\Exception $e) {
            Alert::error('An unexpected error occurred: ' . $e->getMessage())->flash();
        }

        return redirect()->route('template.publish', $template->template_id);
    }
}
