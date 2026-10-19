<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\WaLog;
use App\Models\User;
use App\Models\Pic;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TechnicianReportController;
use App\Http\Controllers\MasterDashboardController;
use App\Http\Middleware\TeknisiOnlySemarang;
use App\Exports\DashboardSheetsExport;
use App\Exports\WaDashboardSheetsExport;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class,'loginForm'])->name('login');
// RATE LIMIT: maksimal 10 percobaan login per menit per IP
Route::post('/login', [AuthController::class,'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class,'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/

// RATE LIMIT UMUM: 'throttle:60,1' = maksimal 60 request per menit per user
Route::middleware(['auth', TeknisiOnlySemarang::class, 'throttle:60,1'])->group(function () {

    /*
    =====================
    DASHBOARD
    =====================
    */

Route::get('/dashboard/export', function (Request $request) {

        $filename = 'Email_Dashboard_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new DashboardSheetsExport($request->from, $request->to),
            $filename
        );
    })->middleware('throttle:5,1')->name('dashboard.export'); // export berat: 5x/menit

   Route::get('/dashboard', function (Request $request) {

    $query = EmailLog::query();

    if ($request->from && $request->to) {
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }

    $totalEmails = $query->count();

    // ✅ PAGINATION
    $recentEmails = $query->latest()->paginate(10);

    // ✅ AGAR FILTER TETAP SAAT PINDAH HALAMAN
    $recentEmails->appends($request->all());

    $totalTemplates = EmailTemplate::count();

    return view('dashboard', compact(
        'totalEmails',
        'totalTemplates',
        'recentEmails'
    ));
});

    /*
    =====================
    DASHBOARD (Rekap Case per Teknisi)
    =====================
    */
    Route::get('/technician-dashboard', [TechnicianReportController::class, 'dashboard']);

    /*
    =====================
    DASHBOARD MASTER (BARU — dikerjakan bertahap, lihat komentar di
    MasterDashboardController untuk daftar 10 sub-poin & progressnya.)
    =====================
    */
    Route::get('/dashboard-master', [MasterDashboardController::class, 'index']);

    /*
    =====================
    BC EMAIL (dialihfungsikan jadi Arsip & Pencarian Close Case + Reminder TAT Otomatis)
    =====================
    */
    Route::get('/bc-email', [TechnicianReportController::class, 'archive']);
    Route::post('/bc-email/reminder/toggle', [TechnicianReportController::class, 'toggleReminder']);
    Route::post('/bc-email/reminder/send-now', [TechnicianReportController::class, 'sendReminderNow'])->middleware('throttle:5,1');

    /*
    =====================
    UPLOAD (Upload Excel + History Upload Batch)
    =====================
    */
    Route::get('/upload', [TechnicianReportController::class, 'index']);
    Route::post('/upload/import', [TechnicianReportController::class, 'store'])->middleware('throttle:5,1');

    /*
    =====================
    CREATE EMAIL
    =====================
    */
    Route::get('/create-email', function () {
    $templates = EmailTemplate::all();
    $technicians = \App\Models\Technician::all();
    $pics = Pic::all(); // ✅ TAMBAH INI

    // ✅ BARU: daftar email customer yang PERNAH tercatat di sistem
    // (bukan riwayat browser), buat saran autocomplete saat mengetik.
    $knownEmails = EmailLog::select('customer_email')
        ->whereNotNull('customer_email')
        ->distinct()
        ->orderBy('customer_email')
        ->pluck('customer_email');

    return view('create-email', compact('templates','technicians','pics','knownEmails'));
});

    Route::get('/get-template-fields/{id}', function ($id) {
        $template = EmailTemplate::find($id);
        return response()->json([
            'fields' => json_decode($template->required_fields)
        ]);
    });

    /*
    =====================
    WA DASHBOARD
    =====================
    */
      Route::get('/wa-dashboard/export', function (Request $request) {

        $filename = 'WhatsApp_Dashboard_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new WaDashboardSheetsExport($request->from, $request->to),
            $filename
        );
    })->middleware('throttle:5,1')->name('wa.dashboard.export'); // export berat: 5x/menit
    Route::get('/wa-dashboard', function (Request $request) {

    $query = WaLog::query();

    if($request->from && $request->to){
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }

    $totalWa = $query->count();

    // ✅ PAGINATION
    $recentWa = $query->latest()->paginate(10);

    // ✅ BIAR FILTER TETAP
    $recentWa->appends($request->all());

    return view('wa-dashboard', compact('totalWa','recentWa'));
});

    /*
    =====================
    TEST EMAIL
    =====================
    */
    Route::get('/test-email', function () {

        Mail::raw('Test Email Laravel CRM', function ($message) {
            $message->to('emailtujuan@gmail.com')
                    ->subject('Test Email');
        });

        return 'Email Sent';
    })->middleware('throttle:2,1'); // kirim email: 2x/menit

    /*
    =====================
    SEND EMAIL + WA
    =====================
    */
    Route::post('/send-email', function (Request $request) {

        $template = EmailTemplate::find($request->template_id);

        if(!$template){
            return back()->with('error','Template tidak ditemukan');
        }

        $channels = $request->channels ?? [];

        if(empty($channels)){
            return back()->with('error','Pilih metode pengiriman');
        }

        $data = $request->all();
        unset($data['channels']);

        $waBody = $template->wa_body ?? '';
        
        $data['sender_name'] = "HP Service Center Semarang";
        $data['sender_position'] = "Customer Service";
        $data['company_name'] = "PT. Harmoni Putra Solusindo";
        $data['sender_phone'] = "085747009467";

        $subject = $template->subject;
        $body = $template->body;

        foreach($data as $key => $value){
            if(is_array($value)) continue;

            $subject = str_replace('{{'.$key.'}}', $value, $subject);
            $body = str_replace('{{'.$key.'}}', $value, $body);
            $waBody = str_replace('{{'.$key.'}}', $value, $waBody);
        }

        // EMAIL
        if(in_array('email', $channels)){
            Mail::send([], [], function ($message) use ($request, $subject, $body) {

                $logoPath = public_path('images/signiture.png');
                $cid = $message->embed($logoPath);

                $finalBody = str_replace('{{company_logo}}', $cid, $body);

                $message->to($request->customer_email)
                        ->bcc([
                            'cs@harmoniputra.com',
                            'laporan@harmoniputra.com',
                            'fransisca@harmoniputra.com'
                        ])
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->replyTo('custcare@harmoniputra.com', 'Customer Care')
                        ->html($finalBody);

                // ✅ ATTACH FILE
            if ($request->hasFile('attachment')) {
             $file = $request->file('attachment');

                $message->attach(
                 $file->getRealPath(),
                 [
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType()
                 ]
        );
    }

});
        }

        // WHATSAPP
        if(in_array('wa', $channels)){

    // kirim ke customer utama
    $mainPhone = preg_replace('/[^0-9]/', '', $request->customer_phone);

    if (substr($mainPhone, 0, 1) == '0') {
        $mainPhone = '62' . substr($mainPhone, 1);
    }

    $response = Http::withHeaders([
        'x-api-key' => '123456'
    ])->post('http://localhost:2026/send', [
        'nomor' => $mainPhone,
        'pesan' => $waBody
    ]);

    // kirim ke BCC (multi nomor)
    $bccPhones = $request->input('bcc_phones', []);

    foreach ($bccPhones as $phone) {

        if(empty($phone)) continue;

        // bersihkan format nomor
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }

        $bccResponse = Http::withHeaders([
            'x-api-key' => '123456'
        ])->post('http://localhost:2026/send', [
            'nomor' => $phone,
            'pesan' => "[COPY] ".$waBody
        ]);

        // log kalau gagal
        if (!$bccResponse->successful()) {
            \Log::error('WA BCC gagal', [
                'phone' => $phone,
                'response' => $bccResponse->body()
            ]);
        }

        sleep(1); // hindari spam / limit
    }

    WaLog::create([
        'pic' => $request->pic,
        'customer_name' => $request->customer_name,
        'customer_phone' => $mainPhone,
        'case_id' => $request->workorder,
        'message' => $waBody,
        'status' => $response->successful() ? 'sent' : 'failed'
    ]);
}


        EmailLog::create([
            'pic' => $request->pic,
            'customer_name' => $request->customer_name ?? '-',
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'case_id' => $request->workorder,
            'subject' => $subject,
        ]);

        return redirect('/dashboard')->with('success','Pesan berhasil dikirim');

    })->middleware('throttle:10,1'); // kirim email/WA: 10x/menit


    /*
    =====================
    👑 ADMIN ONLY (CS0)
    =====================
    */

    Route::get('/users', function () {
        if(auth()->user()->role != 'admin') abort(403);

        $users = User::all();
        return view('users', compact('users'));
    });

    Route::get('/users/create', function () {
        if(auth()->user()->role != 'admin') abort(403);

        return view('create-user');
    });

    Route::post('/users/store', function (Request $request) {
        if(auth()->user()->role != 'admin') abort(403);

        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,cs,teknisi',
            'technician_id' => 'required_if:role,teknisi|nullable|exists:technicians,id|unique:users,technician_id',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email, // ✅ WAJIB
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'technician_id' => $request->role === 'teknisi' ? $request->technician_id : null,
        ]);

        return redirect('/users')->with('success','User ditambahkan');
    });

    Route::get('/users/edit/{id}', function ($id) {
        if(auth()->user()->role != 'admin') abort(403);

        $user = User::findOrFail($id);
        return view('edit-user', compact('user'));
    });

    Route::post('/users/update/{id}', function (Request $request, $id) {
        if(auth()->user()->role != 'admin') abort(403);

        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'role' => 'required|in:admin,cs,teknisi',
            'technician_id' => 'required_if:role,teknisi|nullable|exists:technicians,id|unique:users,technician_id,' . $id,
        ]);

        $user = User::findOrFail($id);

        $user->username = $request->username;
        $user->role = $request->role;
        $user->technician_id = $request->role === 'teknisi' ? $request->technician_id : null;

        if($request->password){
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect('/users')->with('success','User diupdate');
    });

    Route::get('/users/delete/{id}', function ($id) {
        if(auth()->user()->role != 'admin') abort(403);

        if($id == auth()->id()){
            return back()->with('error','Tidak bisa hapus diri sendiri');
        }

        User::findOrFail($id)->delete();

        return redirect('/users')->with('success','User dihapus');
    });
    
    // Tamplate
    Route::get('/templates', function () {
    $templates = EmailTemplate::all();
    return view('templates', compact('templates'));
});

