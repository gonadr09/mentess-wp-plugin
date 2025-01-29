jQuery(document).ready(function($){
    $('.share-form').on('submit', function(e){
        e.preventDefault();

        // Recoger los valores de los campos del formulario
        var email = $('#email').val();
        var user_name = $('input[name="user_name"]').val();
        var user_lastname = $('input[name="user_lastname"]').val();
        var quiz_id = $('input[name="quiz_id"]').val();  // Obtener el quiz_id del campo hidden

        var data = {
            action: 'send_invitation_email',
            email: email,
            user_name: user_name,
            user_lastname: user_lastname,
            quiz_id: quiz_id  // Añadir el quiz_id a los datos que se envían
        };

        // Hacer la solicitud AJAX
        $.post(mentess_ajax_object.ajax_url, data, function(response){
            // Mostrar el mensaje debajo del formulario
            $('.share-section').append('<div class="badge badge-light" style="margin: 12px auto 0;background-color: #FFE187;color: #333;">' + response + '</div>');
        });
    });
});
