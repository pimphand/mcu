<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sticker Lab</title>
    <style>
        *{
            margin-top: 1px;
            margin-bottom: 1px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .new-page {
            width: 90%;
            height: 90%;
            font-size: 28px;
        }
        @page { size: 6cm 10cm landscape; }
    </style>
</head>

<body>
    @php
        $data = ['Registrasi', 'Pemeriksaan Fisik', 'Lab. Urine', 'Lab. Urine', 'Lab. Darah', 'Lab. Darah', 'Radiologi', 'Visus', 'TB / BB', 'Tensi', '', 'Rectal'];
    @endphp
    @foreach ($data as $item)
        <div class="new-page">
            <p>{{ $participant->code }}</p>
            <p>{{ $participant->name }} [{{ $participant->gender }}]</p>
            <p>{{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y Th %m Bl %d Hr') }}
            </p>
            @if ($item)
                <p>[{{ $item }}]</p>
            @endif
        </div>
    @endforeach
</body>

</html>
