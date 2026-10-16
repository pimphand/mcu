{{-- Cetak massal resume: satu resume utuh per peserta, halaman dipisah oleh
     blok dokumen masing-masing seperti cetak massal lainnya. --}}
@foreach ($data as $participant)
    @include('pages.report.resume', ['participant' => $participant])
@endforeach
