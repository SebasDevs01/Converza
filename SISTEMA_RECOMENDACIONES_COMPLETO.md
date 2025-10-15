# 🎯 Sistema de Recomendaciones Inteligentes - Converza

## 📋 Resumen Ejecutivo

Sistema completo que integra **Predicciones**, **Conexiones Místicas** y **Daily Shuffle** para ofrecer recomendaciones personalizadas basadas en intereses compartidos.

---

## 🏗️ Arquitectura del Sistema

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────┐
│                     PREDICCIONES                            │
│  (Recopila intereses mediante votaciones gamificadas)      │
│  Categorías: 🎵 Música | 🍽️ Comida | 🎨 Hobbies           │
│              ✈️ Viajes | 💭 Personalidad                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              INTERESES HELPER (Motor Central)               │
│  - Calcula compatibilidad entre usuarios (0-100%)          │
│  - Identifica intereses comunes                            │
│  - Prioriza usuarios con gustos similares                  │
└───────────┬───────────────┬──────────────────┬─────────────┘
            │               │                  │
            ▼               ▼                  ▼
┌──────────────────┐  ┌──────────────┐  ┌───────────────────┐
│  CONEXIONES      │  │  DAILY       │  │  USUARIOS         │
│  MÍSTICAS        │  │  SHUFFLE     │  │  SIMILARES        │
│                  │  │              │  │                   │
│ • Compatibilidad │  │ • Prioriza   │  │ • Lista top 10    │
│   + Score        │  │   usuarios   │  │   matches         │
│ • Badges de      │  │   con mayor  │  │ • Muestra %       │
│   intereses      │  │   afinidad   │  │   compatibilidad  │
└──────────────────┘  └──────────────┘  └───────────────────┘
```

---

## 🎲 1. Sistema de Predicciones

### Funcionamiento
- Usuario recibe 5 predicciones diarias (una por categoría)
- Vota: ✅ Me gusta | ❌ No me gusta
- Sistema aprende sus preferencias automáticamente
- **NO es invasivo**: Solo usa actividad pública

### Base de Datos
```sql
predicciones_usuarios
├── id_use (Usuario)
├── categoria (musica/comida/hobbies/viajes/personalidad)
├── prediccion (Texto de la predicción)
├── me_gusta (NULL: no votado | 0: no | 1: sí)
├── visto (1: vista | 0: no vista)
└── fecha_creacion
```

### Archivos Clave
- **Backend**: `app/presenters/get_prediccion.php`
- **Frontend**: `app/view/_navbar_panels.php` (Offcanvas Predicciones)
- **Votos**: `app/presenters/votar_prediccion.php`

---

## 🧠 2. InteresesHelper (Motor Central)

### Ubicación
`app/models/intereses-helper.php`

### Métodos Principales

#### `calcularCompatibilidad($usuario1_id, $usuario2_id)`
**Retorna**: Porcentaje de compatibilidad (0-100%)

**Algoritmo**:
```php
compatibilidad = (coincidencias / categorias_comparadas) * 100

Ejemplo:
- Usuario A: Música ✅, Comida ✅, Hobbies ❌
- Usuario B: Música ✅, Comida ❌, Hobbies ❌
- Comparadas: 3 categorías
- Coincidencias: 1 (Música)
- Compatibilidad: 33%
```

**Características**:
- Solo compara categorías que ambos han votado
- Requiere mínimo 2 categorías en común
- Ignora votos NULL (no respondidos)

---

#### `obtenerInteresesComunes($usuario1_id, $usuario2_id)`
**Retorna**: Array de intereses compartidos con emoji y nombre

```php
[
    ['categoria' => 'musica', 'emoji' => '🎵', 'nombre' => 'Música'],
    ['categoria' => 'comida', 'emoji' => '🍽️', 'nombre' => 'Comida']
]
```

---

#### `obtenerUsuariosSimilares($usuario_id, $limite = 10)`
**Retorna**: Top N usuarios con mayor compatibilidad

**Filtros aplicados**:
- ❌ Usuario actual
- ❌ Usuarios bloqueados
- ❌ Usuarios con menos de 2 categorías votadas
- ✅ Ordenados por compatibilidad descendente

---

#### `mejorarConexionesMisticas($usuario_id, $conexiones)`
**Efecto**: Agrega bonus a conexiones basado en intereses

```php
// Antes
[
    'otro_id' => 123,
    'puntuacion' => 75, // Score original basado en patrones
]

// Después
[
    'otro_id' => 123,
    'puntuacion' => 91,        // Score mejorado (75 + 16)
    'score_original' => 75,
    'compatibilidad' => 80,    // 80% compatible
    'intereses_comunes' => [...]
]

