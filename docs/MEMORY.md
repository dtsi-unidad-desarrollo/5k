# Memoria de Arquitectura y Decisiones Técnicas (MEMORY.md)
## Proyecto: Sistema de Registro y Dorsales — Carrera 5K UNELLEZ (51° Aniversario)

**Fecha de Creación:** 22 de Octubre de 2026  
**Última Actualización:** 22 de Octubre de 2026  
**Estado:** Estable / En Producción  

---

## 1. Contexto del Proyecto

El proyecto nace con motivo de la **Carrera 5K y Caminata del 51° Aniversario de la UNELLEZ** (*La Universidad que Siembra*), pautada para el **Domingo 23** en Barinas, Venezuela, con salida y llegada en el **Estacionamiento del Rectorado**.

El objetivo fue crear una solución web ágil, visualmente atractiva e infalible para el registro de miles de corredores, asignando a cada uno un número correlativo con formato `5K-0001`, permitiendo la reimpresión rápida de dorsales para corredores olvidadizos y dotando a los organizadores de un listado exportable en CSV para la logística del evento.

---

## 2. Registro de Decisiones Arquitectónicas (ADRs)

### ADR-001: Asignación Atómica del Número de Participante (`5K-XXXX`)
- **Problema:** En eventos deportivos masivos, múltiples usuarios envían el formulario de registro al mismo segundo. Un simple `Participant::count() + 1` o `max('id')` sin bloqueo genera condiciones de carrera (*race conditions*) donde dos participantes reciben el mismo número de dorsal.
- **Decisión:** Implementar el cálculo dentro de una transacción de base de datos (`DB::transaction`) ejecutando `Participant::lockForUpdate()->max('bib_sequence')`.
- **Efecto:** Garantiza 100% de consistencia atómica y correlatividad matemática estricta sin colisiones.

### ADR-002: Almacenamiento Dual y Normalización de Cédulas
- **Problema:** Los usuarios escriben su cédula de múltiples formas: con prefijo (`V-24123456`), sin prefijo (`24123456`), con puntos (`24.123.456`) o con espacios. Si se compara como texto literal, las búsquedas fallan.
- **Decisión:** Guardar dos columnas en la tabla `participants`:
  1. `national_id`: Cédula formateada oficial (`V-24123456`).
  2. `national_id_clean`: Dígitos puros (`24123456`), indexada para búsquedas ultrarrápidas.
- **Efecto:** Cualquier usuario que busque `24.123.456`, `24123456` o `V-24123456` encuentra de inmediato su registro sin frustraciones.

### ADR-003: Intercepción Amigable de Cédulas Duplicadas
- **Problema:** Un participante que ya se registró hace días puede olvidar que lo hizo o perder su número y volver a llenar el formulario. Lanzar un error rojo de "Cédula ya registrada" genera incertidumbre sobre cuál era su número.
- **Decisión:** Al detectar una cédula existente en `/registro`, el controlador no lanza error, sino que responde con éxito (`already_registered: true`), felicita al usuario, le recuerda su número asignado y le abre automáticamente el modal con el dorsal oficial listo para imprimir.
- **Efecto:** Cero fricción, reducción drástica de consultas a los organizadores.

### ADR-004: Adopción del Sistema de Diseño Figma (Lienzo Claro y Naranja Institucional)
- **Problema:** El prototipo inicial utilizaba un fondo oscuro (*dark mode*). El usuario solicitó adaptar la estética al diseño oficial de Figma (`Propuesta`).
- **Decisión:** Migrar a un lienzo claro corporativo (`#F8FAFC`) con tarjetas en blanco puro (`#FFFFFF`), bordes superiores en Naranja UNELLEZ (`#E65100`), detalles en Azul Noche (`#0F172A`) y la tipografía geométrica **Montserrat** combinada con **Bebas Neue** para los números de competición.
- **Efecto:** Acabado institucional, profesional y perfectamente integrado con la imagen universitaria.

### ADR-005: Generación de Dorsal Imprimible Nativo vía CSS `@media print`
- **Problema:** Generar PDFs en el servidor mediante herramientas como DomPDF o wkhtmltopdf incrementa el consumo de memoria del servidor y requiere binarios externos.
- **Decisión:** Maquetar el dorsal en HTML5 semántico con reglas CSS nativas `@media print`, simulando los agujeros de imperdibles, cabecera institucional y código QR dinámico.
- **Efecto:** Cero consumo adicional de servidor, compatibilidad instantánea con teléfonos y computadoras, e impresión inmediata con `window.print()`.

---

## 3. Estado Actual de la Base de Código

| Componente | Estado | Cobertura / Verificación |
|---|---|---|
| Modelo `Participant` | Completo | Fillables, cast y helpers de saneamiento validados |
| Controlador `ParticipantController` | Completo | Registro, consulta, dorsal, listado y CSV operativos |
| Rutas `routes/web.php` | Completo | 6 rutas REST/Web registradas |
| Vistas Blade (`index`, `bib`, `list`) | Completo | Responsivas, probadas en resoluciones desktop y mobile |
| Pruebas Automatizadas | 100% | 12 pruebas exitosas en `ParticipantRegistrationTest.php` |

---

## 4. Hoja de Ruta / Próximas Mejoras Recomendadas (Roadmap)

Si otros desarrolladores o agentes desean seguir ampliando la aplicación, se recomiendan los siguientes módulos:

1. **Notificación Automática por WhatsApp / Email**:
   - Integrar un webhook o servicio de mensajería (como Twilio o Evolution API) para enviar el dorsal y el código QR directamente al WhatsApp del corredor al registrarse.
2. **Cronómetro y Registro de Tiempos en Meta**:
   - Módulo para jueces de llegada donde escaneando el código QR del dorsal se registre la hora y tiempo oficial del corredor en la carrera.
3. **Categorización por Edades y Género**:
   - Agregar campo de fecha de nacimiento o edad y sexo para clasificar automáticamente a los atletas en categorías (Juvenil, Libre, Máster A, B, C).
