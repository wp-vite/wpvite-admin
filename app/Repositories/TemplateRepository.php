<?php
namespace App\Repositories;

use App\Models\Template;
use Illuminate\Support\Facades\Config;

class TemplateRepository
{
    protected $model;

    public function __construct(Template $model)
    {
        $this->model = $model;
    }

    public static function makeTemplateDomain(Template $template)
    {
        return $template->template_id .'.'. Config::get('wpvite.root_domain');
    }
}
