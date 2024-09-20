<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\TestMail;
use Mail;

class MailController extends Controller
{
    public function index()
    {
        Mail::to('thabanimat24@gmail.com')->send(new TestMail([
            'title' => 'The Title',
            'body' => 'The Body',
        ]));
    }
}
