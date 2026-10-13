@php
    $user = Auth::user();
    $inisial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->username, 0, 2));
@endphp

<header class="sticky top-0 z-30 border-b border-stone-200/80 bg-white/80 backdrop-blur-md supports-[backdrop-filter]:bg-white/70">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button type="button" data-sidebar-open aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu"
                class="-ml-2 inline-flex h-10 w-10 items-center justify-center rounded-lg text-stone-600 transition-colors hover:bg-stone-100 hover:text-stone-900 lg:hidden">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>

        <p class="min-w-0 flex-1 truncate text-sm font-semibold text-stone-900">@yield('title', 'Dashboard')</p>

        @if ($user->role === 'user')
            <a href="{{ route('user.keranjang.index') }}" aria-label="Keranjang ({{ $jumlahKeranjang }} produk)"
               class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-stone-600 transition-colors hover:bg-stone-100 hover:text-stone-900">
                <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                @if ($jumlahKeranjang > 0)
                    <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-700 px-1 text-[10px] font-bold text-white tabular-nums">{{ $jumlahKeranjang }}</span>
                @endif
            </a>
        @endif

        {{-- Menu profil. <details> supaya tetap bisa dibuka walau JS gagal dimuat. --}}
        <details class="group relative" data-menu>
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-1 pr-2 transition-colors hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 [&::-webkit-details-marker]:hidden">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">{{ $inisial }}</span>
                <span class="hidden max-w-[10rem] truncate text-sm font-medium text-stone-700 sm:block">{{ $user->username }}</span>
                <i data-lucide="chevron-down" class="h-4 w-4 text-stone-400 transition-transform group-open:rotate-180"></i>
            </summary>

            <div class="absolute right-0 mt-2 w-60 origin-top-right rounded-xl border border-stone-200/80 bg-white p-1.5 shadow-lift">
                <div class="px-3 py-2.5">
                    <p class="truncate text-sm font-semibold text-stone-900">{{ $user->username }}</p>
                    <p class="truncate text-xs text-stone-500">{{ $user->email }}</p>
                </div>
                <div class="my-1 h-px bg-stone-100"></div>
                <a href="{{ route('akun.edit') }}"
                   class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900">
                    <i data-lucide="settings" class="h-4 w-4 text-stone-400"></i> Pengaturan akun
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm text-stone-700 transition-colors hover:bg-red-50 hover:text-red-700">
                        <i data-lucide="log-out" class="h-4 w-4 text-stone-400"></i> Keluar
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>
