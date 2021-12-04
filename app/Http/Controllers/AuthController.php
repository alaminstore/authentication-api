<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            try {
                if (Auth::attempt($request->only('email', 'password'))) {
                    /** @var User $user */
                    $user = Auth::user();
                    $token = $user->createToken('ayname')->accessToken;
                    return response([
                        'message' => 'success',
                        'token' => $token,
                        'user' => $user
                    ]);
                }
            } catch (\Exception $e) {
                return response([
                    'message' => $e->getMessage()
                ], 400);
            }

            return response([
                'message' => 'Invalid email or password'
            ], 401);
        }

    }

    public function user()
    {
        return Auth::user();
    }

//    Custom request/validation system's code register() method.
//  public function register(RegisterRequest $request){
//      try {
//          $user = User::create([
//              'first_name'=> $request->input('first_name'),
//              'last_name' => $request->input('last_name'),
//              'email' => $request->input('email'),
//              'password' => Hash::make($request->input('password')),
//          ]);
//          return $user;
//      }catch (\Exception $exception){
//          return response([
//              'message' => $exception->getMessage()
//          ],400);
//      }
//  }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $user = User::create([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);
            return $user;
        }


    }

}