Route::get('/templates/edit/{id}', function ($id) {
    $template = EmailTemplate::findOrFail($id);
    return view('edit-template', compact('template'));
});

Route::post('/templates/update/{id}', function (Request $request, $id) {
    $template = EmailTemplate::findOrFail($id);

    $template->name = $request->name;
    $template->subject = $request->subject;
    $template->body = $request->body;
    $template->wa_body = $request->wa_body;

    $template->save();

    return redirect('/templates')->with('success','Template berhasil diupdate');
});
// FORM TAMBAH
Route::get('/templates/create', function () {
    return view('create-template');
});

// SIMPAN DATA BARU
Route::post('/templates/store', function (Request $request) {

    $request->validate([
        'subject' => 'required',
        'body' => 'required'
    ]);

    EmailTemplate::create([
        'subject' => $request->subject,
        'body' => $request->body,
        'wa_body' => $request->wa_body,
    ]);

    return redirect('/templates')->with('success','Template berhasil ditambahkan');
});

// HAPUS
Route::get('/templates/delete/{id}', function ($id) {
    EmailTemplate::findOrFail($id)->delete();

    return redirect('/templates')->with('success','Template berhasil dihapus');
});

    // Preview
    Route::post('/preview-email', function (Request $request) {

    $template = EmailTemplate::find($request->template_id);

    $data = $request->all();

    $subject = $template->subject;
    $body = $template->body;
    $waBody = $template->wa_body ?? '';

    foreach($data as $key => $value){
        if(is_array($value)) continue;

        $subject = str_replace('{{'.$key.'}}', $value, $subject);
        $body = str_replace('{{'.$key.'}}', $value, $body);
        $waBody = str_replace('{{'.$key.'}}', $value, $waBody);
    }

    return view('preview-email', compact('subject','body','waBody'));
});
// LIST TEKNISI
Route::get('/technicians', function () {
    $technicians = \App\Models\Technician::all();
    return view('technicians', compact('technicians'));
});

