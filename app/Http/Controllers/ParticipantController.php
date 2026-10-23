<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    /**
     * Muestra la pantalla de registro.
     */
    public function index(): View
    {
        $totalCount = Participant::count();
        $recentParticipants = Participant::latest()->take(6)->get();

        return view('participants.index', [
            'totalCount' => $totalCount,
            'recentParticipants' => $recentParticipants,
        ]);
    }

    /**
     * Store a newly registered participant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^[0-9\-\+\s\(\)]+$/'],
            'national_id' => ['required', 'string', 'max:9', 'regex:/^[0-9]+$/'],
            'national_id_type' => ['nullable', 'in:V,E'],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.regex' => 'El nombre no debe contener números.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.regex' => 'El apellido no debe contener números.',
            'phone.required' => 'El número de teléfono es obligatorio.',
            'phone.regex' => 'El teléfono no debe contener letras.',
            'national_id.required' => 'La cédula de identidad es obligatoria.',
            'national_id.max' => 'La cédula no debe tener más de 9 dígitos.',
            'national_id.regex' => 'La cédula debe contener exclusivamente números.',
        ]);

        $prefix = $request->input('national_id_type', 'V');
        $rawId = $validated['national_id'];
        $cleanDigits = Participant::cleanNationalId($rawId);

        if (empty($cleanDigits) || strlen($cleanDigits) < 5 || strlen($cleanDigits) > 9) {
            $msg = 'Por favor introduce una cédula de identidad válida (entre 5 y 9 dígitos).';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return back()->withInput()->withErrors(['national_id' => $msg]);
        }

        $formattedId = Participant::formatNationalId($rawId, $prefix);

        // Revisa si ya existe
        $existing = Participant::findByAnyNationalId($cleanDigits);
        if ($existing) {
            $msg = "Esta cédula ya se encuentra registrada con el número {$existing->bib_number}.";
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'already_registered' => true,
                    'message' => $msg,
                    'participant' => $existing,
                ]);
            }

            return back()->with([
                'already_registered' => true,
                'participant' => $existing,
                'info_message' => $msg,
            ]);
        }

        // registro en una transaccion
        try {
            $participant = DB::transaction(function () use ($validated, $formattedId, $cleanDigits) {
                $bibData = Participant::getNextBib();

                return Participant::create([
                    'first_name' => mb_convert_case(trim($validated['first_name']), MB_CASE_TITLE, 'UTF-8'),
                    'last_name' => mb_convert_case(trim($validated['last_name']), MB_CASE_TITLE, 'UTF-8'),
                    'phone' => trim($validated['phone']),
                    'national_id' => $formattedId,
                    'national_id_clean' => $cleanDigits,
                    'bib_number' => $bibData['bib_number'],
                    'bib_sequence' => $bibData['sequence'],
                ]);
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'already_registered' => false,
                    'message' => "¡Registro exitoso! Tu número de corredor asignado es: {$participant->bib_number}",
                    'participant' => $participant,
                ]);
            }

            return back()->with([
                'success' => true,
                'participant' => $participant,
                'success_message' => "¡Registro completado! Tu número asignado es: {$participant->bib_number}",
            ]);
        } catch (\Exception $e) {
            report($e);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al procesar el registro. Intente nuevamente.',
                ], 500);
            }

            return back()->withInput()->with('error_message', 'Error al procesar el registro.');
        }
    }

    /**
     * Look up participant by national ID (for reprint or checking number).
     */
    public function lookup(Request $request)
    {
        $query = $request->input('cedula') ?? $request->input('national_id') ?? $request->input('query');

        if (!$query) {
            if ($request->wantsJson()) {
                return response()->json([
                    'found' => false,
                    'message' => 'Debe ingresar una cédula para buscar.',
                ], 400);
            }
            return back()->with('lookup_error', 'Debe ingresar una cédula.');
        }

        $participant = Participant::findByAnyNationalId($query);

        if (!$participant) {
            if ($request->wantsJson()) {
                return response()->json([
                    'found' => false,
                    'message' => "No se encontró ningún participante con la cédula ingresada ({$query}).",
                ], 200);
            }

            return back()->with([
                'lookup_not_found' => true,
                'lookup_query' => $query,
                'lookup_message' => "No se encontró ningún participante registrado con la cédula {$query}.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'found' => true,
                'participant' => $participant,
                'bib_url' => route('participants.bib', $participant->bib_number),
            ]);
        }

        return back()->with([
            'lookup_found' => true,
            'participant' => $participant,
        ]);
    }

    /**
     * Dedicated printable bib view.
     */
    public function bib(string $bibNumber): View
    {
        $participant = Participant::where('bib_number', $bibNumber)->firstOrFail();

        return view('participants.bib', [
            'participant' => $participant,
        ]);
    }

    /**
     * Admin / Organizers participant list with search.
     */
    public function list(Request $request): View
    {
        $search = $request->input('q');

        $participants = Participant::query()
            ->when($search, function ($query, $search) {
                $clean = Participant::cleanNationalId($search);
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('bib_number', 'LIKE', "%{$search}%")
                    ->orWhere('national_id', 'LIKE', "%{$search}%")
                    ->when(!empty($clean), function ($q) use ($clean) {
                        $q->orWhere('national_id_clean', 'LIKE', "%{$clean}%");
                    });
            })
            ->orderBy('bib_sequence', 'asc')
            ->paginate(25)
            ->withQueryString();

        $totalCount = Participant::count();

        return view('participants.list', [
            'participants' => $participants,
            'search' => $search,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Export registered participants to CSV for the race organizers.
     */
    public function exportCsv()
    {
        $participants = Participant::orderBy('bib_sequence', 'asc')->get();

        $csvHeader = ['Numero_Dorsal', 'Secuencia', 'Nombres', 'Apellidos', 'Cedula', 'Telefono', 'Fecha_Registro'];

        $callback = function () use ($participants, $csvHeader) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($file, $csvHeader);

            foreach ($participants as $p) {
                fputcsv($file, [
                    $p->bib_number,
                    $p->bib_sequence,
                    $p->first_name,
                    $p->last_name,
                    $p->national_id,
                    $p->phone,
                    $p->created_at->format('d/m/Y H:i:s'),
                ]);
            }
            fclose($file);
        };

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="participantes_carrera5k_unellez_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
