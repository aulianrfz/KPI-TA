<nav class="bg-white border-bottom shadow-sm">
    <div class="container-fluid py-2 px-3">
        <div class="row w-100 align-items-center g-2 flex-wrap">

            <div class="col-auto d-flex align-items-center">
                <a class="fw-bold text-primary text-decoration-none" href="#" style="white-space: nowrap; font-size: 1rem;">
                    <span class="d-inline d-md-none" style="font-size: 0.85rem;">LOGO</span>
                    <span class="d-none d-md-inline" style="font-size: 1.2rem;">LOGO APP</span>
                </a>
            </div>

            @auth
                @if(Auth::user()->role === 'user')
                    <div class="col">
                        <form method="GET" action="{{ url()->current() }}" class="w-100">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Cari event..." value="{{ request('search') }}"
                                    style="font-size: 0.75rem;">
                                <button type="submit" class="btn btn-sm text-white" style="background-color: #0367A6;">
                                    <i class="bi bi-search" style="font-size: 0.85rem;"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif(Auth::user()->role === 'admin')
                    <div class="col text-center text-primary fw-bold small d-none d-md-block">
                        Kompetisi Pariwisata Indonesia
                    </div>
                @endif
            @endauth

            <div class="col-auto d-flex align-items-center gap-2 flex-shrink-0 flex-wrap justify-content-end">

                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm"
                        style="border-color: #0367A6; color: #0367A6; font-size: 0.75rem;">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm"
                        style="background-color: #0367A6; border-color: #0367A6; font-size: 0.75rem;">
                        Sign Up
                    </a>
                @endguest

                @auth
                    @if(Auth::user()->role === 'user')
                        <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm"
                            style="border-color: #0367A6; font-size: 0.75rem;">
                            Home
                        </a>
                        <a href="{{ route('events.list') }}" class="btn btn-primary btn-sm"
                            style="background-color: #0367A6; border-color: #0367A6; font-size: 0.75rem;">
                            My Event
                        </a>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('dashboard.index') }}" class="btn btn-outline-primary btn-sm"
                            style="border-color: #0367A6; font-size: 0.75rem;">
                            Home
                        </a>
                    @endif

                    <a href="{{ route('profile.show') }}" class="d-inline-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                            alt="Profile" class="rounded-circle" width="26" height="26">
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
