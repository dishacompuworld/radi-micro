<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Illuminate\Support\Facades\DB;

class SendMail extends Controller
{
    public function mail(){

        $OP = DB::table('opticalpowers')
                    ->get();

        $data = ['name'=>'Rajesh Kothekar', 'data'=>'Hellow Rajesh', 'OP'=>$OP];

        $user['to']= 'rajesh@dishacompuworld.com';
        Mail::send('mail.test-email', $data, function ($message) use ($user) {
            $message->to($user['to']);
            $message->subject('Optical Powers');
        });
    }

    public function sendop(){

        $OP = DB::table('opticalpowers')
                    ->get();

        $data = ['OP'=>$OP];

        $user['to']= auth()->user()->email;
        Mail::send('mail.opticalpowers', $data, function ($message) use ($user) {
            $message->to($user['to']);
            $message->subject('Optical Powers from '. env('MAIL_FROM_NAME', null));
        });

        // return response()->json([
        //     'success' => 'yes',
        //   ]);

          return response()->json(['success' => true]);
    }
}