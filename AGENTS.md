# Guía de Desarrollo para Desarrolladores y Agentes de IA (AGENTS.md)
## Proyecto: Sistema de Registro y Dorsales — Carrera 5K UNELLEZ

Este documento establece el contexto técnico, las convenciones de código, las reglas de negocio críticas y las directrices operativas para cualquier desarrollador humano o agente de Inteligencia Artificial (Claude, Gemini, Cursor, Copilot, Antigravity, etc.) que trabaje en esta base de código.

---

## 1. Información General del Proyecto

- **Nombre:** Carrera 5K y Caminata 51° Aniversario UNELLEZ
- **Ubicación:** `C:\Users\Jesus\.gemini\antigravity-ide\scratch\carrera5k-unellez`
- **Framework:** Laravel 11.x (PHP 8.3+)
- **Base de Datos:** Soporte universal para **SQLite** (por defecto), **MySQL** (Laragon/XAMPP) y **PostgreSQL**.
- **Frontend:** Vistas Blade con JavaScript Vanilla modular y estilos CSS puros (sin dependencias pesadas de NPM en runtime).
- **Diseño:** Basado en la propuesta corporativa de Figma (Lienzo claro institucional, acentos Naranja UNELLEZ `#E65100`, Azul Noche `#0F172A` y tipografía *Montserrat* + *Bebas Neue*).

---

## 2. Estructura de Directorios Clave

```
carrera5k-unellez/
├── app/
│   ├── Http/Controllers/
│   │   └── ParticipantController.php    <-- Toda la lógica de registro, consulta, dorsal y CSV
│   └── Models/
│       └── Participant.php              <-- Modelo Eloquent, normalizadores y generador atómico 5K
├── database/
│   ├── migrations/
│   │   └── 2026_10_22_142618_create_participants_table.php  <-- Estructura de tabla e índices
│   └── seeders/
│       └── DatabaseSeeder.php           <-- Corredores de prueba (V-20123456, V-24789012)
├── docs/
│   ├── SDD.md                           <-- System Design Document & Especificaciones SDD
│   └── MEMORY.md                        <-- Memoria de decisiones arquitectónicas (ADRs)
├── public/
│   ├── css/
│   │   └── style.css                    <-- Sistema de diseño, tokens, modal, dorsal y @media print
│   └── images/
│       └── flyer-5k.jpg                 <-- Afiche oficial del evento
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                <-- Layout maestro (Navbar, Modal de Dorsal, Lightbox)
│   └── participants/
│       ├── index.blade.php              <-- Pantalla principal (Afiche + Formulario + Reimpresión)
│       ├── bib.blade.php                <-- Dorsal oficial imprimible individual
│       └── list.blade.php               <-- Roster administrativo con buscador
├── routes/
│   └── web.php                          <-- Declaración de todas las rutas del sistema
└── tests/Feature/
    └── ParticipantRegistrationTest.php  <-- Suite completa de 12 pruebas de integración
```

---

## 3. Reglas de Negocio Críticas (REGLAS OBLIGATORIAS)

Cualquier cambio o extensión en el código **DEBE** respetar estrictamente las siguientes reglas:

### 3.1 Formato de Número de Participante
- El formato es estrictamente `5K-XXXX` (prefijo `5K-` seguido de mínimo 4 dígitos correlativos rellenos con ceros, ej.: `5K-0001`, `5K-0002`).
- **Seguridad ante concurrencia:** El número debe calcularse siempre dentro de una transacción de base de datos (`DB::transaction`) utilizando `lockForUpdate()`. **NUNCA** calcules el número sumando `count() + 1` sin bloqueo, ya que causará colisiones si dos usuarios envían el formulario en el mismo instante.

### 3.2 Validación de Campos
1. **Nombres y Apellidos:**
   - **PROHIBIDO contener números.** Solo se admiten letras del alfabeto, espacios, tildes, diéresis y la letra `ñ`.
   - Expresión regular: `/^[\pL\s\.\'-]+$/u`.
2. **Cédula de Identidad:**
   - **Límite estricto de 9 caracteres.** Longitud admitida entre 5 y 9 dígitos.
   - **Solo números.** Sin puntos ni guiones dentro del campo numérico.
   - En el cliente se debe mantener `maxlength="9"` y el filtro `oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"`.
3. **Teléfono:**
   - **PROHIBIDO contener letras.** Solo números y caracteres de formato telefónico (`+`, `-`, `()`, espacios).
   - Expresión regular: `/^[0-9\-\+\s\(\)]+$/`.

### 3.3 Tratamiento de Corredores Duplicados
- Si un usuario ingresa una cédula que ya existe en el sistema:
  - **NO** lanzar una excepción HTTP 500 ni un mensaje de error genérico.
  - El sistema debe responder de forma amigable (`already_registered: true`) indicándole: *"Esta cédula ya se encuentra registrada con el número 5K-XXXX"* y desplegándole inmediatamente su dorsal asignado listo para imprimir.

### 3.4 Impresión del Dorsal Oficial
- Las reglas de impresión deben preservarse en `@media print` en `public/css/style.css`.
- Al imprimir, se deben ocultar la barra de navegación, el pie, los botones y los fondos, imprimiendo exclusivamente la tarjeta del dorsal en formato estándar de papel.

---

## 4. Comandos de Trabajo para Desarrolladores y Agentes

### 4.1 Iniciar el Servidor de Desarrollo
```bash
php artisan serve --port=8000
```
La aplicación queda disponible en `http://127.0.0.1:8000`.

### 4.2 Ejecutar la Suite de Pruebas Automatizadas
**SIEMPRE** ejecuta las pruebas tras modificar cualquier controlador, modelo o validación:
```bash
php artisan test
```
*Debe reportar 12 pruebas pasadas y 31 aserciones exitosas.*

### 4.3 Ejecutar Migraciones y Datos de Prueba
```bash
# Migrar esquema limpio
php artisan migrate

# Migrar y poblar con corredores de demostración
php artisan migrate:fresh --seed
```

---

## 5. Configuración de Base de Datos

El archivo `.env` permite alternar de motor de base de datos sin alterar el código:

- **SQLite (Por defecto):** No requiere ningún software adicional. La base de datos se guarda en `database/database.sqlite`.
- **MySQL (Laragon / XAMPP):**
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=carrera5k_unellez
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- **PostgreSQL:**
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=127.0.0.1
  DB_PORT=5432
  DB_DATABASE=carrera5k_unellez
  DB_USERNAME=postgres
  DB_PASSWORD=tu_password
  ```

---

## 6. Pautas para Agentes de IA al Agregar Nuevas Funcionalidades

1. **Preserva la estructura de archivos:** Mantén el código modular en `ParticipantController.php` y `Participant.php`.
2. **No agregues librerías frontend pesadas:** El proyecto está diseñado para cargar a velocidad instantánea con CSS puro y Blade. Evita instalar frameworks pesados de JS (React, Vue) a menos que el usuario lo solicite expresamente.
3. **Mantén las pruebas al día:** Si agregas una nueva regla de validación o un endpoint, crea su correspondiente método de prueba en `tests/Feature/ParticipantRegistrationTest.php`.
4. **Respeta la guía de estilos:** Utiliza las variables CSS `:root` (`--primary`, `--secondary`, `--bg-page`, etc.) en lugar de estilos ad-hoc con colores fijos.
