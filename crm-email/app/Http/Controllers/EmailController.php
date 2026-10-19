<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use Mail;

class EmailController extends Controller
{
    public function send(Request $request)
    {
        $template = EmailTemplate::find($request->template_id);

        $data = $request->all();

        // Data permanen perusahaan
        $data['sender_name'] = "Syifa Catur";
        $data['sender_position'] = "Customer Service";
        $data['company_name'] = "Harmoni Putra";
        $data['sender_phone'] = "08123456789";

        $subject = $template->subject;
        $body = $template->body;

        foreach($data as $key => $value){
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
            $body = str_replace('{{'.$key.'}}', $value, $body);
        }

        Mail::send([], [], function ($message) use ($request, $subject, $body) {
            $message->to($request->customer_email)
                    ->subject($subject)
                    ->html($body);
        });

        EmailLog::create([
            'customer_name' => $request->customer_name ?? null,
            'customer_email' => $request->customer_email,
            'subject' => $subject,
            'body'  => $body,
        ]);

        return redirect('/dashboard');
    }
}