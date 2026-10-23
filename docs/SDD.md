# Documento de Diseño de Sistema (SDD) & Especificación de Desarrollo
## Sistema de Registro y Dorsales Oficiales — Carrera 5K UNELLEZ (51° Aniversario)

**Versión:** 1.2.0  
**Estado:** Aprobado / En Producción  
**Autor:** Antigravity Senior Engineering Team  
**Metodología:** Spec-Driven Development (SDD)  
**Fecha:** 22 de Octubre de 2026  

---

## 1. Visión General del Producto y Objetivos

### 1.1 Propósito
El sistema tiene como objetivo gestionar de manera eficiente, confiable y segura la inscripción masiva de participantes para la **Carrera 5K y Caminata del 51° Aniversario de la UNELLEZ** (*"La Universidad que Siembra"*), a realizarse el **Domingo 23** con salida y llegada en el **Estacionamiento del Rectorado**.

### 1.2 Objetivos Clave
- **Experiencia de Usuario Inmediata**: Presentación en una sola pantalla con el afiche oficial a la izquierda y el formulario de inscripción a la derecha (*side-by-side*).
- **Asignación Única y Atómica de Dorsal**: Garantizar que cada corredor reciba un número correlativo con formato estricto `5K-0001`, `5K-0002`, `5K-0003`, etc., sin riesgo de colisión o saltos, incluso ante peticiones concurrentes simultáneas.
- **Consulta y Reimpresión Autoservicio**: Si un participante ya registrado perdió u olvidó su número, puede ingresar su cédula y el sistema le recupera sus datos con su número asignado y le abre el dorsal oficial listo para imprimir.
- **Tolerancia Cero a Duplicados**: Si una persona intenta registrarse nuevamente con la misma cédula, el sistema la intercepta proactivamente y le devuelve su número ya asignado con opción de impresión directa, evitando errores frustrantes.
- **Dorsal Imprimible con QR**: Generación de un dorsal de competencia (*bib ticket*) con marcas de imperdibles, código QR dinámico de verificación y estilos optimizados para impresión en papel estándar (`@media print`).
- **Herramientas de Mesa de Control**: Panel para los jueces y organizadores con listado de participantes, buscador instantáneo y exportación de nómina a CSV.

---

## 2. Arquitectura de Software

El sistema sigue la arquitectura estándar **MVC (Modelo - Vista - Controlador)** de **Laravel 11+**, complementado con componentes modulares en Blade y JavaScript Vanilla sin dependencias pesadas.

```
+-------------------------------------------------------------------------+
|                              CAPA CLIENTE                               |
|   +-----------------------------------------------------------------+   |
|   | Vistas Blade: index.blade.php / bib.blade.php / list.blade.php  |   |
|   | Estilos: public/css/style.css (Paleta Institucional Figma)      |   |
|   | JavaScript Vanilla: AJAX Fetch, Normalización y Modal Dorsal   |   |
|   +-----------------------------------------------------------------+   |
+-------------------------------------------------------------------------+
                                    |  HTTP / JSON / HTML
                                    v
+-------------------------------------------------------------------------+
|                           CAPA DE APLICACIÓN                            |
|   +-----------------------------------------------------------------+   |
|   | Router (routes/web.php)                                         |   |
|   | Middleware (CSRF, Trimming, Sanitization)                       |   |
|   | Controlador: ParticipantController                              |   |
|   |   - index()        -> Renderiza layout principal y contadores   |   |
|   |   - store()        -> Valida y genera dorsal atómico            |   |
|   |   - lookup()       -> Búsqueda flexible por cédula              |   |
|   |   - bib()          -> Vista dedicada de impresión de dorsal     |   |
|   |   - list()         -> Roster administrativo con paginación      |   |
|   |   - exportCsv()    -> Genera nómina en CSV con BOM UTF-8        |   |
|   +-----------------------------------------------------------------+   |
+-------------------------------------------------------------------------+
                                    |  Eloquent ORM
                                    v
+-------------------------------------------------------------------------+
|                            CAPA DE DATOS                                |
|   +-----------------------------------------------------------------+   |
|   | Modelo: Participant                                             |   |
|   | Métodos: cleanNationalId(), formatNationalId(), getNextBib()    |   |
|   | Base de Datos: SQLite / MySQL / PostgreSQL (Soporte Universal)  |   |
|   | Concurrencia: DB::transaction + lockForUpdate()                 |   |
|   +-----------------------------------------------------------------+   |
+-------------------------------------------------------------------------+
```

