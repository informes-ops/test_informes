<?php
/* ============================================================
   CONFIGURACIÓN DE NOTIFICACIONES PUSH ZGROUP
   1) Crea una cuenta/app Web Push en OneSignal.
   2) Copia aquí tu App ID y REST API Key.
   3) Cambia también el token interno por una cadena larga.
   ============================================================ */

define('ZGROUP_ONESIGNAL_APP_ID', 'PEGA_AQUI_TU_ONESIGNAL_APP_ID');
define('ZGROUP_ONESIGNAL_REST_API_KEY', 'PEGA_AQUI_TU_REST_API_KEY');

// En OneSignal actual normalmente se usa: Key
// Si tu cuenta usa clave antigua y falla, prueba con: Basic
define('ZGROUP_ONESIGNAL_AUTH_PREFIX', 'Key');

// Token simple para permitir que el formulario avise al backend al guardar un informe.
// Cámbialo por algo largo, por ejemplo: zgrp_2026_..._aleatorio
define('ZGROUP_PUSH_TRIGGER_TOKEN', 'CAMBIA_ESTE_TOKEN_LARGO_2026');
