
# Session Title
_A short and distinctive 5-10 word descriptive title for the session. Super info dense, no filler_

Debugging Token Tracking: vacante_id Not Saving for Prefiltro/Video/Pruebas

# Current State
_What is actively being worked on right now? Pending tasks not yet completed. Immediate next steps._

**✅ COMPREHENSIVE IA ENDPOINT DOCUMENTATION COMPLETE - READY FOR CONTINUATION**

**Completed work:**
- Fixed vacante_id tracking for 3 IA endpoints (generar-preguntas-prefiltro, generar-preguntas-videoentrevista, generar-pruebas-psicometricas)
- Modified `public/src/ofertas-laborales/js/ia.js` to send vacante_id and use correct routes
- Modified `OfertaLaboralController::generarPruebasConIA()` to pass contexto_tracking
- **COMPLETED: Comprehensive endpoint analysis and documentation**

**Created documentation file:** `ENDPOINTS_IA_ANALISIS_COMPLETO.md`
- Complete inventory of all 11 FastAPI OpenAI endpoints
- Mapped 8 PHP backend calls to their endpoints
- Identified 10 JavaScript calls across 2 files (3 fixed, 7 need modification)
- Detailed ANTES/DESPUÉS code for 4 functions in vacantes-ia.js
- 4-phase testing plan with validation queries
- Comprehensive checklist for 100% coverage

**Endpoint status summary:**
- **Backend:** 10/11 correct tracking, 1 incomplete (obtener-informacion-hv has empty contexto_tracking)
- **Frontend:** 30% coverage (3/10 calls fixed in ia.js), 70% remaining (7 calls in vacantes-ia.js need same modifications)
- **11 FastAPI endpoints fully mapped:**
  1. generar-vacante - ✅ Backend OK, ⚠️ Frontend partial (ia.js OK, vacantes-ia.js needs fix)
  2. generar-preguntas-prefiltro - ✅ Backend OK, ⚠️ Frontend partial (ia.js OK, vacantes-ia.js needs fix)
  3. generar-preguntas-videoentrevista - ✅ Backend OK, ⚠️ Frontend partial (ia.js OK, vacantes-ia.js needs fix)
  4. generar-pruebas-psicometricas - ✅ Backend OK, ⚠️ Frontend partial (ia.js OK, vacantes-ia.js needs fix)
  5. obtener-informacion-hv - ⚠️ Backend incomplete (empty contexto_tracking), called from PerfilLaboralController line 80
  6. calificar-pregunta-abierta - ✅ Backend OK (EtapaVacanteUsuarioService line 369)
  7. transcribir-respuesta - ✅ Backend OK (EtapaVacanteUsuarioService line 543)
  8. generar-orden-servicio - ✅ Frontend OK (ia.js line 10 via generarInformacion)
  9-11. mejores-candidatos, comparar-candidatos, generar-orientacion-vocacional - ❓ No callers found

**Next steps for continuation in new chat:**
1. Modify `public/js/vacantes/vacantes-ia.js` (4 functions, exact code provided in documentation)
2. Evaluate if obtener-informacion-hv should include vacante_id (depends on use case)
3. Search for remaining 3 endpoints (mejores-candidatos, comparar-candidatos, generar-orientacion-vocacional)
4. Execute comprehensive testing plan from documentation
5. Achieve 100% endpoint coverage validation

**All changes successfully applied (implementation complete):**

1. **Function `generarInformacion()` (lines 143-164) - ✅ COMPLETED:**
   - Changed URL from `/ofertas-laborales/generar-informacion-ia` to `/${ruta}` (direct VacanteController routes)
   - Added `vacante_id: $('#id').val()` to capture vacante ID from hidden input (line 35 of crear.blade.php)
   - Changed `descripcion: ...` to `[llave]: $('#descripcion_general_oferta').val()` (computed property for dynamic key)
   - Now routes to `/generar-preguntas-prefiltro`, `/generar-preguntas-videoentrevista` with full IATrait tracking

