# Generación del informe técnico (PDF)

Documentación de flujos, validaciones y campos que intervienen al crear o actualizar un informe técnico en ZGROUP Informes.

## Flujos principales

| Acción | URL / disparador | Endpoint | Resultado |
|--------|------------------|----------|-----------|
| Nuevo informe | Formulario técnico → **Generar PDF** | `guardar.php` | PDF en `/informes`, registro en `informes`, cierre de preinspección |
| Editar informe | `?modo=editar_informe&id=N` → **Generar PDF** | `actualizar_informe.php` | PDF nuevo (nombre único), actualización de `informes` y preinspección vinculada |
| Inspección preliminar | Botón guardar preliminar | `guardar_preliminar.php` | Registro en `inspecciones_preliminares` |

En ambos flujos el PDF se construye en el navegador con **jsPDF** (`buildPDF()` en `public/assets/js/tecnicos/formulario-core.js`) y se envía como `multipart/form-data` junto con metadatos y una instantánea JSON del formulario.

## Validaciones antes de generar / actualizar

Orden de ejecución en `generatePDF()`:

1. **Datos generales** — orden, cliente, dirección, técnico, fecha (`validateGeneralRequired`).
2. **Preinspección** — debe existir `preinspeccionId` (excepto en edición).
3. **Trabajos** — al menos un trabajo seleccionado con campos obligatorios (`validateWorkSections`).
4. **Repuestos** (`validarRepuestos`, definido en `formulario-modules-late.js`):
   - **Condición comercial (Reefer + Alquiler o Venta):** exige **al menos un repuesto** registrado en cualquiera de:
     - tabla de materiales por trabajo (`state.selected[*].repuestosTrabajo`);
     - textarea `repuestosManual`;
     - tabla final `#repuestosSelectedList`.
   - **Alquiler:** mensaje *"En alquiler debes registrar la pieza que será reemplazada…"*
   - **Venta:** mensaje *"En venta debes registrar al menos un repuesto pendiente de cotización."*
   - **Repuesto requerido genérico** (`requiereRepuesto = si`): mismo conteo mínimo de materiales.
5. **Control final** — estado final, firmas, horarios (`validarControlFinal`).

La misma regla de condición comercial se valida en servidor en `guardar.php` y `actualizar_informe.php` (`zgroup_validate_condicion_comercial_repuestos` en `app/Helpers/helpers.php`).

### Reglas de negocio por modalidad comercial

| Modalidad | Equipo | Repuesto en este servicio | Validación |
|-----------|--------|---------------------------|------------|
| **Alquiler** | Reefer | Pieza a **reemplazar** en la atención | Obligatorio ≥ 1 repuesto |
| **Venta** | Reefer | Repuestos **pendientes de cotización** (trabajo futuro) | Obligatorio ≥ 1 repuesto |
| Otra / vacía | Cualquiera | Según decisión del técnico (`requiereRepuesto`) | Solo si marca "Sí" |

Campo de modalidad: `zgModalidadComercial` (proviene de la preinspección / catálogo Odoo).

## Instantánea del formulario (`datos_json`)

Al guardar se serializa con `window.zgCollectReportSnapshot()`:

- **`fields`:** todos los `input`, `select` y `textarea` con `id` (valor, tipo, checked).
- **`state`:** `tecnicoId`, `tecnicoNombre`, `customSeq`, `selected` (trabajos, campos, fotos, `repuestosTrabajo`).
- **`preEvidence`:** evidencias de la preinspección.
- **`savedAt`:** ISO timestamp.

En edición, la **fecha** enviada por POST tiene prioridad sobre la instantánea (`actualizar_informe.php`).

## Campos POST al guardar / actualizar

| Campo POST | Origen formulario | Uso |
|------------|-------------------|-----|
| `pdf` | Blob jsPDF | Archivo en `/informes` |
| `tecnico_id` | `state.tecnicoId` | FK `informes.tecnico_id` |
| `orden` | `#orden` | N° de reporte / cotización |
| `odoo_ticket_ref` | `#odooTicketRef` | Ticket Odoo |
| `cliente`, `direccion` | Datos generales | Cabecera PDF y BD |
| `direccion_coords` | `#direccionCoords` | Lat/long en preinspección |
| `fecha` | `#fecha` | Fecha del informe |
| `trabajos` | Resumen de trabajos seleccionados | Texto `trabajo1 \| trabajo2` |
| `tipo_equipo` | `#zgTipoEquipo` | Reefer / Genset |
| `tamano_contenedor` | `#zgTamanoContenedor` | Tamaño contenedor |
| `hora_inicio_servicio`, `hora_fin_servicio` | Control final | Tiempos de servicio |
| `preinspeccion_id` | `#preinspeccionId` | Vínculo con preinspección |
| `datos_json` | Instantánea completa | `informes.datos_json` |
| `informe_id` | Solo edición | ID a actualizar |
| `repuestos_manual`, `repuestos_actualizados` | Solo edición | Sincroniza `repuestos_manual` |

