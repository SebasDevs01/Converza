# ⚡ Quick Start - Sistema de Intereses

## 🎯 ¿Qué hace?

Converza ahora recomienda personas basándose en **gustos compartidos** a través de 3 features integradas:

### 1️⃣ Predicciones (Recopila datos)
- Usuario vota predicciones divertidas: ✅ Me gusta / ❌ No me gusta
- 5 categorías: 🎵 Música | 🍽️ Comida | 🎨 Hobbies | ✈️ Viajes | 💭 Personalidad
- **NO invasivo**: Solo usa actividad pública

### 2️⃣ Conexiones Místicas (Mejorado)
- **ANTES**: Solo mostraba score basado en patrones
- **AHORA**: 
  - ➕ Bonus por intereses comunes (hasta +20 puntos)
  - 🏷️ Badges mostrando gustos compartidos
  - 💯 Porcentaje de compatibilidad visible

### 3️⃣ Daily Shuffle (Mejorado)
- **ANTES**: 10 usuarios 100% aleatorios
- **AHORA**: 
  - ⭐ Prioriza usuarios con mayor compatibilidad
  - 🎲 Mantiene aleatoriedad entre mismos %
  - 💜 Badge flotante mostrando % compatible
  - 🏷️ Lista de intereses en común

---

## 🏗️ Arquitectura en 3 Pasos

```
PASO 1: PREDICCIONES → Recolecta votos del usuario
            ↓
PASO 2: INTERESES HELPER → Calcula compatibilidad entre usuarios
            ↓
PASO 3: MEJORA FEATURES → Prioriza matches en Conexiones y Shuffle
```

---

## 📂 Archivos Modificados/Creados

### ✅ Nuevos
- `app/models/intereses-helper.php` - Motor central (244 líneas)
- `app/presenters/get_usuarios_similares.php` - API para matches

### ✏️ Modificados
- `app/presenters/conexiones_misticas.php` - Integración backend
- `app/presenters/daily_shuffle.php` - Integración backend + cálculo
- `app/view/_navbar_panels.php` - UI mejorada para ambos

### 📋 Documentación
- `SISTEMA_RECOMENDACIONES_COMPLETO.md` - Guía técnica detallada

---

## 🚀 Cómo Funciona la Compatibilidad

### Algoritmo Simple
```
compatibilidad = (gustos_coincidentes / categorías_comparadas) * 100
```

### Ejemplo Real
```
👤 Usuario A votó:
   - Música: ✅ Me gusta
   - Comida: ✅ Me gusta  
   - Hobbies: ❌ No me gusta

👤 Usuario B votó:
   - Música: ✅ Me gusta
   - Comida: ❌ No me gusta
   - Hobbies: ❌ No me gusta

📊 Cálculo:
   - Categorías comparadas: 3
   - Coincidencias: 2 (Música ✅✅ y Hobbies ❌❌)
   - Compatibilidad: 2/3 = 66%
```

**Nota**: ❌+❌ SÍ cuenta como coincidencia (a ambos les disgusta)

---

## 🎨 Mejoras Visuales

### Conexiones Místicas
```html
ANTES:
┌──────────────────┐
│ 👤 usuario123    │
│ 💖 Gustos        │
│ Score: 75%       │
└──────────────────┘

AHORA:
┌──────────────────┐
│ 👤 usuario123    │
│ 💖 Gustos        │
│ Score: 91%       │ ← +16 bonus
│ ⭐ Intereses:    │
│ 🎵 Música        │
│ 🍽️ Comida        │
│ ❤️ 80% Compatible│
└──────────────────┘
```

### Daily Shuffle
```html
ANTES:
┌──────────────────┐
│ [Foto Usuario]   │
│ usuario123       │
│ Bio...           │
│ [Ver] [Agregar]  │
└──────────────────┘

AHORA:
┌──────────────────┐
│ 💜 85% Compatible│ ← Badge flotante
│ [Foto Usuario]   │
│ usuario123       │
│ Bio...           │
│ ⭐ Intereses:    │
│ 🎵 Música        │
│ 🎨 Hobbies       │
│ [Ver] [Agregar]  │
└──────────────────┘
```

---

## 🧪 Probar el Sistema

### 1. Generar Predicciones
```
1. Abre Converza
2. Click en icono de Predicciones (navbar)
3. Vota: ✅ o ❌ en 5 categorías
4. Repite con otro usuario
```

### 2. Ver Conexiones Mejoradas
```
1. Ve a Conexiones Místicas
2. Verás badges de intereses y % compatible
3. Usuarios con más compatibilidad tienen score más alto
```

### 3. Probar Daily Shuffle
```
1. Click en icono Shuffle (navbar)
2. Usuarios con mayor compatibilidad aparecen primero
3. Verás badge flotante con % y lista de intereses
```

---

## 📊 API Disponible

### Endpoint: Usuarios Similares
```
GET /app/presenters/get_usuarios_similares.php?limite=10
```