2. **Function `escogerPruebasPsicometricasConIA()` (lines 173-189) - ✅ COMPLETED (hybrid approach):**
   - Keeps legacy URL `/ofertas-laborales/generar-pruebas-ia` (cannot use VacanteController due to validation requiring 5 fields frontend doesn't have)
   - Added `vacante_id: $('#id').val()` (line 178) to capture vacante ID
   - Keeps original data structure: `nombre_vacante: ...` and `pruebas_disponibles` array
   - Backend fix applied to enable tracking through this legacy route

3. **Method `OfertaLaboralController::generarPruebasConIA()` (lines 824-851) - ✅ COMPLETED:**
   - Added line 830: `$vacante_id = $request->input('vacante_id');`
   - Changed `realizarPeticionIA()` call (lines 833-839) to use named parameters with contexto_tracking
   - Now passes `contexto_tracking: ['vacante_id' => $vacante_id]` enabling full IATrait tracking for legacy route

**Vacante ID source:**
View `resources/views/ofertas-laborales/paginas/crear.blade.php` line 35: `<input type="hidden" id="id" name="id" @if($tipo_formulario == 'Editar') value="{{ $oferta_laboral->id }}" @endif>`

**Validation queries to confirm fix:**
```sql
SELECT id, vacante_id, ruta_endpoint, tokens_totales, created_at
FROM consumos_tokens_openai
WHERE ruta_endpoint IN ('generar-preguntas-prefiltro', 'generar-preguntas-videoentrevista', 'generar-pruebas-psicometricas')
ORDER BY created_at DESC LIMIT 10;
-- Verify vacante_id now has values (not NULL)
```

# Task specification
_What did the user ask to build? Any design decisions or other explanatory context_

**Original task (COMPLETED):**
Fix issue where vacante_id is not being saved in the consumos_tokens_openai database table when calling these specific AI endpoints:
1. generar-preguntas-prefiltro
2. generar-preguntas-videoentrevista
3. generar-pruebas-psicometricas

Token tracking system works correctly for basic tests (generating vacante itself), capturing tokens, costs, usuario_id, empresa_id, but vacante_id remains NULL for these three endpoint calls. Need to ensure contexto_tracking parameter with vacante_id is properly passed through realizarPeticionIA() to ConsumoTokenOpenaiService.

**Extended request (COMPLETED):**
User wants comprehensive documentation of ALL IA endpoints in the system for testing purposes. Since the 4 endpoints tested (generar-vacante, generar-preguntas-prefiltro, generar-preguntas-videoentrevista, generar-pruebas-psicometricas) all required adjustments, user wants to identify and test ALL other IA endpoints to ensure they function correctly and have proper tracking.

Goal: Create complete inventory of all 11 FastAPI OpenAI endpoints, map their PHP/JS calling locations, and create comprehensive testing plan. User requested this be documented "en un archivo con todo el contexto para empezar a trabajar en otro chat luego" (in a file with all context to continue in another chat).

**Deliverable completed:** `ENDPOINTS_IA_ANALISIS_COMPLETO.md` - 45KB comprehensive documentation file with:
- Inventory of all 11 FastAPI endpoints with line-by-line caller mapping
- Status analysis (backend/frontend correct or needs fixes)
- Exact ANTES/DESPUÉS code modifications for 4 JavaScript functions
- 4-phase testing plan with SQL validation queries
- Complete checklist for 100% endpoint coverage
- Context preservation for continuation in new chat session

# Files and Functions
_What are the important files? In short, what do they contain and why are they relevant?_

**Documentation:**
- `PLAN_TOKEN_USAGE.md` - Lists 11 FastAPI endpoints consuming OpenAI: generar-vacante, generar-preguntas-prefiltro, generar-preguntas-videoentrevista, generar-pruebas-psicometricas, obtener-informacion-hv, calificar-pregunta-abierta, transcribir-respuesta, mejores-candidatos, comparar-candidatos, generar-orientacion-vocacional, generar-perfil
- `PRUEBAS_TOKEN_TRACKING.md` - Testing guide with validation SQL queries
- **`ENDPOINTS_IA_ANALISIS_COMPLETO.md` - COMPREHENSIVE endpoint analysis with full inventory, status, modifications, testing plan (created this session)**

**Modified Files (vacante_id fix):**
- `public/src/ofertas-laborales/js/ia.js` - ✅ Lines 143-164 `generarInformacion()` now sends vacante_id and routes to VacanteController; Lines 173-189 `escogerPruebasPsicometricasConIA()` adds vacante_id to legacy route
- `app/Http/Controllers/OfertasLaborales/OfertaLaboralController.php` - ✅ Lines 824-851 `generarPruebasConIA()` now passes contexto_tracking with vacante_id

**Controllers with IA Calls:**
- `app/Http/Controllers/Vacantes/VacanteController.php` - Lines 85-92 `resolverVacanteId()`, lines 511-517 `generarConIA()` (generar-vacante endpoint), lines 539-573 `generarPreguntasPrefiltro()`, lines 582-616 `generarPreguntasVideoentrevista()`, lines 625-660 `generarPruebasPsicometricas()` - all have correct contexto_tracking with vacante_id
- `app/Http/Controllers/OfertasLaborales/OfertaLaboralController.php` - Lines 799-814 `generarInformacionIA()` (deprecated), lines 824-851 `generarPruebasConIA()` (now fixed with contexto_tracking)
- `app/Http/Controllers/Usuarios/PerfilesLaborales/PerfilLaboralController.php` - Line 80 calls `obtener-informacion-hv` endpoint with `contexto_tracking: []` (EMPTY - needs evaluation if vacante_id applies)
- `app/Http/Services/Vacantes/EtapaVacanteUsuarioService.php` - Line 369 calls `calificar-pregunta-abierta` with correct contexto_tracking (has vacante_id from etapa->vacante_id); Line 543 calls `transcribir-respuesta` with correct contexto_tracking (has vacante_id from etapa->vacante_id)

**Additional JS Files with IA Calls:**
- `public/js/vacantes/vacantes-ia.js` (406 lines total, fully analyzed) - ❌ 4 functions WITHOUT vacante_id, all need same fix as ia.js:
  1. `generarVacanteIA()` line 14 → POST `/generar-vacante` - missing vacante_id
  2. `generarPreguntasPrefiltroIA()` lines 97-115 → POST `/generar-preguntas-prefiltro` OR `/generar-preguntas-videoentrevista` - missing vacante_id
  3. `generarUnaPreguntaPrefiltroIA()` lines 218-230 → POST `/generar-preguntas-prefiltro` OR `/generar-preguntas-videoentrevista` - missing vacante_id
  4. `generarPruebasPsicometricasIA()` lines 300-310 → POST `/generar-pruebas-psicometricas` - missing vacante_id
  - All 4 functions have access to `$('#id').val()` to capture vacante_id
  - Exact ANTES/DESPUÉS modifications documented in ENDPOINTS_IA_ANALISIS_COMPLETO.md
- `public/js/vacantes/crear-editar-vacantes.js` - Found in search, not yet analyzed
- `public/js/ofertas-laborales/preguntas_prefiltro.js` - Found in search, not yet analyzed

**Complete PHP Backend IA Calls Inventory (8 total calls across 4 files):**
1. `app/Http/Controllers/Vacantes/VacanteController.php`:
   - Line 511: `realizarPeticionIA()` - generar-vacante endpoint
   - Line 552: `realizarPeticionIA()` - generar-preguntas-prefiltro endpoint
   - Line 595: `realizarPeticionIA()` - generar-preguntas-videoentrevista endpoint
   - Line 639: `realizarPeticionIA()` - generar-pruebas-psicometricas endpoint
2. `app/Http/Controllers/OfertasLaborales/OfertaLaboralController.php`:
   - Line 833: `realizarPeticionIA()` - generar-pruebas-psicometricas endpoint (legacy, now fixed)
3. `app/Http/Controllers/Usuarios/PerfilesLaborales/PerfilLaboralController.php`:
   - Line 80: `realizarPeticionIA()` - endpoint not yet identified
4. `app/Http/Services/Vacantes/EtapaVacanteUsuarioService.php`:
   - Line 369: `realizarPeticionIA()` - endpoint not yet identified
   - Line 543: `realizarPeticionIARecursiva()` - endpoint not yet identified

**11 FastAPI OpenAI Endpoints - COMPLETE MAPPING:**
1. generar-vacante - ✅ Backend: VacanteController line 511 (correct tracking); Frontend: ia.js line 80 via generarInformacion() ✅ fixed, vacantes-ia.js line 14 ❌ needs fix
2. generar-preguntas-prefiltro - ✅ Backend: VacanteController line 552 (correct); Frontend: ia.js line 80 ✅ fixed, vacantes-ia.js lines 97/209 ❌ need fix
3. generar-preguntas-videoentrevista - ✅ Backend: VacanteController line 595 (correct); Frontend: ia.js via generarInformacion() ✅ fixed, vacantes-ia.js lines 100/212 ❌ need fix
4. generar-pruebas-psicometricas - ✅ Backend: VacanteController line 639 + OfertaLaboralController line 833 (both correct); Frontend: ia.js line 181 ✅ fixed, vacantes-ia.js line 302 ❌ needs fix
5. obtener-informacion-hv - ⚠️ Backend: PerfilLaboralController line 80 (empty contexto_tracking, needs evaluation)
6. calificar-pregunta-abierta - ✅ Backend: EtapaVacanteUsuarioService line 369 (correct tracking with vacante_id from etapa->vacante_id); Frontend: N/A (auto-executed)
7. transcribir-respuesta - ✅ Backend: EtapaVacanteUsuarioService line 543 (correct tracking with vacante_id from etapa->vacante_id); Frontend: N/A (auto-executed)
8. generar-orden-servicio - ✅ Frontend: ia.js line 10 via generarInformacion() (fixed); Backend: uses deprecated OfertaLaboralService method
9. mejores-candidatos (TOP 3) - ❓ No callers found in current codebase
10. comparar-candidatos - ❓ No callers found in current codebase
11. generar-orientacion-vocacional - ❓ No callers found in current codebase
Note: generar-perfil mentioned in PLAN but not in standard list

**Core Infrastructure:**
- `app/Traits/IATrait.php` - Lines 25-91 `realizarPeticionIA()` makes HTTP to FastAPI and calls `registrarConsumoTokens()`; Lines 31-34 auto-propagate vacante_id from parametros to contexto_tracking; Lines 200-223 `registrarConsumoTokens()` saves to DB via ConsumoTokenOpenaiService
- `app/Http/Services/IA/ConsumoTokenOpenaiService.php` - Registers token consumption in consumos_tokens_openai table
- Table `consumos_tokens_openai` - Fields: usuario_id, empresa_id, vacante_id, agenda_id, ruta_endpoint, tokens_entrada, tokens_salida, costo_estimado_usd, etc.

# Workflow
_What bash commands are usually run and in what order? How to interpret their output if not obvious?_

**Testing token tracking system:**
1. Start Laravel: `php artisan serve`
2. Start FastAPI services: `cd fastapi_model && python main.py`
3. Login to PsicoAlianza web interface
4. Create or select existing vacante (note the ID)
5. Generate preguntas de prefiltro for that vacante
6. Generate preguntas de videoentrevista for that vacante
7. Generate pruebas psicometricas for that vacante

**Validation queries:**
```sql
-- Check if vacante_id is being saved
SELECT id, ruta_endpoint, vacante_id, tokens_totales, created_at
FROM consumos_tokens_openai
WHERE vacante_id = 123  -- Replace with actual vacante ID
ORDER BY created_at DESC;

-- View recent records
SELECT * FROM consumos_tokens_openai
ORDER BY created_at DESC LIMIT 10;
```

Expected: All 4 records (generar-vacante, generar-preguntas-prefiltro, generar-preguntas-videoentrevista, generar-pruebas-psicometricas) should have the same vacante_id.

Actual: Only generar-vacante has vacante_id, the other 3 show NULL.

# Errors & Corrections
_Errors encountered and how they were fixed. What did the user correct? What approaches failed and should not be tried again?_

**Issue:** Tests 1 and 2 from PRUEBAS_TOKEN_TRACKING.md work correctly (vacante generation registers with vacante_id), but tests for preguntas de prefiltro, videoentrevista, and pruebas psicometricas fail to save vacante_id (field shows NULL in database).

**Investigation findings - Backend code is correct:**
- Explore agent confirmed VacanteController.php already has contexto_tracking with vacante_id in all 3 methods
- Code deep dive shows correct structure:
  - VacanteController uses `$this->resolverVacanteId($request)` which tries 4 request keys: 'vacante_id', 'vacanteId', 'id', 'vacante.id' (lines 85-92)
  - Returns `is_numeric($vacante_id) ? (int) $vacante_id : null` - converts to int or returns null
  - All 3 methods add vacante_id to parametros: `if ($vacante_id) { $parametros['vacante_id'] = $vacante_id; }`
  - All 3 methods pass `contexto_tracking: ['vacante_id' => $vacante_id]`
  - IATrait.php has double safety: accepts contexto_tracking AND has auto-propagation logic (lines 31-34) to copy vacante_id from parameters if missing from context
  - registrarConsumoTokens() (lines 200-223) passes the full contexto array to ConsumoTokenOpenaiService::registrarConsumoDesdeRespuesta()

**Investigation findings - Frontend and complete legacy path:**
- Found frontend file: `public/src/ofertas-laborales/js/ia.js`
- Line 80: Makes call `generarInformacion('generar-preguntas-prefiltro', 'vacante')`
- **FOUND function definition at lines 143-162**: `async function generarInformacion(ruta, llave)`
- Function makes jQuery AJAX POST to `/ofertas-laborales/generar-informacion-ia` with only 4 fields:
  - `_token: $('meta[name="csrf-token"]').attr('content')`
  - `ruta: ruta` (the endpoint name like 'generar-preguntas-prefiltro')
  - `llave: llave` (field key like 'vacante')
  - `descripcion: $('#descripcion_general_oferta').val()`
- **MISSING: vacante_id is NOT included in the AJAX data payload**
- Route goes to `OfertaLaboralController@generarInformacionIA` (routes/web.php:530)
- Controller delegates to service (line 804): `$this->servicio->generarInformacionIA($request)`
- **Service method (lines 1099-1103) bypasses ALL tracking:**
  - Has @deprecated comment: "Usar la función que está en IATrait"
  - Makes DIRECT `Http::post("http://dev.psicoalianza.com:8001/{$request->ruta}/", [$request->llave => $request->descripcion])`
  - Returns raw `->json()` response - no usage data, no tracking, nothing
- Same pattern for `generarPruebasConIA()` service method (lines 1114-1123) - also @deprecated

**FINAL ROOT CAUSE - LEGACY CODE PATH:**

User clarification: **"oferta laboral ya NO se usa, solo vacante_id"** - confirms OfertaLaboralController is obsolete legacy code.

**Complete call chain of the problem:**
1. Frontend `ia.js:143-162` calls `/ofertas-laborales/generar-informacion-ia` (WRONG - legacy route)
2. Routes to `OfertaLaboralController@generarInformacionIA` (lines 799-814)
3. Delegates to `OfertaLaboralService::generarInformacionIA()` (lines 1099-1103)
4. Service makes DIRECT `Http::post()` to FastAPI - **COMPLETELY BYPASSES IATrait and all tracking**
5. Returns raw response, no usage data, no token tracking, no database insertion

**Why vacante_id is NULL:** The request never reaches VacanteController's `resolverVacanteId()` or IATrait's tracking system. The legacy service method has no tracking capability whatsoever.

**The correct path that SHOULD be used:**
1. Frontend should call `/vacantes/generar-preguntas-prefiltro` (or videoentrevista/pruebas endpoints)
2. Routes to VacanteController methods (already implemented with full tracking)
3. VacanteController uses IATrait::realizarPeticionIA() with contexto_tracking
4. IATrait calls FastAPI AND registers tokens with vacante_id

**Solution:**
1. Update `public/src/ofertas-laborales/js/ia.js` to call VacanteController routes instead of deprecated OfertaLaboralController routes
2. Change AJAX POST URLs from `/ofertas-laborales/generar-informacion-ia` to `/generar-preguntas-prefiltro`, `/generar-preguntas-videoentrevista`, `/generar-pruebas-psicometricas`
3. Include vacante_id in request data (must match one of the 4 keys checked by resolverVacanteId(): 'vacante_id', 'vacanteId', 'id', or 'vacante.id')
4. No backend changes needed - correct implementation with full IATrait tracking already exists in VacanteController

**User context confirmation:**
- Question: "¿En qué contexto estás generando las preguntas de prefiltro/videoentrevista/pruebas?"
- Answer: **"Editando una vacante existente"**
- This confirms vacante_id SHOULD be available and must be captured in the frontend and passed to backend

**Note about "crear" vs "editar" context:** When creating a NEW vacante, vacante_id will legitimately be NULL because the vacante hasn't been saved to database yet. But user is editing an existing vacante, so the ID exists and should be tracked.

**Implementation completed - All 3 modifications applied:**

1. ✅ **Frontend `generarInformacion()` - COMPLETED:**
   - Routes to VacanteController endpoints with full IATrait tracking
   - Sends vacante_id extracted from hidden input field
   - Uses computed property `[llave]` for dynamic data keys

2. ✅ **Frontend `escogerPruebasPsicometricasConIA()` - COMPLETED (hybrid approach):**
   - Keeps legacy route (validation constraints prevent VacanteController migration)
   - Adds vacante_id to request payload
   - Backend modification enables tracking through legacy path

3. ✅ **Backend `OfertaLaboralController::generarPruebasConIA()` - COMPLETED:**
   - Extracts vacante_id from request
   - Passes contexto_tracking with vacante_id to realizarPeticionIA()
   - Enables full IATrait tracking for hybrid approach

**Why hybrid approach for pruebas psicométricas:**
VacanteController Request validation (GenerarPruebasPsicometricasIA.php) requires 5 fields the frontend doesn't have: nombre_vacante, habilidades (array), herramientas_tecnologicas (array), experiencia_laboral, nivel_educativo. Solution: keep legacy route + add backend tracking.

**Testing validation:**
Edit existing vacante, generate preguntas/pruebas with IA, verify vacante_id now saves correctly in consumos_tokens_openai table.

# Codebase and System Documentation
_What are the important system components? How do they work/fit together?_

**Token Tracking System Architecture:**
1. **Frontend (Laravel)** → Makes request to controller
2. **Controller** → Calls service or uses IATrait directly
3. **IATrait::realizarPeticionIA()** → Makes HTTP request to FastAPI, captures timing
4. **FastAPI services** → Process AI request, return `{'data': result, 'usage': {tokens, model, etc}}`
5. **IATrait::registrarConsumoTokens()** → Calls ConsumoTokenOpenaiService with context
6. **ConsumoTokenOpenaiService::registrarConsumoDesdeRespuesta()** → Extracts usage, calculates cost, saves to DB
7. **Database** → consumos_tokens_openai table stores all tracking data

**Context Propagation Flow:**
```php
// Controller passes contexto_tracking
$this->realizarPeticionIA(
    ruta: 'endpoint',
    parametros: $datos,
    contexto_tracking: ['vacante_id' => $vacante_id, 'agenda_id' => $agenda_id]
);

// IATrait auto-propagates from parametros if needed (lines 31-34)
if (isset($parametros['vacante_id']) && !isset($contexto_tracking['vacante_id'])) {
    $contexto_tracking['vacante_id'] = $parametros['vacante_id'];
}

// Passes to service
$servicio_token->registrarConsumoDesdeRespuesta(
    contexto: $contexto_tracking,
    ...
);
```

**Database Schema:**
Table `consumos_tokens_openai` with 18 columns including: usuario_id, empresa_id, vacante_id, agenda_id, ruta_endpoint, nombre_servicio, modelo, tokens_entrada, tokens_salida, tokens_totales, costo_estimado_usd, estado, latencia_ms, etc.

# Learnings
_What has worked well? What has not? What to avoid? Do not duplicate items from other sections_

**Legacy code can hide root causes:** Initial investigation focused on why tracking wasn't working in VacanteController (which was actually correct), but the real issue was that deprecated code paths in OfertaLaboralController were being used that had NO tracking at all. The @deprecated comments in OfertaLaboralService methods (lines 1097, 1112) were key indicators.

**Frontend-backend route misalignment:** Frontend JavaScript was calling `/ofertas-laborales/*` legacy routes while the correct, fully-implemented routes with tracking were at `/vacantes/*` (without the controller prefix). Always verify frontend is calling the intended backend endpoints, not legacy alternatives. Check both route definitions AND the JavaScript making the AJAX calls.

**User clarification is critical:** The user's statement "oferta laboral ya NO se usa, solo vacante_id" immediately clarified that OfertaLaboralController was obsolete, saving time investigating ways to "fix" code that should simply not be used. When code seems correct but isn't working, check if the correct code is actually being executed.

**Trace complete call chains:** Problem wasn't visible until tracing: Frontend JS → Route → Controller → Service → Direct HTTP call. The service layer was making direct HTTP calls bypassing the IATrait entirely. Always trace the full execution path, not just the obvious entry points.

**Request validation can block migrations:** When attempting to migrate `escogerPruebasPsicometricasConIA()` from legacy OfertaLaboralController route to VacanteController route, discovered that VacanteController's GenerarPruebasPsicometricasIA Request requires 5 fields (nombre_vacante, habilidades array, herramientas_tecnologicas array, experiencia_laboral, nivel_educativo) but frontend only provides nombre_vacante and pruebas_disponibles. Cannot simply change routes without ensuring frontend provides all required validation fields OR relaxing backend validation. Sometimes a hybrid approach (frontend sends to legacy route + backend adds tracking) is more practical than full migration.

**Hybrid approaches work when full migration is blocked:** Successfully implemented tracking for pruebas psicométricas by keeping legacy frontend route but modifying backend to add contexto_tracking parameter. This enabled IATrait tracking without requiring frontend changes that would fail validation. Pragmatic solution when architectural constraints prevent ideal implementation.

# Key results
_If the user asked a specific output such as an answer to a question, a table, or other document, repeat the exact result here_

**Explore Agent Findings - All realizarPeticionIA() calls for the 3 endpoints:**

| Endpoint | Archivo | Línea | Método | ¿Tiene contexto_tracking? | Variable vacante_id |
|----------|---------|-------|--------|---------------------------|-------------------|
| generar-preguntas-prefiltro | VacanteController.php | 552 | generarPreguntasPrefiltro() | SI | `$vacante_id` |
| generar-preguntas-videoentrevista | VacanteController.php | 595 | generarPreguntasVideoentrevista() | SI | `$vacante_id` |
| generar-pruebas-psicometricas | VacanteController.php | 639 | generarPruebasPsicometricas() | SI | `$vacante_id` |
| generar-pruebas-psicometricas | OfertaLaboralController.php | 830 | generarPruebasConIA() | NO | N/A |

**User clarification on entity relationship:**
Question 1: "¿Cómo se relacionan las ofertas laborales con las vacantes en tu flujo de trabajo?"
Answer: **"oferta laboral ya NO se usa, solo vacante_id"**
This confirmed that OfertaLaboralController is legacy/obsolete code and should not be used. The correct implementation exists in VacanteController.

Question 2: "¿En qué contexto estás generando las preguntas de prefiltro/videoentrevista/pruebas?"
Answer: **"Editando una vacante existente"**
This confirms the vacante already exists in the database with a valid ID that should be captured and tracked.

**Grep results for `generarInformacion()` calls in ia.js:**
- Line 10: `generarInformacion('generar-orden-servicio', 'job_title')` - generates job order/service
- Line 80: `generarInformacion('generar-preguntas-prefiltro', 'vacante')` - generates prefiltro questions
- Line 128: `// await generarInformacion('generar-pruebas-psico');` - commented out, not active

**Complete endpoint coverage analysis (from ENDPOINTS_IA_ANALISIS_COMPLETO.md):**

**✅ MODIFIED FILES (30% JavaScript coverage):**
- `public/src/ofertas-laborales/js/ia.js`:
  - `generarInformacion('generar-orden-servicio', ...)` line 10 - ✅ Fixed
  - `generarInformacion('generar-preguntas-prefiltro', ...)` line 80 - ✅ Fixed
  - `escogerPruebasPsicometricasConIA()` line 181 → `/ofertas-laborales/generar-pruebas-ia` - ✅ Fixed
- `app/Http/Controllers/OfertasLaborales/OfertaLaboralController.php`:
  - `generarPruebasConIA()` line 833 - ✅ Fixed to pass contexto_tracking

**❌ UNMODIFIED FILES NEEDING FIXES (70% remaining):**
- `public/js/vacantes/vacantes-ia.js` (406 lines, 4 functions, 7 endpoint calls total):
  - `generarVacanteIA()` line 14 → `/generar-vacante` - ❌ Missing vacante_id (1 call)
  - `generarPreguntasPrefiltroIA()` lines 97-115 → `/generar-preguntas-prefiltro` OR `/generar-preguntas-videoentrevista` - ❌ Missing vacante_id (2 calls)
  - `generarUnaPreguntaPrefiltroIA()` lines 218-230 → `/generar-preguntas-prefiltro` OR `/generar-preguntas-videoentrevista` - ❌ Missing vacante_id (2 calls)
  - `generarPruebasPsicometricasIA()` lines 300-310 → `/generar-pruebas-psicometricas` - ❌ Missing vacante_id (1 call)
  - **Solution:** Add `let vacante_id = $('#id').val();` before each $.ajax() call and include `vacante_id: vacante_id` in data object
  - **Exact modifications documented in ENDPOINTS_IA_ANALISIS_COMPLETO.md lines 393-542**

**⚠️ BACKEND NEEDING EVALUATION:**
- `app/Http/Controllers/Usuarios/PerfilesLaborales/PerfilLaboralController.php` line 80:
  - Calls `obtener-informacion-hv` with `contexto_tracking: []` (empty)
  - **Evaluation needed:** Does this endpoint process candidate HV with or without vacante association?

**Summary statistics:**
- 11 FastAPI endpoints total
- 10 backends have correct tracking, 1 incomplete
- 10 frontend JavaScript calls identified: 3 fixed (30%), 7 need modification (70%)
- 4 JavaScript functions need identical modification pattern (add vacante_id to AJAX data)

**Complete Backend IA Call Search Results - ALL 8 IDENTIFIED:**
```bash
grep -rn "realizarPeticionIA\|realizarPeticionIARecursiva" app/Http/Controllers app/Http/Services
```
Found and analyzed all 8 calls:
1. `OfertaLaboralController.php:833` - realizarPeticionIA → generar-pruebas-psicometricas endpoint (✅ fixed with contexto_tracking)
2. `PerfilLaboralController.php:80` - realizarPeticionIA → obtener-informacion-hv endpoint (⚠️ has empty contexto_tracking: [])
3. `VacanteController.php:511` - realizarPeticionIA → generar-vacante endpoint (✅ correct contexto_tracking with vacante_id)
4. `VacanteController.php:552` - realizarPeticionIA → generar-preguntas-prefiltro endpoint (✅ correct contexto_tracking with vacante_id)
5. `VacanteController.php:595` - realizarPeticionIA → generar-preguntas-videoentrevista endpoint (✅ correct contexto_tracking with vacante_id)
6. `VacanteController.php:639` - realizarPeticionIA → generar-pruebas-psicometricas endpoint (✅ correct contexto_tracking with vacante_id)
7. `EtapaVacanteUsuarioService.php:369` - realizarPeticionIA → calificar-pregunta-abierta endpoint (✅ correct, has vacante_id from etapa->vacante_id)
8. `EtapaVacanteUsuarioService.php:543` - realizarPeticionIARecursiva → transcribir-respuesta endpoint (✅ correct, has vacante_id from etapa->vacante_id)

**Backend tracking status:** 7 of 8 have complete tracking with vacante_id, 1 has empty contexto_tracking (obtener-informacion-hv needs evaluation)

# Worklog
_Step by step, what was attempted, done? Very terse summary for each step_

**Investigation & Root Cause (Steps 1-43):**
1. User reported vacante_id NULL for prefiltro/videoentrevista/pruebas endpoints despite tests 1-2 working
2. Launched Explore agent: found VacanteController methods already have correct contexto_tracking implementation
3. Backend analysis confirmed double safety (explicit context + auto-propagation in IATrait lines 31-34)
4. Traced frontend: found `public/src/ofertas-laborales/js/ia.js` calls legacy `/ofertas-laborales/generar-informacion-ia` route
5. **ROOT CAUSE:** Frontend function `generarInformacion()` (lines 143-162) missing vacante_id in AJAX payload
6. Traced legacy route → OfertaLaboralController → OfertaLaboralService with @deprecated methods making direct Http::post() bypassing ALL IATrait tracking
7. User confirmed: "oferta laboral ya NO se usa, solo vacante_id" - legacy code should not be used
8. User confirmed context: "Editando una vacante existente" - vacante_id available in hidden input `<input id="id">` line 35 of crear.blade.php

**Implementation (Steps 44-65):**
9. Created plan with 2 frontend modifications + 1 backend fix for hybrid approach
10. Modified `generarInformacion()` (lines 143-164): capture vacante_id, route to `/${ruta}` (VacanteController), use computed property `[llave]`
11. Attempted full VacanteController migration for `escogerPruebasPsicometricasConIA()` but hit validation issue: GenerarPruebasPsicometricasIA requires 5 fields, frontend only has 2
12. Applied hybrid solution: keep legacy route `/ofertas-laborales/generar-pruebas-ia`, add vacante_id to payload
13. Modified backend `OfertaLaboralController::generarPruebasConIA()` (line 830): extract vacante_id, pass contexto_tracking to realizarPeticionIA()
14. **ALL 3 MODIFICATIONS COMPLETE** - Frontend now sends vacante_id, backend tracks via IATrait

**Coverage Analysis (Steps 66-75):**
15. User asked: "que otros endpoint se deben probar aparte de esos ya documentados para cubrir al 100 los cambios realizados?"
16. Grepped ia.js: found `generarInformacion()` called with 'generar-orden-servicio' (line 10) and 'generar-preguntas-prefiltro' (line 80)
17. Searched all JS files for IA patterns: found 4 files (ia.js, vacantes-ia.js, crear-editar-vacantes.js, preguntas_prefiltro.js)
18. **Read complete `public/js/vacantes/vacantes-ia.js` (406 lines)** - discovered 4 functions with 6 endpoint calls ALL missing vacante_id
19. Documented comprehensive coverage analysis: 3 of 9 calls fixed (30%), 6 calls in vacantes-ia.js need same modifications (70%)
20. Provided detailed breakdown of all 4 functions in vacantes-ia.js requiring modification

**Comprehensive Endpoint Analysis & Documentation (Steps 76-95):**
21. User clarified question: **"no, mi pregunta es adicional a vacante_id asegurar tambien el funcionamiento ya que los 4 endpoints probados requirieron ajustes"**
22. Translation: User wants comprehensive list of ALL IA endpoints to test (not just vacante_id related), because the 4 tested endpoints required fixes, suggesting other endpoints may also have issues
23. Started searching for all `realizarPeticionIA()` calls in PHP backend: found 4 files (VacanteController, OfertaLaboralController, EtapaVacanteUsuarioService, PerfilLaboralController)
24. Ran comprehensive grep: `grep -rn "realizarPeticionIA\|realizarPeticionIARecursiva" app/Http/Controllers app/Http/Services`
25. Found 8 total backend calls across 4 files
26. Read PerfilLaboralController.php lines 75-94: Line 80 calls `obtener-informacion-hv` endpoint with `contexto_tracking: []` (empty - needs evaluation)
27. Read EtapaVacanteUsuarioService.php lines 365-384: Line 369 calls `calificar-pregunta-abierta` with correct contexto_tracking including `vacante_id: $etapa_usuario->etapa->vacante_id ?? null`
28. Read EtapaVacanteUsuarioService.php lines 540-560: Line 543 calls `transcribir-respuesta` via realizarPeticionIARecursiva with correct contexto_tracking including vacante_id
29. Completed mapping: 8 of 11 FastAPI endpoints identified with callers, 3 not found (mejores-candidatos, comparar-candidatos, generar-orientacion-vocacional)
30. Analyzed frontend: 10 total JavaScript calls - 3 in ia.js (fixed), 7 in vacantes-ia.js (need same modifications)
31. User requested: **"documentalo en un archivo con todo el contexto para empezar a trabajar en otro chat luego"**
32. Created comprehensive documentation file: `ENDPOINTS_IA_ANALISIS_COMPLETO.md` (45KB)
33. Document includes: Complete 11-endpoint inventory with status, line-by-line caller mapping, exact ANTES/DESPUÉS code for 4 JS functions, 4 SQL validation queries, 4-phase testing plan, comprehensive checklist
34. Document structured in 10 sections: Resumen Ejecutivo, Inventario Completo (6 grupos), Modificaciones Pendientes (4 funciones con código exacto), Queries SQL, Plan de Pruebas, Checklist, Próximos Pasos, Información de Contexto
35. All information packaged for seamless continuation in new chat session with complete context preservation
