@extends('layouts.app')

@section('title', 'Registro Carrera 5K & Caminata | 51° Aniversario UNELLEZ')

@section('content')
<div class="split-layout">
    <!-- LEFT COLUMN: Official Flyer & Event Info -->
    <section class="flyer-col">
        <!-- Hero Tag -->
        <div>
            <div class="hero-tag">
                <span class="dot"></span>
                <span>Inscripciones Abiertas &bull; Domingo 25</span>
            </div>
            <h2 class="hero-title-main" style="font-size: 2.1rem; font-weight: 800; line-height: 1.15; margin-bottom: 0.5rem; letter-spacing: -0.5px;">
                5K Caminata y Carrera <br>
                <span style="color: var(--primary);">51° Aniversario UNELLEZ</span>
            </h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 0.75rem;">
                Únete a la gran fiesta deportiva universitaria. Regístrate en línea, obtén tu número oficial de corredor <strong>(5K-XXXX)</strong> e imprímelo al instante.
            </p>
            <div class="mobile-only" style="margin-bottom: 1.25rem;">
                <a href="#formulario-seccion" class="btn btn-primary btn-sm" style="width: 100%; box-shadow: 0 4px 14px rgba(230, 81, 0, 0.25);">
                    <i class="fa-solid fa-arrow-down"></i> Ir directo al Formulario de Inscripción
                </a>
            </div>
        </div>

        <!-- Flyer Card -->
        <div class="flyer-card" onclick="openFlyerLightbox()" title="Hacer clic para ampliar afiche">
            <img src="{{ asset('images/flyer-5k.jpg') }}" alt="Afiche Oficial 5K Caminata UNELLEZ" class="flyer-img">
            <div class="flyer-overlay-badge">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Clic para ampliar flyer
            </div>
        </div>

        <!-- Race Highlights Information -->
        <div class="race-details-card">
            <div class="race-details-title">
                <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                <span>Información del Evento y Recorrido</span>
            </div>
            
            <div class="race-details-grid">
                <div class="detail-item">
                    <div class="detail-label"><i class="fa-regular fa-calendar" style="color: var(--accent-gold);"></i> Fecha</div>
                    <div class="detail-value">Domingo 25</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label"><i class="fa-regular fa-clock" style="color: var(--accent-gold);"></i> Convocatoria</div>
                    <div class="detail-value">6:30 AM (Salida 7:00 AM)</div>
                </div>

                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label"><i class="fa-solid fa-map-pin" style="color: var(--primary);"></i> Salida y Llegada</div>
                    <div class="detail-value">Estacionamiento del Rectorado UNELLEZ</div>
                </div>

                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label"><i class="fa-solid fa-route" style="color: var(--accent-blue);"></i> Ruta Oficial 5K</div>
                    <div class="detail-value" style="font-size: 0.82rem; font-weight: 500; color: #303133ff; line-height: 1.4; margin-top: 0.3rem;">
                        Av. 23 de Enero &rarr; Semáforo Floresta &rarr; Av. Orlando Araujo &rarr; Punto Fresco &rarr; Entrada UNELLEZ.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RIGHT COLUMN: Registration & Reprint / Lookup Form -->
    <section class="form-col" id="formulario-seccion">
        <div class="form-card">
            <!-- Tabs switch: Register vs Lookup -->
            <div class="tabs-container">
                <button type="button" class="tab-btn active" data-tab="tab-register" onclick="switchTab('tab-register')">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Nuevo Registro</span>
                </button>
                <button type="button" class="tab-btn" data-tab="tab-lookup" id="tabLookupBtn" onclick="switchTab('tab-lookup')">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Reimprimir / Consultar</span>
                </button>
            </div>

            <!-- TAB 1: REGISTRATION FORM -->
            <div class="tab-content active" id="tab-register">
                <div class="form-header">
                    <h2>Formulario de Registro</h2>
                    <p>Completa tus datos personales para asignarte tu dorsal único <strong>5K-XXXX</strong>.</p>
                </div>

                <!-- Server Alerts -->
                @if(session('success_message'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
                        <div>{{ session('success_message') }}</div>
                    </div>
                @endif

                @if(session('info_message'))
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-circle-info" style="font-size: 1.2rem;"></i>
                        <div>
                            {{ session('info_message') }}
                            @if(session('participant'))
                                <br>
                                <button type="button" onclick="showDorsalModal({{ json_encode(session('participant')) }})" class="btn btn-sm btn-gold" style="margin-top: 0.5rem;">
                                    <i class="fa-solid fa-print"></i> Ver e Imprimir mi Número
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Por favor revisa los siguientes errores:</strong>
                            <ul style="margin-left: 1.2rem; margin-top: 0.25rem;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form id="registrationForm" action="{{ route('participants.store') }}" method="POST" onsubmit="handleRegistrationSubmit(event)">
                    @csrf

                    <!-- Nombres y Apellidos -->
                    <div class="form-group-row">
                        <div class="form-group">
                            <label class="form-label" for="first_name">
                                Nombre <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-input" 
                                   placeholder="Ej: Carlos" 
                                   value="{{ old('first_name') }}" 
                                   required 
                                   autocomplete="given-name"
                                   pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$"
                                   title="El nombre no debe contener números"
                                   oninput="this.value = this.value.replace(/[0-9]/g, '')">
                            <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 0.25rem;">
                                Solo letras (sin números).
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="last_name">
                                Apellido <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-input" 
                                   placeholder="Ej: Pérez" 
                                   value="{{ old('last_name') }}" 
                                   required 
                                   autocomplete="family-name"
                                   pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$"
                                   title="El apellido no debe contener números"
                                   oninput="this.value = this.value.replace(/[0-9]/g, '')">
                            <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 0.25rem;">
                                Solo letras (sin números).
                            </div>
                        </div>
                    </div>

                    <!-- Cédula de Identidad -->
                    <div class="form-group">
                        <label class="form-label" for="national_id">
                            Cédula de Identidad <span class="required">*</span>
                        </label>
                        <div class="cedula-input-group">
                            <select name="national_id_type" id="national_id_type" class="form-select prefix-select">
                                <option value="V" {{ old('national_id_type') == 'V' ? 'selected' : '' }}>V-</option>
                                <option value="E" {{ old('national_id_type') == 'E' ? 'selected' : '' }}>E-</option>
                            </select>
                            <input type="text" 
                                   id="national_id" 
                                   name="national_id" 
                                   class="form-input" 
                                   placeholder="Ej: 24123456" 
                                   value="{{ old('national_id') }}" 
                                   required 
                                   inputmode="numeric" 
                                   maxlength="9" 
                                   pattern="^[0-9]{5,9}$"
                                   title="Cédula válida de máximo 9 números"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)">
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                            Solo números (máximo <strong>9 dígitos</strong>, sin puntos ni letras).
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="form-group">
                        <label class="form-label" for="phone">
                            Teléfono de Contacto <span class="required">*</span>
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               placeholder="Ej: 0414-1234567 o 04121234567" 
                               value="{{ old('phone') }}" 
                               required 
                               autocomplete="tel"
                               pattern="^[0-9\-\+\s\(\)]+$"
                               title="Solo números y guiones, sin letras"
                               oninput="this.value = this.value.replace(/[^0-9\-\+\s\(\)]/g, '')">
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                            Solo dígitos telefónicos. <strong>No se permiten letras</strong>.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1.05rem; margin-top: 0.5rem;">
                        <i class="fa-solid fa-person-running"></i>
                        <span>COMPLETAR REGISTRO Y OBTENER NÚMERO</span>
                    </button>
                </form>
            </div>

            <!-- TAB 2: REPRINT / LOOKUP DORSAL -->
            <div class="tab-content" id="tab-lookup">
                <div class="form-header">
                    <h2>¿Olvidaste imprimir tu número?</h2>
                    <p>Si ya te registraste anteriormente, ingresa tu cédula para consultar tu número asignado e imprimir tu comprobante oficial.</p>
                </div>

                <div id="lookupAlertContainer"></div>

                <form id="lookupForm" onsubmit="handleLookupSubmit(event)">
                    <div class="form-group">
                        <label class="form-label" for="lookup_cedula">
                            Ingresa tu Cédula de Identidad <span class="required">*</span>
                        </label>
                        <div class="cedula-input-group">
                            <select id="lookup_prefix" class="form-select prefix-select">
                                <option value="V">V-</option>
                                <option value="E">E-</option>
                            </select>
                            <input type="text" 
                                   id="lookup_cedula" 
                                   class="form-input" 
                                   placeholder="Ej: 24123456" 
                                   required 
                                   inputmode="numeric" 
                                   maxlength="9" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)">
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                            Solo números (máximo 9 dígitos).
                        </div>
                    </div>

                    <button type="submit" id="lookupBtn" class="btn btn-gold btn-block" style="padding: 0.9rem; font-size: 1rem;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>BUSCAR MI NÚMERO Y DORSAL</span>
                    </button>
                </form>

                <!-- Fast result placeholder box -->
                <div id="lookupResultBox" style="display: none; margin-top: 1.5rem; background: var(--bg-subtle); border-radius: var(--radius-sm); padding: 1.25rem; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.78rem; color: var(--primary); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Participante Encontrado</div>
                    <div id="resultName" style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.2rem;"></div>
                    <div id="resultId" style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.8rem;"></div>
                    <div style="display: flex; align-items: baseline; gap: 0.6rem; margin-bottom: 1rem;">
                        <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;">Número asignado:</span>
                        <span id="resultBib" style="font-family: var(--font-bib); font-size: 2.4rem; color: var(--primary); letter-spacing: 1.5px; line-height: 1;">5K-0000</span>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" id="btnOpenModalFromResult" class="btn btn-primary btn-block btn-sm">
                            <i class="fa-solid fa-print"></i> Ver e Imprimir Dorsal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Live Stats Banner -->
        <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface); padding: 0.9rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-stopwatch" style="color: var(--primary);"></i>
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total de Participantes Inscritos:</span>
            </div>
            <span style="font-family: var(--font-mono); font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                {{ number_format($totalCount, 0, ',', '.') }}
            </span>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    // Auto-trigger modal if returned from server session
    @if(session('participant'))
        document.addEventListener('DOMContentLoaded', () => {
            const p = @json(session('participant'));
            const isAlready = @json(session('already_registered') ?? false);
            let notice = '';
            if (isAlready) {
                notice = '<div class="alert alert-warning"><i class="fa-solid fa-circle-info"></i> Esta cédula ya estaba registrada. Aquí tienes tu dorsal asignado listo para imprimir.</div>';
            } else {
                notice = '<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> ¡Felicidades! Te has registrado exitosamente. Tu número asignado es: <strong>' + p.bib_number + '</strong></div>';
            }
            showDorsalModal(p, notice);
        });
    @endif

    // Handle AJAX Registration
    async function handleRegistrationSubmit(event) {
        event.preventDefault();
        const form = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Asignando número único...';

        const formData = new FormData(form);

        try {
            const response = await fetch("{{ route('participants.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                form.reset();
                let notice = '';
                if (data.already_registered) {
                    notice = `<div class="alert alert-warning"><i class="fa-solid fa-circle-info"></i> Ya estabas registrado previamente con esta cédula. Tu número asignado es: <strong>${data.participant.bib_number}</strong></div>`;
                } else {
                    notice = `<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> ¡Registro exitoso! Te ha sido asignado el número <strong>${data.participant.bib_number}</strong></div>`;
                }
                showDorsalModal(data.participant, notice);
            } else {
                alert(data.message || 'Error al procesar el registro. Por favor revisa los datos.');
            }
        } catch (error) {
            console.error(error);
            // Fallback to standard form submission if fetch has network issues
            form.submit();
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    // Handle AJAX Lookup / Search
    async function handleLookupSubmit(event) {
        event.preventDefault();
        const cedulaNum = document.getElementById('lookup_cedula').value.trim();
        const prefix = document.getElementById('lookup_prefix').value;
        const lookupBtn = document.getElementById('lookupBtn');
        const alertContainer = document.getElementById('lookupAlertContainer');
        const resultBox = document.getElementById('lookupResultBox');

        if (!cedulaNum) {
            alertContainer.innerHTML = '<div class="alert alert-danger">Por favor escribe el número de cédula.</div>';
            return;
        }

        const originalBtnHtml = lookupBtn.innerHTML;
        lookupBtn.disabled = true;
        lookupBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Consultando base de datos...';
        alertContainer.innerHTML = '';
        resultBox.style.display = 'none';

        try {
            const response = await fetch(`{{ route('participants.lookup') }}?cedula=${prefix}-${cedulaNum}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.found) {
                const p = data.participant;
                document.getElementById('resultName').innerText = `${p.first_name} ${p.last_name}`;
                document.getElementById('resultId').innerText = `C.I: ${p.national_id} | Teléfono: ${p.phone}`;
                document.getElementById('resultBib').innerText = p.bib_number;

                document.getElementById('btnOpenModalFromResult').onclick = () => {
                    showDorsalModal(p, '<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Participante recuperado con éxito. Listo para imprimir.</div>');
                };

                resultBox.style.display = 'block';

                // Automatically open modal for best user experience
                showDorsalModal(p, '<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> ¡Registro encontrado! Aquí tienes tu dorsal <strong>' + p.bib_number + '</strong> para imprimirlo.</div>');
            } else {
                alertContainer.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> No se encontró ningún participante registrado con la cédula <strong>${prefix}-${cedulaNum}</strong>. <br><a href="javascript:void(0)" onclick="switchTab('tab-register')" style="color: white; text-decoration: underline; font-weight: 700;">Haz clic aquí para registrarte ahora</a>.</div>`;
            }
        } catch (error) {
            console.error(error);
            alertContainer.innerHTML = '<div class="alert alert-danger">Ocurrió un error al buscar. Intente nuevamente.</div>';
        } finally {
            lookupBtn.disabled = false;
            lookupBtn.innerHTML = originalBtnHtml;
        }
    }
</script>
@endsection
