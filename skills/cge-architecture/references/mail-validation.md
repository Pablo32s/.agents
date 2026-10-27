# Administración y Validación de Mails (Identity & Comms Gate)

Este documento detalla el esquema de validación obligatoria de correo electrónico para módulos del CGE / SAGE.

---

## 1. Validación Previa Obligatoria

Antes de permitir que un agente o usuario inicie una solicitud, cargue formularios o suba documentación sensible, la interfaz (`index.php`) o el endpoint verifica el estado de validación del correo del usuario en SAGE.

### Flujo de Interfaz:
1. Si el correo no está validado:
   - Se bloquea la interacción con el formulario.
   - Se despliega un **Empty State** visual utilizando la ilustración `ValidateMail.svg` informando al usuario que debe verificar su casilla de correo para continuar.
2. Si el correo está validado:
   - Se habilita el acceso completo a los formularios y flujos de carga.

---

## 2. Justificación y Beneficios

1. **Canal de Notificaciones Confiable**: Garantiza la existencia de un medio de contacto directo para el envío de alertas de estado asíncronas ("Pendiente", "Aprobado", "Rechazado") tanto a Recursos Humanos como al propio agente.
2. **Capa Extra de Autenticación**: Añade verificación de identidad real antes de autorizar la ingesta de documentos probatorios o trámites con validez legal.
