<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Form request validasi untuk pembuatan dan pembaruan data pengguna.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi validasi nama, email unik, dan password pengguna.
 */

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        #yang diambil adalah parameter {user} dari route, jadi gunakan $this->route('user').
        $userID = $this->route('user') ? $this->route('user')->id : null;
        return [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $userID,
            'password' => $this->isMethod('post') ? 'required|min:6' : 'nullable|min:6',
        ];
    }
}
