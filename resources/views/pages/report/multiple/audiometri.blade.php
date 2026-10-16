{{-- Cetak massal audiometri: satu hasil audiometri per peserta. --}}
@foreach ($data as $participant)
    @include('pages.report.audiometri', ['participant' => $participant])
@endforeach
