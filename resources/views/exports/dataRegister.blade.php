<table class="dt-responsive table mt-1 textkecil" id="table">
    <thead>
    <tr>
        <th class="border-bottom border-top textkecil">No.</th>
        <th class="border-bottom border-top textkecil">Tgl Register</th>
        <th class="border-bottom border-top textkecil">MCU ID</th>
        <th class="border-bottom border-top textkecil">NIK</th>
        <th class="border-bottom border-top textkecil">Nama Pasien</th>
        <th class="border-bottom border-top textkecil">Tgl Lahir</th>
        <th class="border-bottom border-top textkecil">JK</th>
        <th class="border-bottom border-top textkecil">Bagian/ Unit</th>
        <th class="border-bottom border-top textkecil">Perusahaan</th>
        <th class="border-bottom border-top textkecil">Gedung</th>
        <th class="border-bottom border-top textkecil">Paket MCU</th>
        <th class="border-bottom border-top textkecil hilang">TTV </th>
        <th class="border-bottom border-top textkecil hilang">Pemeriksaan Fisik </th>
        <th class="border-bottom border-top textkecil hilang">Lab </th>
        <th class="border-bottom border-top textkecil hilang">Rad </th>
        <th class="border-bottom border-top textkecil hilang">Audiometri </th>
        <th class="border-bottom border-top textkecil hilang">EKG </th>
        <th class="border-bottom border-top textkecil hilang">Spiro </th>
        <th class="border-bottom border-top textkecil hilang">Rectal </th>
    </tr>
    </thead>
    <tbody id="data_table">
    @foreach($data as $index => $item)
        <tr>
            <td class="border-bottom border-top text-right" nowrap="">{{$index+1}}</td>
            <td class="border-bottom border-top text-center">{{$item->register_date}}</td>
            <td class="border-bottom border-top text-center">{{$item->code}}</td>
            <td class="border-bottom border-top text-center">{{$item->nik}}</td>
            <td class="border-bottom border-top text-left">{{$item->name}}</td>
            <td class="border-bottom border-top text-left" nowrap="">{{$item->birthday}}</td>
            <td class="border-bottom border-top text-center">{{$item->gender}}</td>
            <td class="border-bottom border-top text-center">{{$item->divisi->name}} - {{$item->department->name}}</td>
            <td class="border-bottom border-top text-center">{{$item->client->name}}</td>
            <td class="border-bottom border-top text-center">{{$item->gedung}}</td>
            <td class="border-bottom border-top text-left">{{$item->packet_name}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->tandaVital?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->pemeriksaanFisik?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->laboratorium?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->radiologi?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->audiometri?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->ekg?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->spirometri?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td class="border-bottom border-top text-left hilang">{{$item->rectal?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
