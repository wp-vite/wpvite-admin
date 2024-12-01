<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
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
            'template_uid' => 'required|string|max:20|unique:templates,template_uid',
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:template_categories,category_id',
            'server_id' => 'required|exists:hosting_servers,server_id',
            'status' => 'required|integer|in:0,1,2', // Must be one of the allowed statuses
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
