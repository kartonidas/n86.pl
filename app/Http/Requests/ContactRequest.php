<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "firstname" => "required|max:100",
            "lastname" => "required|max:100",
            "email" => "required|email",
            "message" => "required|max:2000",
            "g-recaptcha-response" => "required",
        ];
       
        return $rules;
    }
    
    public function messages(): array
    {
        return [
            "firstname.required" => "Uzupełnij imię",
            "firstname.max" => "Maksymalna długość w polu imię: :max znaków",
            "lastname.required" => "Uzupełnij nazwisko",
            "lastname.max" => "Maksymalna długość w polu nazwisko: :max znaków",
            "email.required" => "Uzupełnij adres e-mail",
            "email.email" => "Nieprawidłowy adres e-mail",
            "message.required" => "Uzupełnij wiadomość",
            "message.max" => "Maksymalna długość w polu wiadomość: :max znaków",
            "g-recaptcha-response.required" => "Udowodnij, że nie jesteś robotem",
        ];
    }
}
