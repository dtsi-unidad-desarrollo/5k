# Guía para Agentes de IA y Desarrolladores (CLAUDE.md)
Consulta el documento principal en [AGENTS.md](AGENTS.md) y la especificación completa en [docs/SDD.md](docs/SDD.md).

## Resumen Ejecutivo
- **Proyecto:** Carrera 5K y Caminata 51° Aniversario UNELLEZ.
- **Stack:** Laravel 11 (PHP 8.3), SQLite/MySQL/PostgreSQL, Blade, CSS3 puro.
- **Comando del servidor:** `php artisan serve --port=8000`
- **Comando de pruebas:** `php artisan test`
- **Reglas Críticas:**
  1. Número de corredor con formato secuencial atómico `5K-XXXX` usando `lockForUpdate()`.
  2. Nombres y apellidos **sin números**.
  3. Teléfono **sin letras**.
  4. Cédula **máximo 9 dígitos numéricos**.
  5. Cédulas duplicadas interceptadas amigablemente para reimpresión inmediata.
  6. Paleta institucional Figma (Lienzo claro, naranja UNELLEZ `#E65100`, azul noche `#0F172A`, fuente Montserrat).