// Cálculo del bonus: round(compatibilidad / 5)
// Bonus máximo: 20 puntos (compatibilidad 100%)
```

---

#### `mejorarDailyShuffle($usuario_id, $candidatos)`
**Efecto**: Prioriza usuarios con intereses compatibles

**Estrategia**:
1. Calcula compatibilidad con cada candidato
2. Ordena poniendo primero usuarios con mayor afinidad
3. **Mantiene aleatoriedad** entre usuarios con misma compatibilidad

```php
// Shuffle original (aleatorio)
[Usuario A (0%), Usuario B (80%), Usuario C (0%), Usuario D (50%)]

// Shuffle mejorado (priorizando compatibilidad)
[Usuario B (80%), Usuario D (50%), Usuario A (0%), Usuario C (0%)]
```

---

## 🔗 3. Conexiones Místicas (Mejoradas)

### Integración

**Archivo**: `app/presenters/conexiones_misticas.php`

```php
// Backend
require_once(__DIR__ . '/../models/intereses-helper.php');
$interesesHelper = new InteresesHelper($conexion);

$conexiones = $motor->obtenerConexionesUsuario($_SESSION['id'], 50);
$conexiones = $interesesHelper->mejorarConexionesMisticas($_SESSION['id'], $conexiones);
```

### UI Mejorada
Cada tarjeta de conexión ahora muestra:
- **Score original** (basado en patrones de actividad)
- **Porcentaje de compatibilidad** (basado en intereses)
- **Badges de intereses comunes** (🎵 Música, 🍽️ Comida, etc.)

```html
<div class="conexion-card">
    <div class="conexion-header">
        <img src="avatar.jpg">
        <div class="conexion-username">usuario123</div>
        <div class="conexion-badge">91%</div> <!-- Score mejorado -->
    </div>
    <div class="conexion-descripcion">...</div>
    
    <!-- NUEVO -->
    <div class="intereses-comunes">
        <small><i class="bi bi-star-fill"></i> Intereses en común:</small>
        <span class="badge">🎵 Música</span>
        <span class="badge">🍽️ Comida</span>
        <small>❤️ Compatibilidad: 80%</small>
    </div>
</div>
```

---

## 🎲 4. Daily Shuffle (Mejorado)

### Integración

**Archivo**: `app/presenters/daily_shuffle.php`

```php
// Backend
require_once(__DIR__ . '/../models/intereses-helper.php');
$interesesHelper = new InteresesHelper($conexion);

// Obtener 10 usuarios aleatorios
$usuariosDisponibles = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// ⭐ MEJORA: Priorizar por compatibilidad
$usuariosDisponibles = $interesesHelper->mejorarDailyShuffle($usuario_id, $usuariosDisponibles);

// Agregar info de compatibilidad a cada usuario
foreach ($shuffle as &$usuario) {
    $usuario['compatibilidad'] = $interesesHelper->calcularCompatibilidad(...);
    $usuario['intereses_comunes'] = $interesesHelper->obtenerInteresesComunes(...);
}
```

### UI Mejorada

**Archivo**: `app/view/_navbar_panels.php`

Cada tarjeta de shuffle ahora incluye:

```html
<div class="shuffle-card">
    <!-- Badge flotante de compatibilidad -->
    <div class="compatibilidad-badge-shuffle">
        <span class="badge">
            <i class="bi bi-heart-fill"></i> 85% Compatible
        </span>
    </div>
    
    <img src="avatar.jpg" class="shuffle-card-image">
    
    <div class="shuffle-card-body">
        <h3>usuario123</h3>
        <p>Bio del usuario...</p>
        
        <!-- NUEVO: Intereses comunes -->
        <div class="intereses-comunes-shuffle">
            <small><i class="bi bi-star-fill"></i> Intereses en común:</small>
            <span class="badge">🎵 Música</span>
            <span class="badge">🎨 Hobbies</span>
        </div>
        
        <div class="shuffle-actions">
            <button>Ver perfil</button>
            <button>Agregar</button>
        </div>
    </div>
