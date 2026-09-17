<nav class="navbar bg-body-tertiary fixed-top" style="font-size: 1.2rem;">
    <div class="container-fluid">
        @auth
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        @endauth
        
        <div class="mx-auto">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo.png') }}" style="width:40px; height:auto;" alt="Logo">
            </a>
        </div>
        
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            @guest
                <a href="{{ route('login') }}" class="bot botNegro" title="Iniciar sesión">🔐 Acceder</a>
            @else
                <span class="small fw-semibold text-truncate d-inline-block" style="max-width:120px;">
                    {{ explode(' ', Auth::user()->name)[0] }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="bot botNegro" title="Cerrar sesión">
                        @canany(['admin', 'adminMax']) 🛠️ @else 👤 @endcanany
                    </button>
                </form>
            @endguest
        </div>
        
        @if(auth()->check() && auth()->user()->canMenu)
            <div class="offcanvas offcanvas-start cardSec" tabindex="-1" id="offcanvasNavbar">
                <div class="cardSec-header">
                    <span class="fs-5 fw-bold">📌 Menú</span>
                    <button type="button" class="bot botNegro" data-bs-dismiss="offcanvas">✕</button>
                </div>
                <div class="cardSec-body">
                    <ul class="navbar-nav pe-3">
                        <li class="nav-item">
                            <a href="{{ url('/expedientes') }}" class="nav-link small">⚖️ Expedientes</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav pe-3">
                        <li class="nav-item custom-dropdown-item">
                            <a href="#" class="nav-link menu-trigger">🗂️ Catálogos</a>
                            <ul class="submenu d-none list-unstyled ps-3">
                                <li class="nav-item">
                                    <a href="{{ url('/materias') }}" class="nav-link small">📚 Materias</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/organos') }}" class="nav-link small">🏛️ Juzgados</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/personas') }}" class="nav-link small">👥 Personas</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link menu-trigger">⚙️ Configuración</a>
                                    <ul class="submenu d-none list-unstyled ps-3 border-start">
                                        <li class="nav-item">
                                            <a href="{{ url('/users') }}" class="nav-link small">👨‍💻 Usuarios</a>
                                        </li>
                                        @auth
                                            @if(auth()->user()->roles->min('nivel') < 3)
                                                <li class="nav-item">
                                                    <a href="{{ url('/catalogos') }}" class="nav-link small">📦 Básicos</a>
                                                </li>
                                            @endif
                                        @endauth
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</nav>

<style>
    .menu-trigger { cursor: pointer; position: relative; }
    .menu-trigger::after { content: ' ▾'; font-size: 0.8em; color: gray; }
    .menu-trigger.active::after { content: ' ▴'; }
    .submenu { background: rgba(0,0,0,0.02); border-radius: 4px; }
    .nav-link:hover { color: #0d6efd; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuTriggers = document.querySelectorAll('.menu-trigger');
    menuTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const nextSubmenu = this.nextElementSibling;
            if (nextSubmenu) {
                const isHidden = nextSubmenu.classList.contains('d-none');
                this.classList.toggle('active');
                if (isHidden) {
                    nextSubmenu.classList.remove('d-none');
                } else {
                    nextSubmenu.classList.add('d-none');
                }
            }
        });
    });
});
</script>