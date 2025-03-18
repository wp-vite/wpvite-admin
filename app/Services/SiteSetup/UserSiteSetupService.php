<?php

namespace App\Services\SiteSetup;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Models\UserSite;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\SiteSetup\SiteSetupService;
use App\Services\Ssh\SshService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserSiteSetupService extends SiteSetupService
{

}
