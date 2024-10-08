@isset($participant)
    <table>
        <tbody>
            <tr>
                <td>
                    <img src="{{ public_path('logo.png') }}" width="80" alt="img" alt="img">
                </td>
                <td class="text-center">
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-align: center;">
                                    <p style="padding: 0px; margin: 0px; font-size: 24px;">KLINIK DR. DINI <br>
		                                MEDICAL CENTER
                                    </p>
                                    <p>No.Izin : 0104220009994 <br>
                                        Jln. Raya Karang Hilir no 815 Desa Karangtengah Kec. Cibadak
                                        Kab.  Sukabumi (0266) 6545065
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center" style="font-size: 13px; color: rgb(157, 155, 155);">
                                    {{-- {{ $participant->client?->address }} --}}
                                    </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate($participant->code)) !!} ">
                </td>
            </tr>
        </tbody>
    </table>
@else
    <table>
        <tbody>
            <tr>
                <td>
                    <img src="{{ public_path('logo.png') }}" width="80" alt="img" alt="img">
                </td>
                <td class="text-center">
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-align: center;">
                                    <p style="padding: 0px; margin: 0px; font-size: 24px;">{{ config('app.name') }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center" style="font-size: 13px; color: rgb(157, 155, 155);">
                                    {{-- {{ config('app.address') }}</td> --}}
                            </tr>
                        </tbody>
                    </table>
                </td>
                {{-- <td>
                    <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate($participant->code)) !!} ">
                </td> --}}
            </tr>
        </tbody>
    </table>
@endisset

<div style="height: 2px; width: 100%; background-color: rgb(126, 126, 126); margin-bottom: 10px"></div>
