<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Carrera 5K y Caminata | 51° Aniversario UNELLEZ')</title>
    <meta name="description" content="Registro oficial para la Carrera 5K y Caminata del 51 Aniversario UNELLEZ. Domingo 25, salida y llegada en el estacionamiento del Rectorado.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@500;700&family=Montserrat:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons (Lucide / Boxicons / FontAwesome style) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @yield('styles')
</head>
<body>
    <div class="bg-grid-pattern"></div>

    <div class="app-container">
        <!-- Top Navbar -->
        <header class="navbar no-print">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-badge">5K</span>
                <div class="brand-text">
                    <h1>CAMINATA Y CARRERA</h1>
                    <span>51° Aniversario UNELLEZ</span>
                </div>
            </a>

            <nav class="nav-actions">
                <a href="{{ route('home') }}?tab=lookup#formulario-seccion" onclick="handleNavbarLookup(event)" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Consultar Cédula</span>
                </a>
                <a href="{{ route('participants.list') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-users"></i>
                    <span>Ver Inscritos</span>
                </a>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Reusable Dorsal / Official Bib Modal -->
    <div id="dorsalModal" class="modal-backdrop">
        <div class="modal-content">
            <button type="button" class="modal-close no-print" onclick="closeDorsalModal()">&times;</button>
            
            <div id="modalNotice" class="no-print" style="margin-bottom: 1rem;"></div>

            <!-- Official Race Bib Card -->
            <div class="bib-container" id="printableBib">
                <!-- Authentic Safety Pin Holes -->
                <div class="bib-pin top-left"></div>
                <div class="bib-pin top-right"></div>
                <div class="bib-pin bottom-left"></div>
                <div class="bib-pin bottom-right"></div>

                <!-- Header -->
                <div class="bib-header">
                    <div class="bib-subtitle">UNELLEZ &bull; LA UNIVERSIDAD QUE SIEMBRA</div>
                    <div class="bib-title">5K CAMINATA Y CARRERA &bull; 51° ANIVERSARIO</div>
                </div>

                <!-- Big Runner Number -->
                <div class="bib-number-wrap">
                    <div class="bib-number" id="bibNumberText">5K-0001</div>
                    <div>
                        <span class="bib-category-pill">PARTICIPANTE OFICIAL</span>
                    </div>
                </div>

                <!-- Runner Details -->
                <div class="bib-runner-info">
                    <div>
                        <div class="bib-runner-name" id="bibRunnerName">NOMBRE DEL CORREDOR</div>
                        <div class="bib-runner-id">C.I: <span id="bibRunnerId">V-00.000.000</span> &bull; TEL: <span id="bibRunnerPhone">0414-0000000</span></div>
                    </div>
                    <div class="bib-qr">
                        <img id="bibQrImg" src="" alt="Código QR de Verificación" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                </div>

                <!-- Bib Footer Info -->
                <div class="bib-footer">
                    <span>DOMINGO 25 &bull; RECTORADO UNELLEZ</span>
                    <span>REGISTRO OFICIAL VERIFICADO</span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="modal-actions no-print">
                <button type="button" class="btn btn-primary btn-block" onclick="printBib()">
                    <i class="fa-solid fa-print"></i> Imprimir Dorsal Oficial
                </button>
                <a id="bibDirectLink" href="#" target="_blank" class="btn btn-outline btn-block">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Página Completa
                </a>
            </div>
        </div>
    </div>

    <!-- Flyer Lightbox Modal -->
    <div id="flyerLightbox" class="lightbox-modal" onclick="closeFlyerLightbox()">
        <img src="{{ asset('images/flyer-5k.jpg') }}" alt="Flyer 5K Ampliado" class="lightbox-img">
    </div>

    <!-- Scripts -->
    <script>
        // Tab switching
        function switchTab(tabId) {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                if (tab.dataset.tab === tabId) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            contents.forEach(content => {
                if (content.id === tabId) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            if (tabId === 'tab-lookup') {
                setTimeout(() => {
                    const searchInput = document.getElementById('lookup_cedula');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 50);
            }
        }

        // Manejador del botón Consultar Cédula del Navbar
        function handleNavbarLookup(e) {
            const lookupTab = document.getElementById('tab-lookup');
            if (lookupTab) {
                if (e) e.preventDefault();
                switchTab('tab-lookup');
            }
        }

        // Activar pestaña si la URL incluye ?tab=lookup o #formulario-seccion
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (window.location.hash === '#formulario-seccion' || urlParams.get('tab') === 'lookup') {
                switchTab('tab-lookup');
            }
        });

        // Lightbox
        function openFlyerLightbox() {
            document.getElementById('flyerLightbox').classList.add('open');
        }

        function closeFlyerLightbox() {
            document.getElementById('flyerLightbox').classList.remove('open');
        }

        // Dorsal Modal Handlers
        function showDorsalModal(participant, customNoticeHtml = '') {
            document.getElementById('bibNumberText').innerText = participant.bib_number;
            document.getElementById('bibRunnerName').innerText = `${participant.first_name} ${participant.last_name}`;
            document.getElementById('bibRunnerId').innerText = participant.national_id;
            document.getElementById('bibRunnerPhone').innerText = participant.phone || 'N/A';
            
            // Generate QR verification link using quick QR image service
            const verifyUrl = `${window.location.origin}/dorsal/${encodeURIComponent(participant.bib_number)}`;
            const qrApi = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(verifyUrl)}`;
            document.getElementById('bibQrImg').src = qrApi;
            document.getElementById('bibDirectLink').href = verifyUrl;

            const noticeEl = document.getElementById('modalNotice');
            if (customNoticeHtml) {
                noticeEl.innerHTML = customNoticeHtml;
                noticeEl.style.display = 'block';
            } else {
                noticeEl.innerHTML = '';
                noticeEl.style.display = 'none';
            }

            document.getElementById('dorsalModal').classList.add('open');
        }

        function closeDorsalModal() {
            document.getElementById('dorsalModal').classList.remove('open');
        }

        function printBib() {
            window.print();
        }

        // Close modal when pressing ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDorsalModal();
                closeFlyerLightbox();
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