---

## 3. Modelo de Datos y Esquema de Base de Datos

### 3.1 Tabla: `participants`
Almacena todos los registros de los atletas inscritos en el evento.

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | NO | PRIMARY KEY | Identificador único autoincremental |
| `bib_number` | `VARCHAR(20)` | NO | UNIQUE | Número de dorsal formateado (`5K-0001`, `5K-0002`, etc.) |
| `bib_sequence` | `INT UNSIGNED` | NO | UNIQUE | Secuencia correlativa numérica (1, 2, 3...) para ordenamiento rápido |
| `first_name` | `VARCHAR(100)` | NO | NO | Nombres del participante (solo letras, capitalizado) |
| `last_name` | `VARCHAR(100)` | NO | NO | Apellidos del participante (solo letras, capitalizado) |
| `national_id` | `VARCHAR(30)` | NO | UNIQUE | Cédula formateada con prefijo (`V-12345678`, `E-87654321`) |
| `national_id_clean`| `VARCHAR(30)` | NO | INDEX | Cédula en dígitos puros (`12345678`) para búsquedas infalibles |
| `phone` | `VARCHAR(30)` | NO | NO | Teléfono de contacto (solo dígitos y símbolos telefónicos) |
| `created_at` | `TIMESTAMP` | SÍ | NO | Fecha y hora exacta de registro |
| `updated_at` | `TIMESTAMP` | SÍ | NO | Fecha de última modificación |

---

## 4. Reglas de Negocio y Validaciones Estrictas

### 4.1 Formato de Número de Corredor (`bib_number`)
- **Estructura:** Prefijo constante `5K-` seguido de una secuencia numérica formateada a mínimo 4 dígitos rellenados con ceros a la izquierda:
  $$\text{Dorsal} = \text{"5K-"} + \text{str\_pad}(\text{secuencia}, 4, \text{'0'}, \text{STR\_PAD\_LEFT})$$
- **Ejemplos:** `5K-0001`, `5K-0002`, `5K-0045`, `5K-0120`, `5K-1000`.
- **Atomicidad:** Se calcula con `Participant::lockForUpdate()->max('bib_sequence')` dentro de una transacción activa `DB::transaction(...)`.

### 4.2 Validación de Campos de Entrada

#### 1. Nombre (`first_name`) y Apellido (`last_name`):
- **Regla:** Obligatorio, longitud máxima 100 caracteres.
- **Restricción de caracteres:** **NO permite números**.
- **Expresión Regular:** `/^[\pL\s\.\'-]+$/u` (permite caracteres latinos, tildes, diéresis y la letra `ñ`).
- **Normalización:** Se aplica `mb_convert_case(trim($str), MB_CASE_TITLE, "UTF-8")` antes de persistir.

#### 2. Cédula de Identidad (`national_id`):
- **Regla:** Obligatorio.
- **Límite de longitud:** **Máximo 9 dígitos numéricos** (longitud entre 5 y 9 dígitos).
- **Restricción de caracteres:** Exclusivamente números en el input (`/^[0-9]+$/`).
- **Prefijo:** Se selecciona mediante selector desplegable (`V-` para venezolanos, `E-` para extranjeros).
- **Unicidad:** Una cédula solo puede tener un único número asignado en toda la base de datos.
- **Búsqueda flexible:** Al consultar, el sistema limpia puntos, guiones y espacios, contrastando contra `national_id_clean`.

#### 3. Teléfono de Contacto (`phone`):
- **Regla:** Obligatorio, máximo 25 caracteres.
- **Restricción de caracteres:** **NO permite letras**.
- **Expresión Regular:** `/^[0-9\-\+\s\(\)]+$/` (permite dígitos, guiones, espacios, signos `+` y paréntesis).

---

## 5. Especificación de Endpoints y Contratos de API

### 5.1 `POST /registro`
Registra un nuevo participante y emite su número de dorsal.

- **Headers:** `Accept: application/json`, `X-CSRF-TOKEN: <token>` (opcional si es petición AJAX exenta).
- **Parámetros del Body (JSON o Form-Data):**
  ```json
  {
    "first_name": "Carlos",
    "last_name": "Pérez",
    "national_id_type": "V",
    "national_id": "24123456",
    "phone": "0414-1234567"
  }
  ```
