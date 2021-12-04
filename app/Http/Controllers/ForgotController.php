<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotRequest;
use App\Models\User;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ForgotController extends Controller
{
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $email = $request->input('email');
            if (User::where('email', $email)->doesntExist()) {
                return response([
                    'message' => 'User doesn\'t exists'
                ]);
            }
            $token = Str::random(20);
            try {
                DB::table('password_resets')->insert([
                    'email' => $email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);
                //send Mail
                //Here forgotmail is a view file name, token send in view(with send mail), and callback function
                Mail::send('Mails.forgotmail', ['token' => $token], function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('Reset Your Password');
                });
                return response([
                    'message' => 'Check your email'
                ]);
            } catch (\Exception $e) {
                return response([
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $token = $request->input('token');
            if (!$passwordResets = DB::table('password_resets')->where('token', $token)->first()) {
                return response([
                    'message' => 'Invalid Token!'
                ], 400);
            }
            /** @var User $user */
            if (!$user = User::where('email',$passwordResets->email)->first()){
                return response([
                    'message' => 'User Doesn\'t Exist'
                ], 404);
            }
            $user->password = Hash::make($request->input('password'));
            $user->save();
            return response([
                'message' => 'Password reset successfully!'
            ]);
        }
    }
}















