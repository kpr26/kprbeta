@if ($paginator->hasPages())
    <div class="kpr-pagination-bottom" style="margin-top:18px;">
        <nav class="kpr-pagination-nav" aria-label="Navigasi Halaman Data">
            {{-- Tombol Pertama & Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" disabled title="Halaman Pertama">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.79 14.77a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L11.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Zm-6 0a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L5.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Z" clip-rule="evenodd"/></svg>
                    <span>Pertama</span>
                </button>
                <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" disabled title="Halaman Sebelumnya">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                    <span>Sebelumnya</span>
                </button>
            @else
                <a href="{{ $paginator->url(1) }}" class="kpr-page-btn kpr-page-btn--nav-text" title="Halaman Pertama">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.79 14.77a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L11.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Zm-6 0a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L5.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Z" clip-rule="evenodd"/></svg>
                    <span>Pertama</span>
                </a>
                <a href="{{ $paginator->previousPageUrl() }}" class="kpr-page-btn kpr-page-btn--nav-text" title="Halaman Sebelumnya">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Elemen Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Pemisah Tiga Titik --}}
                @if (is_string($element))
                    <span class="kpr-page-ellipsis">{{ $element }}</span>
                @endif

                {{-- Array Tautan Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="kpr-page-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="kpr-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya & Terakhir --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="kpr-page-btn kpr-page-btn--nav-text" title="Halaman Berikutnya">
                    <span>Berikutnya</span>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                </a>
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="kpr-page-btn kpr-page-btn--nav-text" title="Halaman Terakhir">
                    <span>Terakhir</span>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.21 14.77a.75.75 0 0 1 .02-1.06L14.168 10 10.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Zm-6 0a.75.75 0 0 1 .02-1.06L8.168 10 4.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                </a>
            @else
                <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" disabled title="Halaman Berikutnya">
                    <span>Berikutnya</span>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" disabled title="Halaman Terakhir">
                    <span>Terakhir</span>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.21 14.77a.75.75 0 0 1 .02-1.06L14.168 10 10.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Zm-6 0a.75.75 0 0 1 .02-1.06L8.168 10 4.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                </button>
            @endif
        </nav>

        <div class="kpr-pagination-status-text">
            Menampilkan <strong>{{ $paginator->firstItem() }} – {{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data (Halaman <strong>{{ $paginator->currentPage() }}</strong> dari <strong>{{ $paginator->lastPage() }}</strong>)
        </div>
    </div>
@endif
