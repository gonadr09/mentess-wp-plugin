<?php
    global $wpdb;
    $message = '';

    $user = wp_get_current_user();

    // Eliminar encuesta
    if (isset($_POST['delete-quiz-submit'])) {
        $delete_quiz_id = intval($_POST['delete-quiz-id']);
        $result = $wpdb->delete("{$wpdb->prefix}lg_user_quiz", array('user_quiz_id' => $delete_quiz_id), array('%d'));

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al eliminar:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Eliminado correctamente.</strong></p>
            </div>
        ';
        }
    }

    // Marcar encuesta como "no completada"
    if (isset($_POST['reset-quiz-submit'])) {
        $reset_quiz_id = intval($_POST['reset-quiz-id']);

        $query = $wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}lg_user_quiz WHERE user_quiz_id = %d
        ", $reset_quiz_id);
        $user_quiz = $wpdb->get_row($query, ARRAY_A);

        if ($user_quiz) {
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_user_quiz",
                ['is_complete' => false],
                ['user_quiz_id' => $user_quiz['user_quiz_id']],
                ['%d'],
                ['%d']
            );
        }

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al modificar:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Modificado correctamente.</strong></p>
            </div>
        ';
        }
    }

    // Obtener resultados
    $user_quiz_list = $wpdb->get_results("
        SELECT
        uq.user_quiz_id, uq.quiz_id, uq.user_id, uq.is_complete, uq.created_at, uq.updated_at,
        u.user_login, u.user_email, u.display_name,
        q.name
        FROM {$wpdb->prefix}lg_user_quiz uq
        LEFT JOIN {$wpdb->prefix}users u ON uq.user_id = u.ID
        LEFT JOIN {$wpdb->prefix}lg_quizzes q ON uq.quiz_id = q.quiz_id
    ", ARRAY_A);

    if(empty($user_quiz_list)) {
        $user_quiz_list = array();
    }
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <hr class="wp-header-end">
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <tr>
                <th scope="col" id="user" class="manage-column column-primary" abbr="Usuario">Usuario</th>
                <th scope="col" id="name" class="manage-column" abbr="Nombre">Nombre</th>
                <th scope="col" id="email" class="manage-column" abbr="Email">Email</th>
                <th scope="col" id="quiz" class="manage-column" abbr="Encuesta">Encuesta</th>
                <th scope="col" id="created_at" class="manage-column" abbr="Fecha inicio">Fecha inicio</th>
                <th scope="col" id="is_complete" class="manage-column" abbr="Completada">Resultado</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($user_quiz_list as $key => $value) {
                    $user_quiz_id = $value['user_quiz_id'];
                    $is_complete = $value['is_complete'];
                    $created_at = $value['created_at'];
                    $updated_at = $value['updated_at'];
                    $user_login = $value['user_login'];
                    $user_email = $value['user_email'];
                    $display_name = $value['display_name'];
                    $name = $value['name'];

                    echo "
                        <tr id='user-quiz-$user_quiz_id' class=''>
                            <td class='' data-colname='Nombre:'>$user_login</td>
                            <td class='' data-colname='Nombre:'>$display_name</td>
                            <td class='' data-colname='Email:'>$user_email</td>
                            <td class='' data-colname='Encuesta:'>$name</td>
                            <td class='' data-colname='Fecha:'>$created_at</td>
                            <td class='' data-colname='Resultado:'>";

                    if ($is_complete > 0) {
                        /* <span class='dashicons dashicons-yes-alt' style='color: green'></span></td> */

                        echo "
                            <a href='encuesta?user_quiz_id=$user_quiz_id' class='button' target='_blank' aria-label='Resultado'>
                                Ver
                            </a>
                        ";
                    } else {
                        /* <span class='dashicons dashicons-dismiss' style='color: #b32d2e'></span></td> */
                        echo "";
                    }
                    if (current_user_can('manage_options')) {
                        echo "
                            <td>
                                <div style='display: flex; gap: 10px'>
                                    <form method='post' name='reset-question-form' id='reset-question-form' class='validate' novalidate='novalidate'>
                                        <input name='reset-quiz-id' type='hidden' value='$user_quiz_id'>
                                        <input type='hidden' name='reset-quiz-submit' value='Reiniciar'>
                                        <button type='button' data-reset-quiz class='button-link' style='padding: 3px'>
                                            <span class='dashicons dashicons-update'></span>
                                        </button>
                                    </form>
                                    <form method='post' name='delete-question-form' id='delete-question-form' class='validate' novalidate='novalidate'>
                                        <input name='delete-quiz-id' type='hidden' value='$user_quiz_id'>
                                        <input type='hidden' name='delete-quiz-submit' value='Eliminar'>
                                        <button type='button' data-delete-quiz class='button-link' style='color: #b32d2e; padding: 3px'>
                                            <span class='dashicons dashicons-dismiss'></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        ";
                    }
                }
            ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteSectionButtons = document.querySelectorAll('[data-delete-quiz]');
        deleteSectionButtons.forEach(deleteSectionButton => {
            deleteSectionButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm("¿Está seguro que desea eliminar la encuesta del usuario?\nEsta acción no se puede deshacer.");
                if (response) {
                    const form = deleteSectionButton.closest('form');
                    form.submit();
                }
            });
        });

        const resetSectionButtons = document.querySelectorAll('[data-reset-quiz]');
        resetSectionButtons.forEach(resetSectionButton => {
            resetSectionButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm('¿Está seguro que desea cambiar el estado de la encuesta del usuario?');
                if (response) {
                    const form = resetSectionButton.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>