## Secciones del PDF (`buildPDF`)

### 1. Cabecera y datos generales

- N° de reporte (`orden`), fecha, cliente, técnico, dirección/ubicación, coordenadas, observaciones generales (`obs`).

### 2. Inspección preliminar del equipo

Campos incluidos si tienen valor:

- Identificación: contenedor/equipo, serial, marca, modelo, controlador, año, refrigerante.
- Parámetros iniciales: set point, temperaturas, presiones (si aplica), voltajes L1-L2 / L2-L3 / L1-L3.
- Estado inicial, observación inicial.

Origen: campos `#equipoNo`, `#serialUnidad`, `#marcaEquipo`, etc., rellenados desde preinspección o edición.

### 3. Trabajos realizados (una sección por trabajo)

Por cada ítem en `state.selected`:

- Título del trabajo.
- **Automático:** actividades, hallazgos, acciones, checklist PTI (si aplica).
- **Campos manuales** según catálogo `CAMPOS[id]`.
- Detalle / observaciones del panel.
- **Materiales del trabajo** (`repuestosTrabajo`) — tabla código, detalle, cantidad, unidad.
- **Fotos** del trabajo (cuadrícula 2 columnas con pie de foto).

### 4. Materiales de supervisión (si aplica)

Salida asignada por supervisión: reporte, técnico, equipo, apoyo, tabla de materiales preparados.

### 5. Repuestos a considerar

Texto parseado de `repuestosManual` cuando `requiereRepuesto = si` (código | detalle | cantidad).

### 6. Control final del equipo

- Horas inicio/fin de servicio.
- Estado final, set point final, temperaturas y voltajes finales.
- ¿Requiere otro mantenimiento? (`zgRequiereOtroMantenimiento`, tipo, motivo).

En condición comercial con repuestos, el sistema puede marcar automáticamente mantenimiento futuro y el motivo según Alquiler/Venta.

### 7. Conformidad y firmas (página dedicada)

- Firma del técnico (`firmaTecnico`).
- Firma del responsable del cliente / supervisor (`firmaAdmin`, nombre y cargo).

## Tablas de base de datos afectadas

### `informes`

`tecnico_id`, `orden`, `cliente`, `direccion`, `fecha`, `trabajos`, `archivo`, `datos_json`, `repuestos_manual`, `tipo_equipo`, `tamano_contenedor`, `hora_inicio_servicio`, `hora_fin_servicio`, `odoo_ticket_ref`, `preinspeccion_id`, `actualizado_en`.

### `inspecciones_preliminares` (si hay `preinspeccion_id`)

Se sincronizan datos generales, equipo, parámetros iniciales, modalidad comercial, evidencias y tiempos de servicio desde la instantánea.

## Integraciones posteriores al guardado

- **Odoo:** adjunto del PDF al ticket (`zgOdooSyncInforme`).
- **Catálogos locales:** `clientes_catalogo`, `cotizaciones_catalogo`.
- **Notificaciones** (solo nuevo informe): Telegram / WhatsApp vía `guardar.php`.

## Archivos de referencia

| Archivo | Rol |
|---------|-----|
| `public/assets/js/tecnicos/formulario-core.js` | `generatePDF`, `buildPDF`, validaciones base |
| `public/assets/js/tecnicos/formulario-modules-late.js` | Reglas comerciales Alquiler/Venta, `validarRepuestos` |
| `guardar.php` | Persistencia informe nuevo |
| `actualizar_informe.php` | Persistencia edición desde panel |
| `app/Helpers/helpers.php` | Validación servidor condición comercial |

## Ejemplo: editar informe #21

URL: `http://localhost:8877/index.php?modo=editar_informe&id=21`

- Carga informe y preinspección asociada (#26).
- Si modalidad comercial es **Alquiler** o **Venta** y el equipo es **Reefer**, debe existir al menos un repuesto antes de pulsar **Generar PDF**.
- Tras validación, se genera el PDF, se llama a `actualizar_informe.php` y se reemplaza el archivo en `/informes` con nombre nuevo (evita caché del navegador).
