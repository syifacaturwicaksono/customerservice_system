@extends('layouts.admin')

@section('content')

<h2 class="mb-4">✉️ Create Message To Customer</h2>
<div class="card shadow-sm border-0">
    <div class="card-body">

        <form method="POST" action="/send-email" enctype="multipart/form-data" autocomplete="off">
            @csrf

            <div class="mb-3">
                <label class="form-label">Jenis Pesan</label>
                <select autocomplete="off" name="template_id" class="form-select" required>
                    <option value="">-- Pilih jenis pesan --</option>
                    @foreach($templates as $template)
                    <option value="{{ $template->id }}">
                        {{ $template->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Nama PIC</label>
                <select autocomplete="off" name="pic" class="form-control" required>
    <option value="">-- Pilih PIC --</option>
    @foreach($pics as $p)
        <option value="{{ $p->name }}">
            {{ $p->name }}
        </option>
    @endforeach
</select>
            </div>
            <div class="mb-3">
                <label class="form-label">Customer Email</label>
                <input autocomplete="off" list="knownEmailsList" type="email" name="customer_email" class="form-control" required>
                <datalist id="knownEmailsList">
                    @foreach($knownEmails as $email)
                        <option value="{{ $email }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div id="dynamicFields"></div>

            
        
            <div class="mb-3">
                <label class="form-label">Kirim Melalui</label><br>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                        name="channels[]" value="email" id="emailCheck">
                    <label class="form-check-label" for="emailCheck">
                        Email
                    </label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                        name="channels[]" value="wa" id="waCheck">
                    <label class="form-check-label" for="waCheck">
                        WhatsApp
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                🚀 Send Email
            </button>
            <button type="submit" formaction="/preview-email" formtarget="_blank" class="btn btn-info">
                👁️ Preview
            </button>

        </form>
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const templateSelect = document.querySelector("select[name='template_id']");
                const dynamicFields = document.getElementById("dynamicFields");

                templateSelect.addEventListener("change", function() {

                    const selectedText = templateSelect.options[templateSelect.selectedIndex].text;

                    let html = "";

                    if (selectedText === "[1001] Service Confirmation - Konfirmasi Layanan") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>

        <div class="mb-3">
                    <label>Serial Number</label>
                    <input autocomplete="off" type="text" name="serialnumber" class="form-control" required>
                </div>

        <div class="mb-3">
            <label>ETA</label>
            <input autocomplete="off" type="date" name="eta" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Tanggal Layanan 1</label>
            <input autocomplete="off" type="date" name="service_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Layanan 2</label>
            <input autocomplete="off" type="date" name="service_date1" class="form-control" required>
        </div>

        
    `;
                    }

                    if (selectedText === "[1002] Part Delay Notification - Notifikasi Keterlambatan Part") {
                        html = `
                <div class="mb-3">
                    <label>Customer Name</label>
                    <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
                </div>
                 
                <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Serial Number</label>
                    <input autocomplete="off" type="text" name="serialnumber" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Part Kosong</label>
                    <input autocomplete="off" type="text" name="part_kosong" class="form-control" required>
                </div>
            `;
                    }


                    if (selectedText === "[1005] Follow Up & Penutupan Kasus") {
                        html = `
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
          
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
        <div class="mb-3">
                    <label>Serial Number</label>
                    <input autocomplete="off" type="text" name="serialnumber" class="form-control" required>
                </div>

    `;
                    }

                    if (selectedText === "[1004] Service Reminder - Appointment") {
                        html = `
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
          
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
         <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
        <div class="mb-3">
                    <label>Serial Number</label>
                    <input autocomplete="off" type="text" name="serialnumber" class="form-control" required>
                </div>
        <div class="mb-3">
    <label>Teknisi</label>
    <select autocomplete="off" name="technician_id" id="technician" class="form-control">
        <option value="">-- Pilih Teknisi --</option>
        @foreach($technicians as $t)
           <option value="{{ $t->name }}" data-phone="{{ $t->phone }}">
                {{ $t->name }}
            </option>
        @endforeach
    </select>
</div>
    <div class="mb-3">
    <label>No Teknisi</label>
        <div id="bcc-container">
    <input autocomplete="off" type="text" name="bcc_phones[]" id="technician_phone" class="form-control mb-2>
        </div>
     </div>

        <div class="mb-3">
            <label>Tanggal Janji Temu</label>
            <input autocomplete="off" type="date" name="appointment_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Waktu Janji Temu</label>
            <input autocomplete="off" type="time" name="appointment_time" class="form-control" required>
        </div>

    `;
                    }
                    if (selectedText === "[1003] Confirm Visit Schedule - Konfirmasi Jadwal Kunjungan") {
                        html = `
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
          
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div> 
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
        <div class="mb-3">
                    <label>Serial Number</label>
                    <input autocomplete="off" type="text" name="serialnumber" class="form-control" required>
                </div> 
        <div class="mb-3">
            <label>Teknisi</label>
            <select autocomplete="off" name="technician_id" id="technician" class="form-control">
            <option value="">-- Pilih Teknisi --</option>
            @foreach($technicians as $t)
            <option value="{{ $t->name }}" data-phone="{{ $t->phone }}">
                {{ $t->name }}
            </option>
            @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label>Tanggal Janji Temu</label>
            <input autocomplete="off" type="date" name="appointment_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Waktu Janji Temu</label>
            <input autocomplete="off" type="time" name="appointment_time" class="form-control" required>
        </div>

         
    `;
                    }
                    if (selectedText === "[1006] Finish Repair Bench") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
       
    `;
                    }
                    if (selectedText === "[1007] Finish Repair ARC") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>

        <div class="mb-3">
            <label>Supplies</label>
            <input autocomplete="off" type="text" name="supplies" class="form-control" required>
        </div>
               
    `;
                    }
                    if (selectedText === "[1008] Repair Progress Bench") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>

    `;
                    }
                    if (selectedText === "[1009] Part Order Bench") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Part</label>
                    <input autocomplete="off" type="text" name="part" class="form-control" required placeholder="LCD,Mainboard,SSD">
                </div>

    `;
                    }

                    if (selectedText === "[1010] Part Ready") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Part</label>
                    <input autocomplete="off" type="text" name="part" class="form-control" required placeholder="Keyboard">
                </div>

    `;
                    }

                    if (selectedText === "[1011] Part Request") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Note Teknisi</label>
                    <input autocomplete="off" type="text" name="technician_note" class="form-control" required placeholder="beberapa tombol keyboard tidak berfungsi">
                </div>
                <div class="mb-3">
                    <label>Part</label>
                    <input autocomplete="off" type="text" name="part" class="form-control" required placeholder="Keyboard">
                </div>

    `;
                    }

                    if (selectedText === "[1012] Escalation") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>

    `;
                    }
                     if (selectedText === "[1013] Part Back Order") {
                        html = `         
        <div class="mb-3">
            <label>Customer Name</label>
            <input autocomplete="off" type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
                    <label>Nomor Whastapp</label>
                    <input autocomplete="off" type="text" name="customer_phone" class="form-control" required>
                </div>
          
        <div class="mb-3">
                    <label>Case ID</label>
                    <input autocomplete="off" type="text" name="workorder" class="form-control" required>
                </div>
        <div class="mb-3">
                    <label>Part</label>
                    <input autocomplete="off" type="text" name="part" class="form-control" required>
                </div>

    `;
                    }

                    dynamicFields.innerHTML = html;
                });

            });
        </script>

    </div>
    <script>
// ================= AUTO ISI NOMOR TEKNISI =================
document.addEventListener('change', function(e){

    if(e.target && e.target.id === 'technician'){

        let selected = e.target.options[e.target.selectedIndex];
        let phone = selected.getAttribute('data-phone');

        let phoneInput = document.getElementById('technician_phone');

        if(phoneInput){
            phoneInput.value = phone || '';
        }

    }

});
</script>
</div>

@endsection