// FORM TAMBAH
Route::get('/technicians/create', function () {
    return view('create-technician');
});

// SIMPAN
Route::post('/technicians/store', function (Request $request) {

    $request->validate([
        'name' => 'required',
        'phone' => 'required'
    ]);

    \App\Models\Technician::create([
        'name' => $request->name,
        'phone' => $request->phone
    ]);

    return redirect('/technicians')->with('success','Teknisi berhasil ditambahkan');
});

// HAPUS
Route::get('/technicians/delete/{id}', function ($id) {
    \App\Models\Technician::findOrFail($id)->delete();

    return redirect('/technicians')->with('success','Teknisi dihapus');
});
// FORM EDIT
Route::get('/technicians/edit/{id}', function ($id) {
    $technician = \App\Models\Technician::findOrFail($id);
    return view('edit-technician', compact('technician'));
});

// UPDATE DATA
Route::post('/technicians/update/{id}', function (Request $request, $id) {

    $request->validate([
        'name' => 'required',
        'phone' => 'required'
    ]);

    $technician = \App\Models\Technician::findOrFail($id);

    $technician->name = $request->name;
    $technician->phone = $request->phone;
    $technician->save();

    return redirect('/technicians')->with('success','Teknisi berhasil diupdate');
});

    // pic
// LIST
Route::get('/pics', function () {
    $pics = Pic::all();
    return view('pics', compact('pics'));
});

