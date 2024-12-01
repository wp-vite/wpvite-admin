<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HostingServerRequest extends FormRequest
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
            'name' => 'required|string|max:50',
            'provider' => 'required|string|max:20',
            'instance_type' => 'required|string|max:30',
            'public_ip' => 'required|string|max:45',
            'private_ip' => 'nullable|string|max:45',
            'instance_id' => 'nullable|string|max:100',
            'virtualmin_url' => 'required|url:http,https',
            'max_sites' => 'required|numeric|max:255',
            'cpu' => 'required|numeric|max:255',
            'ram' => 'required|numeric|max:255',
            'disk_size' => 'required|numeric|max:300',
            'status' => 'required|in:0,1,2',
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
