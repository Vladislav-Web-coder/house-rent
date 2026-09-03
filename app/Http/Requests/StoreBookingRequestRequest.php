<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'phone:INTERNATIONAL'],
            'country_code' => ['required', 'string', 'max:10'],
            'guest_email' => ['required', 'email', 'max:255'],
            'contact_method' => ['required', 'string', 'in:telegram,whatsapp,max,phone'],
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:10'],
            'children' => ['required', 'integer', 'min:0', 'max:10'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
    public function messages(): array
    {
        return [
            'check_out.after' => 'Дата выезда должна быть позже даты заезда.',
            'check_in.after_or_equal' => 'Дата заезда не может быть в прошлом.',
            'guest_phone.phone' => 'Введите корректный номер телефона для выбранной страны',
            'guest_email.email' => 'Введите корректный email',
            'contact_method.in' => 'Выберите допустимый способ связи',
        ];
    }
}
