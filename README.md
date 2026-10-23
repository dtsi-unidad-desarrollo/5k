# Sistema de Registro y Dorsales Oficiales - Carrera 5K UNELLEZ 🏃💨
### 51° Aniversario UNELLEZ &bull; La Universidad que Siembra

Sistema web desarrollado con **Laravel**, arquitectura limpia y vistas **Blade** responsivas para el registro de participantes, asignación atómica de números de corredor (`5K-0001`, `5K-0002`, etc.) y reimpresión de dorsales con código QR.

---

## 🚀 Características Principales

1. **Diseño Side-by-Side Moderno**:
   - Afiche oficial del evento en la columna izquierda con zoom lightbox y resumen de ruta.
   - Formulario de inscripción oficial en la columna derecha con diseño deportivo, glassmorphism y micro-animaciones.

2. **Campos del Formulario**:
   - **Nombre** y **Apellido** (con capitalización automática y saneamiento).
   - **Cédula de Identidad** (selector V-/E- con normalizador numérico para evitar duplicados por formato).
   - **Teléfono de Contacto**.

3. **Asignación de Número Único de Participante**:
   - Formato oficial: `5K-0001`, `5K-0002`, `5K-0003`, ..., secuencial y de ancho fijo.
   - **Garantía de concurrencia**: Transacciones de base de datos con bloqueo atómico (`lockForUpdate()`), evitando números duplicados si varios corredores se registran simultáneamente.

4. **Reimpresión y Consulta de Dorsal**:
   - Pestaña dedicada para consultar por número de cédula.
   - Si el participante olvidó o perdió su número, simplemente escribe su cédula y el sistema le muestra sus datos con el número asignado y abre el comprobante listo para imprimir.
   - **Manejo inteligente de duplicados**: Si un corredor ya registrado intenta volver a llenar el formulario, el sistema le notifica amistosamente y le presenta su número y dorsal ya generado.

5. **Dorsal Oficial (Bib) Imprimible con QR**:
   - Diseño idéntico a un dorsal real de maratón (con marcas de imperdibles, cabecera institucional UNELLEZ, número en tipografía gigante y código QR de verificación).
   - Optimizado con reglas `@media print` para imprimir en formato estándar de papel con un solo clic.

6. **Panel para Organizadores**:
   - Ruta `/organizador/participantes` con buscador en tiempo real.
   - Botón de exportación directa a **CSV** para planillas de mesa el día de la carrera.

---

## 🛠️ Requisitos e Instalación

- **PHP 8.2+** (Probado y funcionando en PHP 8.3)
- **Composer 2+**
- **Base de Datos**: MySQL, PostgreSQL o SQLite (incluido por defecto sin configuración previa).

### Pasos rápidos para ejecutar:

1. Abrir terminal en la carpeta del proyecto:
   ```bash
   cd C:\Users\Jesus\.gemini\antigravity-ide\scratch\carrera5k-unellez
   ```

2. Ejecutar las migraciones y datos de prueba:
   ```bash
   php artisan migrate --seed
   ```

3. Iniciar el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

4. Abrir en el navegador:
   ```
   http://127.0.0.1:8000
   ```

---

## 🗄️ Configuración de Base de Datos (MySQL / PostgreSQL / SQLite)

En el archivo `.env`, puedes alternar el motor de base de datos en cualquier momento:

### Para MySQL (Laragon / XAMPP):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carrera5k_unellez
DB_USERNAME=root
DB_PASSWORD=
```
*(Luego ejecuta: `php artisan migrate`)*

### Para PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=carrera5k_unellez
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```
*(Luego ejecuta: `php artisan migrate`)*

---

## 🛣️ Rutas del Sistema

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/` | Página principal (Flyer + Formulario de Registro + Pestaña de Reimpresión) |
| `POST` | `/registro` | Endpoint de registro y generación de número `5K-XXXX` |
| `GET/POST` | `/consultar` | Búsqueda por cédula para reimprimir número |
| `GET` | `/dorsal/{bib_number}` | Vista individual del dorsal oficial imprimible con QR |
| `GET` | `/organizador/participantes` | Listado y buscador de inscritos |
| `GET` | `/organizador/exportar-csv` | Descarga de nómina en formato CSV para los jueces |

---

## 🧪 Pruebas Automatizadas

Para validar la integridad del sistema:
```bash
php artisan test
```
Todas las pruebas (registro secuencial, unicidad de cédula, consulta y dorsal) se ejecutan con éxito.
