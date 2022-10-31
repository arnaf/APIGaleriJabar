<?php

namespace App\Http\Controllers;

use Exception;
use App\User;
use App\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function register(Request $request) {

        $rules = [
            // 'username'          => 'required|unique:users',
            'email'             => 'required|email|unique:users',
            'name'              => 'required',
            'date_birth'        => 'required',
            // 'role'              => 'required', //DISABLE DULU BUAT TESTING KE POSTMAN AJA
            'phone_number'      => 'required',
            'address'           => 'required',
            'district'          => 'required',
            'province'          => 'required',
            'password'          => 'required|min:8',

        ];

        $message = [
            // 'username.required'     => 'Mohon isikan username Anda',
            // 'username.unique'       => 'Username sudah terpakai!',
            'email.required'        => 'Mohon isikan email Anda',
            'email.email'           => 'Mohon isikan email valid',
            'email.unique'          => 'Email sudah terdaftar!',
            'name.required'         => 'Mohon isikan nama Anda',
            'date_birth.required'   => 'Mohon isikan tanggal lahir Anda',
            // 'role.required'         => 'Mohon isikan role Anda', //DISABLE DULU BUAT TESTING KE POSTMAN AJA
            'phone_number.required' => 'Mohon isikan nomor hp Anda',
            'address.required'      => 'Mohon isikan alamat Anda',
            'district.required'     => 'Mohon isikan kabupaten/kota Anda',
            'province.required'     => 'Mohon isikan provinsi Anda',
            'password.required'     => 'Mohon isikan password Anda',
            'password.min'          => 'Password wajib mengandung minimal 8 karakter',
        ];





        $validator = Validator::make($request->all(), $rules, $message);

        if($validator->fails()) {
            return apiResponse(400, 'error', 'Data tidak lengkap ', $validator->errors());
        }

        try {
            DB::transaction(function () use ($request) {
                $id = User::insertGetId([
                    // 'username'      => $request->username,
                    'email'         => $request->email,
                    // 'role'          => $request->role, //DISABLE DULU BUAT TESTING KE POSTMAN AJA
                    'password'      => Hash::make($request->password),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);

                $photo_path = '';

                if ($request->hasFile('photo')) {
                    $photo_path = $request->file('photo')->store('users', 'public'); //ALAMAT STORENYA COBA MAKE SURE DULU
                }

                UserDetail::insert([
                    'user_id'       => $id,
                    'photo'         => $photo_path,
                    'name'          => $request->name,
                    'date_birth'    => $request->date_birth,
                    'phone_number'  => $request->phone_number,
                    'address'       => $request->address,
                    'district'      => $request->district,
                    'province'      => $request->province,
                    'status'        => 'Aktif',
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            });
            return apiResponse(201, 'success', 'user berhasil daftar');
        } catch(Exception $e) {
            return apiResponse(400, 'error', 'error', $e);
        }
    }

    public function login(Request $request) {
        $rules = [
            'email'             => 'required',
            'password'          => 'required|min:8',
        ];

        $message = [
            'email.required' => 'Mohon isikan email anda',
            'password.required' => 'Mohon isikan password anda',
            'password.min'      => 'Password wajib mengandung minimal 8 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if($validator->fails()) {
            return apiResponse(400, 'error', 'Data tidak lengkap ', $validator->errors());
        }

        $data = [
            'email'             => $request->email,
            'password'          => $request->password,
        ];

        if(!Auth::attempt($data)) {
            return apiResponse(400, 'error', 'Data tidak terdaftar, akun bodong nya?');
        }

        $token = Auth::user()->createToken('API Token')->accessToken;

        $data   = [
            'token'     => $token,
            'user'      => Auth::user()->detail,
        ];

        return apiResponse(200, 'success', 'berhasil login', $data);
    }

    public function logout()
    {
        if (Auth::user()) {
            $tokens = Auth::user()->tokens->pluck('id');

            Token::whereIn('id', $tokens)->update([
                'revoked' => true
            ]);
            RefreshToken::whereIn('access_token_id', $tokens)->update([
                'revoked' => true
            ]);
        }
        return apiResponse(200, 'success', 'berhasil logout');
    }
}
