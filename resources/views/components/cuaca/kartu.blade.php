{{--
    Ringkasan cuaca sebuah lahan dari WeatherService::untukLahan().
    lengkap = tampilkan juga prakiraan 5 hari dan saran perawatan (dashboard).
--}}
@props(['cuaca', 'saran' => [], 'lengkap' => false])
@use('Illuminate\Support\Carbon')

@php
    // 6,0 ditulis 6 supaya ringkas; 6,5 tetap 6,5
    $angka = fn ($nilai, $desimal = 1) => preg_replace('/,0+$/', '', number_format((float) $nilai, $desimal, ',', '.'));
    $diambil = Carbon::parse($cuaca['diambil_at'])->setTimezone(config('cuaca.zona_waktu'))->locale('id');
    $namaSumber = ['openweathermap' => 'OpenWeatherMap', 'open-meteo' => 'Open-Meteo'][$cuaca['sumber']] ?? $cuaca['sumber'];
    $statistik = [
        ['Kelembaban', $cuaca['kelembaban'].'%'],
        ['Angin', $angka($cuaca['kecepatan_angin'], 0).' km/j'],
        ['Hujan 24 jam', $angka($cuaca['curah_hujan']).' mm'],
        ['Peluang hujan', $cuaca['peluang_hujan'].'%'],
    ];
@endphp

<div {{ $attributes->class(['grid gap-5', 'lg:grid-cols-5' => $lengkap]) }}>
    <div @class(['rounded-xl bg-stone-50 ring-1 ring-inset ring-stone-200/80', 'lg:col-span-2 lg:self-start' => $lengkap])>
        <div class="flex items-center gap-3.5 p-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white text-brand-700 ring-1 ring-stone-200">
                <i data-lucide="{{ $cuaca['ikon'] }}" class="h-6 w-6"></i>
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-semibold leading-tight tracking-tight text-stone-900 tabular-nums">{{ round($cuaca['suhu']) }}&deg;C</p>
                <p class="truncate text-sm text-stone-600">
                    {{ $cuaca['kondisi_cuaca'] }}
                    @if ($cuaca['terasa_seperti'] !== null && abs($cuaca['terasa_seperti'] - $cuaca['suhu']) >= 1)
                        <span class="text-stone-400">/ terasa {{ round($cuaca['terasa_seperti']) }}&deg;</span>
                    @endif
                </p>
            </div>
        </div>

        <dl class="grid grid-cols-2 gap-px border-t border-stone-200/80 bg-stone-200/80">
            @foreach ($statistik as [$label, $nilai])
                <div class="bg-stone-50 px-4 py-2.5">
                    <dt class="text-xs text-stone-500">{{ $label }}</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-stone-900 tabular-nums">{{ $nilai }}</dd>
                </div>
            @endforeach
        </dl>

        <p @class([
            'flex items-start gap-1.5 border-t border-stone-200/80 px-4 py-2.5 text-xs',
            'text-amber-800 bg-amber-50/70 rounded-b-xl' => $cuaca['arsip'] ?? false,
            'text-stone-500' => ! ($cuaca['arsip'] ?? false),
        ])>
            @if ($cuaca['arsip'] ?? false)
                <i data-lucide="archive" class="mt-px h-3.5 w-3.5 shrink-0"></i>
                <span>Layanan cuaca sedang tidak bisa dihubungi. Ini data terakhir, {{ $diambil->diffForHumans() }}.</span>
            @else
                <i data-lucide="clock" class="mt-px h-3.5 w-3.5 shrink-0"></i>
                <span>Diperbarui {{ $diambil->isToday() ? $diambil->format('H.i') : $diambil->translatedFormat('j M, H.i') }} WIB / {{ $namaSumber }}</span>
            @endif
        </p>
    </div>

    @if ($lengkap)
        <div class="space-y-5 lg:col-span-3">
            @if (! empty($cuaca['prakiraan']))
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Prakiraan 5 hari</h3>
                    <ol class="mt-2.5 grid grid-cols-5 gap-1.5 sm:gap-2">
                        @foreach (array_slice($cuaca['prakiraan'], 0, 5) as $hari)
                            @php $tanggal = Carbon::parse($hari['tanggal'], config('cuaca.zona_waktu'))->locale('id'); @endphp
                            <li class="flex flex-col items-center rounded-lg px-1 py-2.5 text-center ring-1 ring-inset ring-stone-200/80" title="{{ $hari['kondisi_cuaca'] }}">
                                <span class="text-xs font-medium text-stone-600">{{ $tanggal->isToday() ? 'Hari ini' : $tanggal->translatedFormat('D') }}</span>
                                <i data-lucide="{{ $hari['ikon'] }}" class="my-1.5 h-5 w-5 text-brand-700" aria-hidden="true"></i>
                                <span class="sr-only">{{ $hari['kondisi_cuaca'] }},</span>
                                <span class="text-sm font-semibold text-stone-900 tabular-nums">{{ round($hari['suhu_max']) }}&deg;</span>
                                <span class="text-xs text-stone-500 tabular-nums">{{ round($hari['suhu_min']) }}&deg;</span>
                                <span @class(['mt-1 inline-flex items-center gap-0.5 text-[11px] tabular-nums', 'font-semibold text-sky-700' => $hari['peluang_hujan'] >= 50, 'text-stone-400' => $hari['peluang_hujan'] < 50])>
                                    <i data-lucide="droplets" class="h-3 w-3" aria-hidden="true"></i>{{ $hari['peluang_hujan'] }}%
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @if ($saran)
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Saran untuk lahan hari ini</h3>
                    <ul class="mt-2.5 space-y-2">
                        @foreach ($saran as $butir)
                            <li class="flex items-start gap-2.5 text-sm text-stone-700">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-700">
                                    <i data-lucide="{{ $butir['ikon'] }}" class="h-3.5 w-3.5"></i>
                                </span>
                                <span>{{ $butir['teks'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>
