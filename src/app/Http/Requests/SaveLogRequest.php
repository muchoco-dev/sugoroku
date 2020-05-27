<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveLogRequest extends FormRequest
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
            'room_id'   => 'required|exists:rooms,id',
            'action_id' => 'required|numeric',
            'effect_id' => 'required|numeric',
            'effect_num'=> 'required|numeric'
        ];
    }
}
