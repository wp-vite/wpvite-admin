<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_uid' => 'required|string|max:20|unique:user_sites,site_uid',
            'user_id' => 'required|exists:users,id',
            'template_id' => 'required|exists:templates,template_id',
            'server_id' => 'required|exists:hosting_servers,server_id',
            'domain' => 'required|string|unique:user_sites,domain|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'status' => 'required|integer|in:0,1,3', // Must be one of the allowed statuses
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
