$(document).ready(function(){
	// Actualizar el estado inicial de los likes
	$('.like').each(function(){
		var id = this.id;
		$.ajax({
			url: 'megusta.php',
			type: 'GET',
			data: {id:id},
			dataType: 'json',
			success:function(data){
				var likes = data['likes'];
				var text = data['text'];
				$("#likes_"+id).text(likes);
				$("#"+id).html(text);
			}
		});
	});

	// Manejar clics en el botón de like
	$(".like").click(function(){
		var id = this.id;

		$.ajax({
			url: 'megusta.php',
			type: 'POST',
			data: {id:id},
			dataType: 'json',

			success:function(data){
				var likes = data['likes'];
				var text = data['text'];

				$("#likes_"+id).text(likes);
				$("#"+id).html(text);
			}
		});
	});

	// Recuperar el estado inicial de las reacciones al cargar la página
	$('.reaction').each(function(){
		var id = $(this).data('post-id');
		$.ajax({
			url: 'megusta.php',
			type: 'GET',
			data: {id: id},
			dataType: 'json',
			success:function(data){
				var likes = data['likes'];
				var reaction = data['reaction'];
				$("#likes_"+id).text(likes);
				$("#reaction_"+id).attr('data-reaction-type', reaction);
			}
		});
	});

	// Manejar clics en las reacciones
	$('.reaction').click(function(){
		var id = $(this).data('post-id');
		var reaction = $(this).data('reaction-type');

		$.ajax({
			url: 'megusta.php',
			type: 'POST',
			data: {id: id, reaction: reaction},
			dataType: 'json',
			success:function(data){
				var likes = data['likes'];
				var newReaction = data['reaction'];

				// Actualizar el contador de likes y el tipo de reacción
				$("#likes_"+id).text(likes);
				$("#reaction_"+id).attr('data-reaction-type', newReaction);
			},
			error: function() {
				alert('Ocurrió un error al procesar la reacción.');
			}
		});
	});
});