<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackingFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'firstname' => 'required|max:255',
            'secondname' => 'required|max:255',
            'addname'   => 'required|max:255',
            'district' => 'max:32',
            'city' => 'max:30',
            'address' => 'required',
        ];
    }
}