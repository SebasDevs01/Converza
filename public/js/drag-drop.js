// Sistema de Drag & Drop para imágenes y videos
$(document).ready(function() {
    let selectedFiles = [];
    
    function renderPreviews() {
        console.log('🔄 Ejecutando renderPreviews() con', selectedFiles.length, 'archivos');
        console.log('📋 Archivos a renderizar:', selectedFiles.map(f => f.name + ' (' + f.type + ')'));
        
        const container = $('#preview-container');
        console.log('📦 Container encontrado:', container.length > 0);
        container.empty();
        
        if (selectedFiles.length === 0) {
            console.log('📭 No hay archivos, ocultando container');
            container.hide();
            return;
        }
        
        console.log('📂 Mostrando container con archivos');
        container.show();
        
        selectedFiles.forEach((file, idx) => {
            const preview = $('<div class="position-relative d-inline-block">')
                .css({'width':'120px','height':'120px'});
            
            // Botón de eliminar común para todos los tipos
            const btn = $('<button type="button" title="Eliminar">&times;</button>');
            btn.removeClass();
            btn.css({
                'position': 'absolute',
                'top': '6px',
                'right': '6px',
                'width': '26px',
                'height': '26px',
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'center',
                'z-index': 2,
                'font-size': '1.3rem',
                'line-height': '1',
                'background': '#0d6efd',
                'border': 'none',
                'color': '#fff',
                'border-radius': '50%',
                'box-shadow': '0 2px 6px rgba(0,0,0,0.25)',
                'cursor': 'pointer',
                'padding': 0
            });
            
            // Hover effect azul más oscuro
            btn.on('mouseenter', function() {
                $(this).css('background', '#0b5ed7');
            });
            btn.on('mouseleave', function() {
                $(this).css('background', '#0d6efd');
            });
            
            btn.on('click', function() {
                selectedFiles.splice(idx, 1);
                renderPreviews();
            });
            
            // Verificar si es imagen o video
            if (file.type.startsWith('image/')) {
                // Previsualización de imagen
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const img = $('<img class="rounded-3 border">')
                        .attr('src', ev.target.result)
                        .css({
                            'width': '120px',
                            'height': '120px',
                            'object-fit': 'cover',
                            'display': 'block'
                        });
                    preview.append(img).append(btn);
                    container.append(preview);
                };
                reader.readAsDataURL(file);
                
            } else if (file.type.startsWith('video/')) {
                // Previsualización de video
                const video = $('<video class="rounded-3 border" muted>')
                    .css({
                        'width': '120px',
                        'height': '120px',
                        'object-fit': 'cover',
                        'display': 'block'
                    });
                
                // Crear URL para el video
                const videoURL = URL.createObjectURL(file);
                video.attr('src', videoURL);
                
                // Añadir icono de play sobre el video
                const playIcon = $('<div class="position-absolute top-50 start-50 translate-middle">')
                    .css({
                        'width': '40px',
                        'height': '40px',
                        'background-color': 'rgba(0,0,0,0.7)',
                        'border-radius': '50%',
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'color': 'white',
                        'font-size': '1.2rem',
                        'pointer-events': 'none'
                    })
                    .html('▶');
                
                preview.append(video).append(playIcon).append(btn);
                container.append(preview);
                
                // Limpiar URL cuando se elimine
                btn.on('click', function() {
                    URL.revokeObjectURL(videoURL);
                });
            }
        });
    }

    // Drag & Drop para imágenes y videos
    console.log('Inicializando drag & drop...');
    const $form = $('#form-publicar');
    const $textarea = $form.find('textarea[name="publicacion"]');
    console.log('Formulario encontrado:', $form.length > 0);
    
    // Estilos de drag & drop
    $form.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('border-primary bg-light');
        $textarea.attr('placeholder', 'Suelta aquí tus archivos de imagen o video...');
    });
    
    $form.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Solo remover estilos si realmente salimos del formulario
        if (!e.relatedTarget || !$.contains(this, e.relatedTarget)) {
            $(this).removeClass('border-primary bg-light');
            $textarea.attr('placeholder', '¿Qué estás pensando?');
        }
    });
    
    $form.on('drop', function(e) {
        console.log('Drop event detectado!');
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('border-primary bg-light');
        $textarea.attr('placeholder', '¿Qué estás pensando?');
        
        const files = Array.from(e.originalEvent.dataTransfer.files);
        console.log('Archivos arrastrados:', files);
        
        // Filtrar archivos válidos (imágenes y videos)
        const validFiles = files.filter(f => {
            const isValid = /^image\/(jpeg|png|gif)$/.test(f.type) || 
                           /^video\/(mp4|webm|ogg)$/.test(f.type);
            console.log(`Archivo ${f.name} (${f.type}): ${isValid ? 'VÁLIDO' : 'INVÁLIDO'}`);
            return isValid;
        });
        
        console.log('Archivos válidos:', validFiles.length, 'de', files.length);
        
        if (validFiles.length > 0) {
            validFiles.forEach(f => {
                // Evitar duplicados
                if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) {
                    selectedFiles.push(f);
                    console.log('Archivo añadido:', f.name);
                }
            });
            console.log('Total archivos seleccionados:', selectedFiles.length);
            renderPreviews();
        }
        
        // Mostrar mensaje si hay archivos no válidos
        const invalidFiles = files.filter(f => {
            return !/^(image\/(jpeg|png|gif)|video\/(mp4|webm|ogg))$/.test(f.type);
        });
        
        if (invalidFiles.length > 0) {
            alert(`${invalidFiles.length} archivo(s) no son compatibles. Solo se aceptan imágenes (JPEG, PNG, GIF) y videos (MP4, WebM, OGG).`);
        }
    });
    
    // Manejador para input de imágenes
    $('#file-input').on('change', function(e) {
        console.log('📸 Botón de imágenes usado - archivos seleccionados:', this.files.length);
        const newFiles = Array.from(this.files);
        newFiles.forEach(f => {
            if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) {
                selectedFiles.push(f);
                console.log('📸 Imagen añadida:', f.name, f.type);
            }
        });
        console.log('📸 Total archivos después de imágenes:', selectedFiles.length);
        renderPreviews();
        this.value = ''; // Limpiar para permitir reselección
    });
    
    // Manejador para input de videos
    $('#video-input').on('change', function(e) {
        console.log('🎥 Botón de videos usado - archivos seleccionados:', this.files.length);
        console.log('🎥 Archivos recibidos:', Array.from(this.files).map(f => f.name + ' (' + f.type + ')'));
        
        const newFiles = Array.from(this.files);
        newFiles.forEach(f => {
            if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) {
                selectedFiles.push(f);
                console.log('🎥 Video añadido:', f.name, f.type);
            } else {
                console.log('🎥 Video duplicado omitido:', f.name);
            }
        });
        console.log('🎥 Total archivos después de videos:', selectedFiles.length);
        console.log('🎥 Array completo:', selectedFiles.map(f => f.name + ' (' + f.type + ')'));
        console.log('🎥 Llamando a renderPreviews...');
        renderPreviews();
        this.value = ''; // Limpiar para permitir reselección
    });
    
    // Al enviar el formulario, separar archivos por tipo
    $('#form-publicar').on('submit', function(e) {
        if (selectedFiles.length > 0) {
            // Eliminar inputs existentes
            $(this).find('input[type="file"]').remove();
            
            // Separar imágenes y videos
            const images = selectedFiles.filter(f => f.type.startsWith('image/'));
            const videos = selectedFiles.filter(f => f.type.startsWith('video/'));
            
            // Crear input para imágenes si hay
            if (images.length > 0) {
                const imageInput = document.createElement('input');
                imageInput.type = 'file';
                imageInput.name = 'fotos[]';
                imageInput.multiple = true;
                imageInput.style.display = 'none';
                const imageDT = new DataTransfer();
                images.forEach(f => imageDT.items.add(f));
                imageInput.files = imageDT.files;
                this.appendChild(imageInput);
            }
            
            // Crear input para videos si hay
            if (videos.length > 0) {
                const videoInput = document.createElement('input');
                videoInput.type = 'file';
                videoInput.name = 'videos[]';
                videoInput.multiple = true;
                videoInput.style.display = 'none';
                const videoDT = new DataTransfer();
                videos.forEach(f => videoDT.items.add(f));
                videoInput.files = videoDT.files;
                this.appendChild(videoInput);
            }
        }
    });

    // Feedback visual al publicar
    const form = document.getElementById('form-publicar');
    if(form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if(btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
            }
        });
    }
});