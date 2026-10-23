<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dorsal Oficial {{ $participant->bib_number }} | Carrera 5K UNELLEZ</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@500;700&family=Outfit:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        body {
            background-color: #070D14;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .standalone-wrap {
            width: 100%;
            max-width: 640px;
        }

        .top-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .top-toolbar {
                display: none !important;
            }
            .standalone-wrap {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="standalone-wrap">
        <!-- Top Toolbar -->
        <div class="top-toolbar no-print">
            <a href="{{ route('home') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print"></i> Imprimir Dorsal Oficial
            </button>
        </div>

        <!-- Official Bib Card -->
        <div class="bib-container">
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
                <div class="bib-number">{{ $participant->bib_number }}</div>
                <div>
                    <span class="bib-category-pill">PARTICIPANTE OFICIAL</span>
                </div>
            </div>

            <!-- Runner Details -->
            <div class="bib-runner-info">
                <div>
                    <div class="bib-runner-name">{{ $participant->full_name }}</div>
                    <div class="bib-runner-id">C.I: {{ $participant->national_id }} &bull; TEL: {{ $participant->phone }}</div>
                </div>
                <div class="bib-qr">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('participants.bib', $participant->bib_number)) }}" alt="QR" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            </div>

            <!-- Bib Footer Info -->
            <div class="bib-footer">
                <span>DOMINGO 23 &bull; RECTORADO UNELLEZ</span>
                <span>REGISTRADO: {{ $participant->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 1rem; color: #94A3B8; font-size: 0.85rem;">
            Presenta este comprobante o número impreso en el punto de concentración para retirar tu dorsal físico y kit del participante.
        </div>
    </div>

    <script>
        // Check if print query parameter is present
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.addEventListener('load', () => {
                window.print();
            });
        }
    </script>
</body>
</html>
