<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function showID($id)
    {
        $user = User::with('userDetails')->where('id', $id)->first();

        if ($user) {
            return apiResponse('200', 'success', 'data user berhasil ditemukan', $user);
        }

        return Response::json(apiResponse(404, 'not found', 'User tidak ditemukan :('), 404);
    }

    public function indexAll()
    {
        $user = User::get();

        return Response::json(apiResponse(200, 'True','list data user', $user));
    }

    public function updateUserDetail(Request $request, $id) {
        if ($request->hasFile('image')) {
            // Delete old image
            if ($request->photo) {
                Storage::delete($request->photo);
            }
            // Store image
            $photo_path = $request->file('photo')->store('users', 'public');
            // Save to Database
            $request->photo = $photo_path; //ALAMAT STORENYA COBA MAKE SURE DULU DAN LOGICNYA COBA DITELITI LAGI BENER GA
        }

        $rules = [
            // 'username'      => 'required',
            // 'password'      => 'required|min:8',
            // 'role' => 'required', //DISABLE DULU BUAT TESTING KE POSTMAN AJA
            'name'          => 'required',
            'address'       => 'required',
            'district'      => 'required',
            'province'      => 'required',
            'phone_number'  => 'required',
            'status'        => 'required',
        ];

        $message = [
            // 'username.required'     => 'Mohon isikan username anda',
            // 'password.required'     => 'Mohon isikan password anda',
            // 'password.min'          => 'Password wajib mengandung minimal 8 karakter',
            //'role.required'         => 'Mohon isikan role Anda',//DISABLE DULU BUAT TESTING KE POSTMAN AJA
            'name.required'         => 'Mohon isikan nama anda',
            'address.required'      => 'Mohon isikan alamat anda',
            'district.required'     => 'Mohon isikan kabupaten/kota anda',
            'province.required'     => 'Mohon isikan provinsi anda',
            'phone_number.required' => 'Mohon isikan nomor HP anda',
            'status.required'       => 'Mohon isikan status approval seniman',
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if($validator->fails()) {
            return apiResponse(400, 'error', 'Data tidak lengkap ', $validator->errors());
        }

        try {
            DB::transaction(function () use ($request, $id) {
                User::where('id', $id)->update([
                    'username'  => $request->name,
                    // 'password' => Hash::make($request->password),
                    // 'role' => $request->role,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                UserDetail::where('user_id', $id)->update([

                    'user_id'       => $id,
                    'photo'         => $request->photo, //COBA LOGICNYA DIBENERIN LAGI
                    'name'          => $request->name,
                    'date_birth'    => $request->date_birth,
                    'phone_number'  => $request->phone_number,
                    'address'       => $request->address,
                    'district'      => $request->district,
                    'province'      => $request->province,
                    // 'photo'         => $request->user_photo,
                    // 'status'        => $request->status,
                    'updated_at'    => date('Y-m-d H:i:s')


                ]);
            });

            return apiResponse(202, 'success', 'user berhasil disunting');
        } catch(Exception $e) {
            return apiResponse(400, 'error', 'error', $e);
        }
    }

    public function updatePassword(Request $request, $id) {
        $rules = [
            'password' => 'required|min:8'
        ];

        $message = [
            'password.required'     => 'Mohon isikan password anda',
            'password.min'          => 'Password wajib mengandung minimal 8 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if($validator->fails()) {
            return apiResponse(400, 'error', 'Password tidak diubah', $validator->errors());
        }

        try {
            DB::transaction(function () use ($request, $id) {
                User::where('id', $id)->update([
                    'password' => Hash::make($request->password),

                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    }


    public function destroy($id) {
        try {
            DB::transaction(function () use ($id) {
                UserDetail::where('user_id', $id)->delete();
                User::where('id', $id)->delete();
            });

            return apiResponse(202, 'success', 'user berhasil dihapus :(');
        } catch(Exception $e) {
            return apiResponse(400, 'error', 'error', $e);
        }
    }
}
