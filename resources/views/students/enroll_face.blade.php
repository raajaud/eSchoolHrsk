@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Enroll Face for {{ $student->user->first_name.' '.$student->user->last_name }}
            </h3>
        </div>


    <div class="row">
        <div class="col-md-6 text-center">
            <video id="webcam" autoplay playsinline width="100%" style="border:1px solid #ccc; border-radius:10px;"></video>
            <canvas id="snapshot" width="640" height="480" class="d-none"></canvas>
            <button id="captureBtn" class="btn btn-primary mt-3">📸 Capture & Enroll</button>
        </div>
    </div>

    <div id="result" class="alert mt-3 d-none"></div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('snapshot');
    const captureBtn = document.getElementById('captureBtn');
    const result = document.getElementById('result');

    // 1️⃣ Open Webcam
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            return video.play();
        })
        .catch(err => {
            console.error("Camera access denied:", err);
            alert("Camera access denied: " + err.message);
        });

    // 2️⃣ Capture Frame & Send
    captureBtn.addEventListener('click', () => {
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const image_b64 = canvas.toDataURL("image/jpeg").split(",")[1];

        fetch("http://127.0.0.1:8000/enroll", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                student_id: "{{ $student->user->id }}",
                name: "{{ $student->user->first_name.' '.$student->user->last_name }}",
                image_b64: image_b64
            })
        })
        .then(res => res.json())
        .then(data => {
            result.classList.remove('d-none', 'alert-danger');
            result.classList.add('alert-success');
            result.innerHTML = "✅ " + data.message;
        })
        .catch(err => {
            result.classList.remove('d-none', 'alert-success');
            result.classList.add('alert-danger');
            result.innerHTML = "❌ Error: " + err;
        });
    });
});
</script>
@endsection
