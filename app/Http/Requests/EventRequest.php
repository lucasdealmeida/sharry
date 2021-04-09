<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'title'      => 'required',
            'content'    => 'required',
            'valid_from' => 'required|date|date_format:Y-m-d H:i:s',
            'valid_to'   => 'required|date|date_format:Y-m-d H:i:s',
            'gps_lat'    => 'required',
            'gps_lng'    => 'required',
        ];
    }
}
