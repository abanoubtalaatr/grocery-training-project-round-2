<header class="border-bottom bg-white px-4 py-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-lg-none">
            <button class="btn btn-sm btn-light rounded-3" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-0 bg-transparent">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $item)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if($loop->last)
                                {{ $item['label'] }}
                            @else
                                <a href="{{ $item['url'] }}" class="text-decoration-none">{{ $item['label'] }}</a>
                            @endif
                        </li>
                    @endforeach
                @else
                    <li class="breadcrumb-item active">Dashboard</li>
                @endif
            </ol>
        </nav>

        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-light rounded-3" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i>
                    <span class="d-none d-sm-inline">{{ Auth::user()->username ?? 'Admin' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end rounded-3" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item rounded-2" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider rounded"></li>
                    <li>
                        <a class="dropdown-item rounded-2" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-1"></i> Sign out
                        </a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