// FORM TAMBAH
Route::get('/pics/create', function () {
    return view('create-pic');
});

// SIMPAN
Route::post('/pics/store', function (Request $request) {

    $request->validate([
        'name' => 'required'
    ]);

    Pic::create([
        'name' => $request->name
    ]);

    return redirect('/pics')->with('success','PIC ditambahkan');
});

// EDIT
Route::get('/pics/edit/{id}', function ($id) {
    $pic = Pic::findOrFail($id);
    return view('edit-pic', compact('pic'));
});

// UPDATE
Route::post('/pics/update/{id}', function (Request $request, $id) {

    $pic = Pic::findOrFail($id);

    $pic->name = $request->name;
    $pic->save();

    return redirect('/pics')->with('success','PIC diupdate');
});

// HAPUS
Route::get('/pics/delete/{id}', function ($id) {
    Pic::findOrFail($id)->delete();

    return redirect('/pics')->with('success','PIC dihapus');
});

    /*
    =====================
    ✅ BARU: BACKUP HISTORY (hasil backup:export-daily)
    =====================
    */
    Route::get('/backup-history', function (Request $request) {

        $baseDir = 'backups';

        // ★★★ BARU: filter Cabang. File pending_case/close_case sekarang
        // dibackup terpisah per cabang (lihat DailyBackupExport), dengan
        // nama file seperti "pending_case-Semarang-2026-09-12_170003.csv".
        // Kalau cabang dipilih, cuma file yang namanya mengandung
        // "-{cabang}-" yang ditampilkan. File yang TIDAK punya info cabang
        // di namanya (email_logs, wa_logs — tidak dipecah per cabang)
        // TETAP selalu tampil apa pun cabang yang dipilih, karena memang
        // bukan data per-cabang.
        $cabang = $request->input('cabang');

        $folders = collect(Storage::disk('local')->directories($baseDir))
            ->sortDesc()
            ->values()
            ->map(function ($folder) use ($cabang) {
                $date = basename($folder);

                $files = collect(Storage::disk('local')->files($folder))
                    ->sortDesc()
                    ->map(function ($f) {
                        return [
                            'name' => basename($f),
                            'size' => Storage::disk('local')->size($f),
                            'path' => $f,
                        ];
                    })
                    ->filter(function ($file) use ($cabang) {
                        if (!$cabang) {
                            return true;
                        }

                        // File per-cabang (pending_case/close_case) namanya
                        // mengandung "-Semarang-" / "-Cirebon-" / dst.
                        // File tanpa info cabang (email_logs, wa_logs) selalu
                        // ikut ditampilkan, tidak ikut difilter.
                        $isPerCabangFile = (bool) preg_match('/-(Semarang|Cirebon|TanpaCabang)-/', $file['name']);

                        if (!$isPerCabangFile) {
                            return true;
                        }

                        return str_contains($file['name'], "-{$cabang}-");
                    })
                    ->values();

                return ['date' => $date, 'files' => $files];
            });

        return view('backup-history', compact('folders', 'cabang'));
    });

    Route::get('/backup-history/download', function (Request $request) {

        $path = $request->query('path');

        // ✅ VALIDASI KEAMANAN: path WAJIB di dalam folder backups/ saja,
        // supaya tidak bisa dipakai untuk mengunduh file lain di server.
        if (!$path || !str_starts_with($path, 'backups/') || str_contains($path, '..')) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    })->middleware('throttle:20,1'); // download backup: 20x/menit
});