<?php

return [
    'accepted' => ':attribute harus diterima.',
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'array' => ':attribute harus berupa daftar.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'digits_between' => ':attribute harus terdiri dari :min sampai :max angka.',
    'digits' => ':attribute harus terdiri dari :digits angka.',
    'in' => 'Pilihan :attribute tidak valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file' => ':attribute tidak boleh lebih dari :max kilobita.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'file' => ':attribute minimal :min kilobita.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute harus memiliki minimal :min item.',
    ],
    'password' => 'Kata sandi salah.',
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'custom' => [
        // PRD KP-02 dan KP-17. unique pada kolom "email" hanya dipakai untuk akun, pada "name" hanya untuk kategori.
        'email' => ['unique' => 'Email sudah terdaftar.'],
        'name' => ['unique' => 'Nama kategori sudah dipakai.'],
    ],
    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'phone' => 'nomor telepon',
        'address' => 'alamat',
        'code' => 'kode unit',
        'description' => 'deskripsi',
        'category_ids' => 'kategori',
        'category_ids.*' => 'kategori',
    ],
];
