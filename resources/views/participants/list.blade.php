@extends('layouts.app')

@section('title', 'Participantes Registrados | Carrera 5K UNELLEZ')

@section('content')
<div style="max-width: 1040px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <a href="{{ route('home') }}" class="btn btn-outline btn-sm" style="margin-bottom: 0.75rem;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Formulario
            </a>
            <h1 style="font-size: 1.85rem; font-weight: 800; line-height: 1.2;">
                Listado de Corredores Inscritos
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Total de participantes registrados oficialmente: <strong style="color: var(--primary);">{{ $totalCount }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('participants.export') }}" class="btn btn-gold btn-sm">
                <i class="fa-solid fa-file-csv"></i> Descargar CSV Oficial
            </a>
            <button onclick="window.print()" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-print"></i> Imprimir Roster
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
        <form action="{{ route('participants.list') }}" method="GET" style="display: flex; gap: 0.75rem;">
            <input type="text" 
                   name="q" 
                   value="{{ $search }}" 
                   class="form-input" 
                   placeholder="Buscar por cédula, nombre, apellido o número (Ej: 5K-0001)...">
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
            @if($search)
                <a href="{{ route('participants.list') }}" class="btn btn-outline" style="white-space: nowrap;">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Participants Table -->
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Dorsal</th>
                    <th>Corredor</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Fecha Registro</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participants as $p)
                    <tr>
                        <td>
                            <span class="bib-pill-small">{{ $p->bib_number }}</span>
                        </td>
                        <td style="font-weight: 700; color: var(--text-primary);">
                            {{ $p->full_name }}
                        </td>
                        <td style="font-family: var(--font-mono);">
                            {{ $p->national_id }}
                        </td>
                        <td>
                            {{ $p->phone }}
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            {{ $p->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('participants.bib', $p->bib_number) }}" class="btn btn-outline btn-sm" target="_blank" title="Ver dorsal e imprimir">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No se encontraron participantes registrados con los criterios especificados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem;">
        {{ $participants->links() }}
    </div>
</div>
@endsection
