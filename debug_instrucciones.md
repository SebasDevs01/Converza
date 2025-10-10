## DEBUGGING DEL SISTEMA DE COMENTARIOS

### Para verificar si funciona:

1. **Recarga la página** donde hay comentarios
2. **Inspecciona el código fuente** (Ctrl+U)  
3. **Busca** los comentarios HTML que empiecen con `<!-- DEBUG:`

### Qué buscar:
```html
<!-- DEBUG: sessionUserId=3, com[usuario]=3, com[id_use]=3 -->
<!-- DEBUG: canDelete=true -->
```

### Interpretación:
- **sessionUserId**: ID del usuario logueado
- **com[usuario]**: FK usuario en tabla comentarios  
- **com[id_use]**: ID real del usuario (de tabla usuarios)
- **canDelete**: true/false si puede eliminar

### Si canDelete=false:
- Verifica que tengas sesión iniciada
- Verifica que sea TU comentario
- Verifica los IDs en los comentarios DEBUG

### Si canDelete=true pero no ves el botón:
- Problema de CSS o JavaScript
- Botón puede estar oculto

### Una vez funcionando:
Quitar los comentarios DEBUG del código.