<?php
    global $wpdb;

    // Peticiones POST
    if (isset($_POST['save_quiz'])) {
        $quiz_id_post = !empty($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
        $name_post = sanitize_text_field($_POST['name']);
        $shortcode_post = sanitize_text_field($_POST['shortcode']);
        $is_active_post = isset($_POST['is_active']) ? 1 : 0;

        $errors = false;

        $data_prepared = [
            'name' => $name_post,
            'shortcode' => $shortcode_post,
            'is_active' => $is_active_post,
        ];

        $format = ['%s', '%s', '%d'];
        
        if ($quiz_id_post) {
            // Actualizar sección existente
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_quizzes",
                $data_prepared,
                ['quiz_id' => $quiz_id_post],
                $format,
                ['%d']
            );
        } else {
            // Insertar nueva sección
            $result = $wpdb->insert("{$wpdb->prefix}lg_quizzes", $data_prepared, $format);
        }

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar la encuesta:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Encuesta guardada correctamente.</strong></p>
            </div>
        ';
        }
    }

    // Peticiones GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $name = '';
    $shortcode = '';
    $is_active = '';
    $checked = '';
    $title = 'Agregar';

    if ($id > 0) {
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}lg_quizzes WHERE `quiz_id` = %d", $id);
        $quiz = $wpdb->get_row($query, ARRAY_A);
    
        if ($quiz) {
            $name = $quiz['name'];
            $shortcode = $quiz['shortcode'];
            $is_active = $quiz['is_active'];
            $checked = $quiz['is_active'] ? "checked" : "";
            $title = "Editar";
        }
    }
?>

<div class='wrap'>
    <h1 id='quiz-title'><?php echo esc_attr($title); ?> encuesta</h1>
    <hr class="wp-header-end"><br>
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <form method='post' name='quiz-post' id='quiz-post' class='validate' novalidate='novalidate'>
        <input name='action' type='hidden' value='quiz-post'>
        <?php if ($id > 0) : ?>
            <input name='quiz_id' type='hidden' value='<?php echo esc_attr($id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' value='<?php echo esc_attr($name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='shortcode'>Shortcode</label></th>
                    <td><input class='regular-text' name='shortcode' type='text' id='shortcode' value='<?php echo esc_attr($shortcode); ?>'></td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='is_active'>Activo</label></th>
                    <td>
                        <input type='checkbox' name='is_active' id='is_active' <?php echo esc_attr($checked); ?>>
                        <label for='is_active'></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'><input type='submit' name='save_quiz' id='save_quiz' class='button button-primary' value='<?php echo ($id > 0) ? "Actualizar encuesta" : "Agregar encuesta"; ?>'></p>
    </form>
</div>