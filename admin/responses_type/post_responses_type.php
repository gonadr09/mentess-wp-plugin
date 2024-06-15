<?php
    global $wpdb;

    // Peticiones POST
    if (isset($_POST['save-response-type'])) {
        $response_type_id = !empty($_POST['response_type_id']) ? intval($_POST['response_type_id']) : null;
        $name_post = sanitize_text_field($_POST['name']);
        $response_type_post = sanitize_text_field($_POST['response_type']);

        $errors = false;

        $data_prepared = [
            'name' => $name_post,
            'response_type' => $response_type_post,
        ];

        $format = ['%s', '%s'];
        
        if ($response_type_id) {
            // Actualizar sección existente
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_responses_type",
                $data_prepared,
                ['response_type_id' => $response_type_id],
                $format,
                ['%d']
            );
        } else {
            // Insertar nueva sección
            $result = $wpdb->insert("{$wpdb->prefix}lg_responses_type", $data_prepared, $format);
        }

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar la sección:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Sección guardada correctamente.</strong> <a href="admin.php?page=response_type_list">Haz clic aquí para ver el listado</a></p>
            </div>
        ';
        }
    }


    // Peticiones GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $response_type_id = '';
    $name = '';
    $responses_type = '';
    $enum_values = '';
    $title = 'Agregar';

    // Obtener los valores posibles del campo response_type (ENUM)
    $sql = "SHOW COLUMNS FROM {$wpdb->prefix}lg_responses_type LIKE 'response_type'";
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($results)) {
        $enum_values = $results[0]['Type'];
        preg_match("/^enum\(\'(.*)\'\)$/", $enum_values, $matches);
        $enum_values = explode("','", $matches[1]);
    }

    if ($id > 0) {
        $query = $wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}lg_responses_type WHERE response_type_id = %d
        ", $id);
        $responses_type_result = $wpdb->get_row($query, ARRAY_A);
    
        if ($responses_type_result) {
            $response_type_id = $responses_type_result['response_type_id'];
            $name = $responses_type_result['name'];
            $response_type = $responses_type_result['response_type'];
            $title = "Guardar";
        }
    }
?>

<div class='wrap'>
    <!-- Section -->
    <h1 id='responses_type-title'><?php echo esc_attr($title); ?> tipo de respuesta</h1>
    <hr class="wp-header-end"><br>
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <form method='post' name='responses_type-post' id='responses_type-post' class='validate' novalidate='novalidate'>
        <input name='action' type='hidden' value='responses_type-post'>
        <?php if ($id > 0) : ?>
            <input name='response_type_id' type='hidden' value='<?php echo esc_attr($response_type_id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' value='<?php echo esc_attr($name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='responses_type'>Tipo de respuesta</label></th>
                    <td>
                        <select name="response_type" id="response_type">
                            <option value=''>-- Elige una opción --</option>
                            <?php
                                foreach ($enum_values as $value) {
                                    $selected = $response_type == $value ? 'selected' : '';
                                    echo "<option value='$value' $selected>$value</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class='submit'>
            <input type='submit' name='save-response-type' id='save-response-type' class='button button-primary' value='Guardar'>
            <a href="admin.php?page=response_type_list" class="button button-secondary">Volver</a>
        </p>
    </form>
</div>