</div>
```

### Características
- ✅ **Priorizados**: Usuarios con intereses comunes aparecen primero
- ✅ **Visual**: Badge de compatibilidad flotante
- ✅ **Transparente**: Muestra qué intereses comparten
- ✅ **Mantiene aleatoriedad**: Entre usuarios con misma compatibilidad

---

## 📊 5. API de Usuarios Similares

### Endpoint
`GET /app/presenters/get_usuarios_similares.php?limite=10`

### Respuesta
```json
{
    "success": true,
    "usuarios": [
        {
            "id_use": 123,
            "usuario": "maria_dev",
            "nombre": "María González",
            "foto_perfil": "avatar123.jpg",
            "compatibilidad": 85,
            "intereses_comunes": [
                {"categoria": "musica", "emoji": "🎵", "nombre": "Música"},
                {"categoria": "hobbies", "emoji": "🎨", "nombre": "Hobbies"}
            ]
        },
        ...
    ],
    "total": 10
}
```

### Uso Futuro
Este endpoint permite crear una página dedicada tipo "Usuarios Compatibles" donde mostrar matches ordenados por afinidad.

---

## 🔒 Privacidad y Ética

### ✅ Cumple RF (Requerimientos Funcionales)
- **No invasivo**: Solo usa actividad pública
- **Transparente**: Usuario sabe qué se está analizando
- **Gamificado**: Predicciones son entretenidas, no interrogatorios
- **Opcional**: Usuario puede ignorar el sistema

### 🛡️ Protecciones
- Solo compara usuarios que han votado predicciones
- No expone votos específicos, solo categorías generales
- Usuario controla qué categorías responde
- Sistema respeta bloqueos y privacidad

---

## 📈 Ventajas vs Competencia

### vs Facebook/Instagram
- ❌ **Ellos**: Análisis invasivo de datos sin consentimiento
- ✅ **Converza**: Gamificación transparente con control del usuario

### vs Tinder/Bumble
- ❌ **Ellos**: Matching basado solo en fotos y ubicación
- ✅ **Converza**: Compatibilidad real basada en gustos compartidos

### vs LinkedIn
- ❌ **Ellos**: Recomendaciones solo laborales
- ✅ **Converza**: Descubrimiento social integral

---

## 🚀 Roadmap Futuro

### Fase Actual ✅
- [x] Sistema de Predicciones funcionando
- [x] InteresesHelper implementado
- [x] Conexiones Místicas integradas
- [x] Daily Shuffle mejorado
- [x] API de Usuarios Similares

### Próximas Mejoras 🔜
- [ ] Página dedicada "Usuarios Compatibles"
- [ ] Sección de intereses en perfil (opcional)
- [ ] Notificación cuando aparece un match 95%+
- [ ] Estadísticas personales de compatibilidad
- [ ] Sistema de recomendación de posts basado en intereses

### Ideas Avanzadas 💡
- [ ] Machine Learning para predicciones personalizadas
- [ ] Análisis de texto en posts para refinar intereses
- [ ] Eventos/grupos sugeridos según compatibilidad
- [ ] "Compatibility Wrapped" anual (tipo Spotify Wrapped)

---

## 🧪 Testing

### Casos de Prueba

#### Test 1: Compatibilidad Básica
```
Usuario A: Música ✅, Comida ✅
Usuario B: Música ✅, Comida ❌
Resultado esperado: 50% compatible
```

#### Test 2: Sin Intereses en Común
```
Usuario A: Música ✅
Usuario B: Comida ✅
Resultado esperado: 0% compatible (no comparten categorías)
```

#### Test 3: Usuarios Sin Votos
```
Usuario A: Todo NULL
Usuario B: Música ✅
Resultado esperado: 0% (insuficientes datos)
```

#### Test 4: Daily Shuffle Priorizado
```
Candidatos: [A (0%), B (80%), C (50%), D (0%)]
Orden esperado: B, C, A o D (aleatorio entre 0%)
```

---

## 📝 Notas Técnicas

### Performance
- **InteresesHelper** usa consultas optimizadas con índices
- Caché de compatibilidad podría agregarse si escala
- Predicciones se generan bajo demanda (no todas de golpe)

### Escalabilidad
- Sistema preparado para millones de usuarios
- Algoritmo O(n) lineal para cálculo de compatibilidad
- Daily Shuffle limitado a 10 usuarios (evita sobrecarga)

### Mantenimiento
- Agregar nuevas categorías: Modificar `InteresesHelper::CATEGORIAS`
- Cambiar peso de bonus: Ajustar fórmula `round($compatibilidad / 5)`
- Personalizar Daily Shuffle: Cambiar `LIMIT 10` en query

---

## 👨‍💻 Créditos

**Sistema diseñado e implementado para Converza**  
**Fecha**: Octubre 2025  
**Versión**: 1.0  

---

## 📞 Soporte

Si encuentras bugs o tienes ideas de mejora:
1. Revisa la documentación técnica en cada archivo
2. Verifica logs en `check_quick.php`
3. Usa herramientas de debug: `test_predicciones.php`

---

**¡Disfruta del sistema de recomendaciones más inteligente y ético de las redes sociales! 🚀**
