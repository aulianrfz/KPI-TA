<footer class="mt-5 py-5" style="background: linear-gradient(90deg, #0367A6, #3F9BBF);">
    <div class="container">
        @php
            $brand = \App\Models\Link::where('type', 'brand')->first();
            $brandLabel = $brand ? $brand->label : 'Nama.com';
            $brandUrl = $brand ? (preg_match('/^https?:\/\//', $brand->url) ? $brand->url : 'https://' . ltrim($brand->url, '/')) : '#';
        @endphp

        <div class="row text-white">
            <div class="col-12 col-md-4 mb-4">
                <div class="fw-bold fs-5 fs-md-3">
                    <a href="{{ $brandUrl }}" class="text-white text-decoration-none" target="_blank">
                        {{ $brandLabel }}
                    </a>
                </div>
            </div>

            <div class="col-6 col-md-4 mb-4">
                <div class="footer-links">
                    @php
                        $linksSection1 = \App\Models\Link::where('type', 'link')->take(6)->get();
                    @endphp

                    @foreach($linksSection1 as $link)
                        @php
                            $url = preg_match('/^https?:\/\//', $link->url) ? $link->url : 'https://' . ltrim($link->url, '/');
                        @endphp
                        <p class="mb-1">
                            <a href="{{ $url }}" class="text-white text-decoration-none small" target="_blank">
                                {{ $link->label }}
                            </a>
                        </p>
                    @endforeach
                </div>
            </div>

            <div class="col-6 col-md-4 mb-4">
                <div class="footer-links">
                    @php
                        $linksSection2 = \App\Models\Link::where('type', 'link')->skip(6)->take(6)->get();
                    @endphp

                    @foreach($linksSection2 as $link)
                        @php
                            $url = preg_match('/^https?:\/\//', $link->url) ? $link->url : 'https://' . ltrim($link->url, '/');
                        @endphp
                        <p class="mb-1">
                            <a href="{{ $url }}" class="text-white text-decoration-none small" target="_blank">
                                {{ $link->label }}
                            </a>
                        </p>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-center gap-3">
            @php
                $socialLinks = \App\Models\Link::where('type', 'social')->get();
            @endphp

            @foreach($socialLinks as $social)
                @php
                    $url = preg_match('/^https?:\/\//', $social->url) ? $social->url : 'https://' . ltrim($social->url, '/');
                @endphp
                <a href="{{ $url }}" target="_blank">
                    @if(filter_var($social->icon, FILTER_VALIDATE_URL))
                        <img src="{{ $social->icon }}" alt="{{ $social->label }}" class="img-fluid" style="max-width: 24px;">
                    @elseif($social->icon)
                        <img src="{{ asset($social->icon) }}" alt="{{ $social->label }}" class="img-fluid" style="max-width: 24px;">
                    @else
                        <span class="text-white small">{{ $social->label }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</footer>
