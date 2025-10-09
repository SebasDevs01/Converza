/*
	By Osvaldas Valutis, www.osvaldas.info
	Available for use under the MIT License
*/

'use strict';

;( function ( document, window, index )
{
	var inputs = document.querySelectorAll( '.inputfile' );
	Array.prototype.forEach.call( inputs, function( input )
	{
		var label	 = input.nextElementSibling,
			labelVal = label.innerHTML;

		// Mostrar mensajes de retroalimentación
		function mostrarMensaje(tipo) {
			var mensaje = '';
			if (tipo === 'imagen') {
				mensaje = 'Se reemplazó el video por una imagen';
			} else if (tipo === 'video') {
				mensaje = 'Se reemplazó la imagen por un video';
			}
			var feedbackContainer = document.querySelector('#feedback-container');
			feedbackContainer.innerHTML = mensaje;
			feedbackContainer.style.display = 'block';
			setTimeout(function() {
				feedbackContainer.style.display = 'none';
			}, 3000);
		}

		input.addEventListener( 'change', function( e )
		{
			var fileName = '';
			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				label.querySelector( 'span' ).innerHTML = fileName;
			else
				label.innerHTML = labelVal;

			// Detectar tipo de archivo y manejar reemplazo
			var fileType = this.files[0].type;
			var previewContainer = document.querySelector('#preview-container');
			previewContainer.innerHTML = ''; // Limpiar vista previa

			if (fileType.startsWith('image/')) {
				var img = document.createElement('img');
				img.src = URL.createObjectURL(this.files[0]);
				img.alt = 'Vista previa de la imagen';
				previewContainer.appendChild(img);
				mostrarMensaje('imagen');
			} else if (fileType.startsWith('video/')) {
				var video = document.createElement('video');
				video.src = URL.createObjectURL(this.files[0]);
				video.controls = true;
				previewContainer.appendChild(video);
				mostrarMensaje('video');
			} else {
				alert('Tipo de archivo no soportado');
			}
		});

		// Firefox bug fix
		input.addEventListener( 'focus', function(){ input.classList.add( 'has-focus' ); });
		input.addEventListener( 'blur', function(){ input.classList.remove( 'has-focus' ); });
	});
}( document, window, 0 ));