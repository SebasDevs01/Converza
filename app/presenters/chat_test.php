<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Converza - Chat - VERSIÓN ARREGLADA</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <style>
    .message-bubble.sent {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      border-bottom-right-radius: 6px;
      margin-left: auto;
    }
    
    .message-bubble.received {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      color: #333;
      border: 1px solid #90caf9;
      border-bottom-left-radius: 6px;
    }
    
    .btn-delete-test {
      background: rgba(248,249,250,0.95) !important;
      border-color: #dc3545 !important;
      color: #dc3545 !important;
      font-size: 16px !important;
      padding: 8px 12px !important;
      border-radius: 6px !important;
      min-width: 40px !important;
      height: 36px !important;
    }
    
    .btn-reaction-test {
      background: rgba(248,249,250,0.95) !important;
      border-color: #007bff !important;
      color: #007bff !important;  
      font-size: 16px !important;
      padding: 8px 12px !important;
      border-radius: 6px !important;
      min-width: 40px !important;
      height: 36px !important;
    }
  </style>
</head>
<body>

<div class="container mt-4">
  <div class="row">
    <div class="col-12">
      <h2>🔧 PRUEBA DE BOTONES ARREGLADOS</h2>
      <p>Esta es una página de prueba para verificar que los botones funcionan correctamente:</p>
      
      <!-- Mensaje de prueba estilo recibido -->
      <div class="d-flex mb-3">
        <div class="message-bubble received p-3 me-auto" style="max-width: 70%;">
          <p>Este es un mensaje recibido con fondo azul claro</p>
          <div class="d-flex gap-2 mt-2">
            <button class="btn btn-reaction-test">😊</button>
            <button class="btn btn-delete-test">🗑️</button>
          </div>
        </div>
      </div>
      
      <!-- Mensaje de prueba estilo enviado -->
      <div class="d-flex mb-3">
        <div class="message-bubble sent p-3 ms-auto" style="max-width: 70%;">
          <p>Este es un mensaje enviado con fondo azul oscuro</p>
          <div class="d-flex gap-2 mt-2">
            <button class="btn btn-reaction-test">😊</button>
            <button class="btn btn-delete-test">🗑️</button>
          </div>
        </div>
      </div>
      
      <div class="alert alert-info">
        <h5>✅ Si ves esto correctamente:</h5>
        <ul>
          <li>Los mensajes recibidos tienen fondo azul claro</li>
          <li>Los botones tienen el emoji 🗑️ visible</li>
          <li>Los botones tienen fondo gris claro</li>
        </ul>
        <p><strong>Entonces los estilos están funcionando y podemos aplicarlos al chat real.</strong></p>
      </div>
      
      <a href="chat.php?usuario=15" class="btn btn-primary">← Volver al chat real</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Página de prueba cargada correctamente');
    
    // Verificar que los estilos se aplicaron
    const botonesEliminar = document.querySelectorAll('.btn-delete-test');
    const botonesReaccion = document.querySelectorAll('.btn-reaction-test');
    
    console.log(`Botones de eliminar encontrados: ${botonesEliminar.length}`);
    console.log(`Botones de reacción encontrados: ${botonesReaccion.length}`);
    
    botonesEliminar.forEach((boton, index) => {
        console.log(`Botón ${index}: innerHTML = "${boton.innerHTML}"`);
        console.log(`Botón ${index}: background = "${boton.style.backgroundColor}"`);
    });
});
</script>

</body>
</html>