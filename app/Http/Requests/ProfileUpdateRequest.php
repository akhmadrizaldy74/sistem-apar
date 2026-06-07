<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'no_telpon' => [
                'required',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'alamat_maps' => ['nullable', 'string', 'max:1000', 'required_with:alamat_detail'],
            'alamat_detail' => ['nullable', 'string', 'max:1000', 'required_with:alamat_maps'],
            'alamat_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:alamat_maps'],
            'alamat_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:alamat_maps'],
            'alamat_provinsi' => ['nullable', 'string', 'max:255'],
            'alamat_kota' => ['nullable', 'string', 'max:255'],
            'alamat_kecamatan' => ['nullable', 'string', 'max:255'],
            'alamat_kode_pos' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan akun lain.',
            'alamat_maps.required_with' => 'Pilih alamat dari saran peta agar lokasi tersimpan.',
            'alamat_detail.required_with' => 'Detail alamat atau patokan wajib diisi.',
            'alamat_lat.required_with' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran peta.',
            'alamat_lng.required_with' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran peta.',
        ];
    }
}