**Respuesta**:
```json
{
    "success": true,
    "usuarios": [
        {
            "id_use": 123,
            "usuario": "maria_dev",
            "compatibilidad": 85,
            "intereses_comunes": [
                {"emoji": "🎵", "nombre": "Música"}
            ]
        }
    ],
    "total": 10
}
```

**Uso futuro**: Página dedicada "Encuentra personas compatibles"

---

## ⚙️ Configuración

### Agregar Nueva Categoría
**Archivo**: `app/models/intereses-helper.php`

```php
private const CATEGORIAS = [
    'musica' => ['emoji' => '🎵', 'nombre' => 'Música'],
    'comida' => ['emoji' => '🍽️', 'nombre' => 'Comida'],
    'hobbies' => ['emoji' => '🎨', 'nombre' => 'Hobbies'],
    'viajes' => ['emoji' => '✈️', 'nombre' => 'Viajes'],
    'personalidad' => ['emoji' => '💭', 'nombre' => 'Personalidad'],
    // ➕ Agrega aquí nueva categoría
    'deportes' => ['emoji' => '⚽', 'nombre' => 'Deportes'],
];
```

### Ajustar Bonus de Compatibilidad
**Archivo**: `app/models/intereses-helper.php` línea ~145

```php
// Bonus actual: 0-20 puntos
$bonus = round($compatibilidad / 5);

// Aumentar a 0-30 puntos:
$bonus = round($compatibilidad / 3.33);

// Disminuir a 0-10 puntos:
$bonus = round($compatibilidad / 10);
```

### Cambiar Cantidad Daily Shuffle
**Archivo**: `app/presenters/daily_shuffle.php` línea ~62

```php
// Actual: 10 usuarios
LIMIT 10

// Cambiar a 20:
LIMIT 20
```

---

## 🔍 Debug

### Ver Compatibilidad de 2 Usuarios
```php
// En cualquier PHP
require_once(__DIR__ . '/app/models/intereses-helper.php');
$helper = new InteresesHelper($conexion);

$compatibilidad = $helper->calcularCompatibilidad(16, 17); // IDs
echo "Compatibilidad: {$compatibilidad}%";

$intereses = $helper->obtenerInteresesComunes(16, 17);
print_r($intereses);
```

### Ver Usuarios Más Compatibles
```php
$similares = $helper->obtenerUsuariosSimilares(16, 5); // Top 5
foreach ($similares as $usuario) {
    echo "{$usuario['usuario']}: {$usuario['compatibilidad']}%\n";
}
```

---

## 🐛 Troubleshooting

### "Compatibilidad siempre 0%"
- **Causa**: Usuarios no han votado predicciones
- **Solución**: Ambos usuarios deben votar al menos 2 categorías

### "No aparecen badges de intereses"
- **Causa**: Usuarios no tienen intereses en común
- **Solución**: Normal, solo se muestran si hay match

### "Daily Shuffle no prioriza bien"
- **Causa**: Usuarios tienen misma compatibilidad (0%)
- **Solución**: Se mantiene orden aleatorio entre empates

---

## 📈 Métricas de Éxito

### KPIs a Monitorear
- **Engagement Predicciones**: % usuarios que votan
- **Match Rate**: % conexiones con compatibilidad >50%
- **Shuffle CTR**: % usuarios que agregan desde shuffle
- **Retention**: ¿Usuarios vuelven a Daily Shuffle?

### Queries Útiles
```sql
-- % Usuarios con predicciones votadas
SELECT 
    COUNT(DISTINCT id_use) * 100.0 / (SELECT COUNT(*) FROM usuarios) as porcentaje_activos
FROM predicciones_usuarios 
WHERE me_gusta IS NOT NULL;

-- Top usuarios más compatibles
SELECT u.usuario, AVG(compatibilidad) as avg_compat
FROM ... -- Usar InteresesHelper
GROUP BY u.usuario
ORDER BY avg_compat DESC
LIMIT 10;
```

---

## 🎯 Próximos Pasos

### Corto Plazo
1. ✅ Probar sistema con usuarios reales
2. ✅ Monitorear performance de queries
3. ✅ Recopilar feedback sobre badges

### Medio Plazo
- [ ] Página dedicada "Usuarios Compatibles"
- [ ] Notificaciones para matches 90%+
- [ ] Estadísticas en perfil

### Largo Plazo
- [ ] Machine Learning para predicciones
- [ ] "Compatibility Wrapped" anual
- [ ] Recomendación de posts/eventos

---

## 🏆 Ventajas Competitivas

✅ **Ético**: Transparente, no invasivo  
✅ **Divertido**: Gamificado con predicciones  
✅ **Efectivo**: Matching real por gustos  
✅ **Integrado**: 3 features trabajando juntas  
✅ **Escalable**: Preparado para crecer  

---

**¡Sistema listo para producción! 🚀**

*Para más detalles técnicos, consulta `SISTEMA_RECOMENDACIONES_COMPLETO.md`*