- **Respuesta de Éxito (HTTP 200 - Nuevo Registro):**
  ```json
  {
    "success": true,
    "already_registered": false,
    "message": "¡Registro exitoso! Tu número de corredor asignado es: 5K-0003",
    "participant": {
      "id": 3,
      "first_name": "Carlos",
      "last_name": "Pérez",
      "phone": "0414-1234567",
      "national_id": "V-24123456",
      "national_id_clean": "24123456",
      "bib_number": "5K-0003",
      "bib_sequence": 3,
      "created_at": "2026-10-22T14:51:20.000000Z"
    }
  }
  ```
- **Respuesta de Cédula Ya Registrada (HTTP 200 - Intercepción Amigable):**
  ```json
  {
    "success": true,
    "already_registered": true,
    "message": "Esta cédula ya se encuentra registrada con el número 5K-0003.",
    "participant": { ... }
  }
  ```
- **Respuesta de Error de Validación (HTTP 422):**
  ```json
  {
    "message": "El nombre no debe contener números.",
    "errors": {
      "first_name": ["El nombre no debe contener números."]
    }
  }
  ```

### 5.2 `GET|POST /consultar`
Consulta el número y datos del corredor a partir de su cédula.

- **Query Parameters:** `?cedula=24123456` o `?national_id=V-24123456`
- **Respuesta Participante Encontrado (HTTP 200):**
  ```json
  {
    "found": true,
    "participant": { ... },
    "bib_url": "http://127.0.0.1:8000/dorsal/5K-0003"
  }
  ```
- **Respuesta No Encontrado (HTTP 200):**
  ```json
  {
    "found": false,
    "message": "No se encontró ningún participante con la cédula ingresada (24123456)."
  }
  ```

### 5.3 `GET /dorsal/{bib_number}`
Renderiza la vista oficial HTML imprimible del dorsal con código QR dinámico.

---

## 6. Sistema de Diseño (Design System Tokens)

El diseño visual está alineado con la propuesta corporativa de Figma para eventos universitarios:

| Token | Valor | Uso |
|---|---|---|
| `--primary` | `#E65100` | Naranja Institucional UNELLEZ (botones, destacados, bordes activos) |
| `--primary-hover` | `#D84315` | Estado hover de botones primarios |
| `--primary-light` | `#FFF3E0` | Fondos de etiquetas y badges secundarios |
| `--secondary` | `#0F172A` | Azul Noche / Midnight (botones secundarios, footer) |
| `--bg-page` | `#F8FAFC` | Fondo general claro de alta legibilidad |
| `--bg-surface` | `#FFFFFF` | Fondo de tarjetas y contenedores |
| `--bg-subtle` | `#F1F5F9` | Fondos de pestañas inactivas y previsualizaciones |
| `--border-color` | `#E2E8F0` | Líneas divisorias y bordes estándar |
| `--font-main` | `'Montserrat', sans-serif` | Tipografía general de interfaz y lectura |
| `--font-bib` | `'Bebas Neue', sans-serif` | Tipografía atlética gigante para el número del dorsal |

---

## 7. Criterios de Aceptación (Testing Checklist)

1. [x] **Flujo de Registro Básico:** Al ingresar datos válidos, se persiste en base de datos y se asigna el siguiente número correlativo `5K-XXXX`.
2. [x] **Secuencialidad Continua:** No existen saltos ni duplicaciones entre registros consecutivos.
3. [x] **Restricción de Nombres:** Rechaza nombres o apellidos que incluyan caracteres numéricos (`123`).
4. [x] **Restricción de Teléfono:** Rechaza teléfonos que contengan letras (`0414-TELEFONO`).
5. [x] **Límite de Cédula:** Restringe la entrada a un máximo de 9 dígitos numéricos en cliente y servidor.
6. [x] **Reimpresión Rápida:** La búsqueda por cédula recupera exitosamente el dorsal para imprimir.
7. [x] **Control de Duplicados:** Al ingresar una cédula existente, se notifica y se presenta el dorsal asignado sin lanzar un error 500.
8. [x] **Impresión Limpia:** `@media print` oculta menús, botones y fondos innecesarios, imprimiendo únicamente el dorsal en hoja carta/A4.
9. [x] **Exportación de Datos:** `/organizador/exportar-csv` descarga la nómina completa en formato compatible con Excel (UTF-8 con BOM).
