<?php
    global $wpdb;

    // PETICIONES POST
    if (isset($_POST['save-response_options'])) {
        $response_type_id = intval($_POST['response_type_id']);
        $response_options_id_list = isset($_POST['response_option_id']) ? $_POST['response_option_id'] : [];
        $options_list = $_POST['response_text'];

        $options_count = 0;
        $errors = false;

        // Obtener todas las opciones de respuesta de la base de datos para este grupo de respuesta
        $query_options = $wpdb->prepare("
            SELECT response_option_id FROM {$wpdb->prefix}lg_response_options WHERE response_type_id = %d
        ", $response_type_id);
        $options_db = $wpdb->get_results($query_options, ARRAY_A);

        // Convertir la lista de preguntas de la base de datos a un array de IDs
        $options_db_ids = array_map(function($option) {
            return $option['response_option_id'];
        }, $options_db);

        // Eliminar preguntas que ya no están presentes en el formulario
        foreach ($options_db_ids as $response_option_id) {
            if (!in_array($response_option_id, $response_options_id_list)) {
                $wpdb->delete("{$wpdb->prefix}lg_response_options", array('response_option_id' => $response_option_id), array('%d'));
            }
        }
    
        // Guardar las preguntas (nuevas y actualizadas)
        foreach ($options_list as $key => $value) {
            $response_option_id = !empty($_POST['response_option_id'][$options_count]) ? intval($_POST['response_option_id'][$options_count]) : null;
            $response_text = sanitize_text_field($_POST['response_text'][$options_count]);
            $response_value = intval($_POST['response_value'][$options_count]);

            $data_prepared = [
                'response_type_id' => $response_type_id,
                'response_text' => $response_text,
                'response_value' => $response_value,
            ];

            $format = ['%d', '%s', '%d'];
            
            if ($response_option_id) {
                // Actualizar pregunta existente
                $result = $wpdb->update(
                    "{$wpdb->prefix}lg_response_options",
                    $data_prepared,
                    ['response_option_id' => $response_option_id],
                    $format,
                    ['%d']
                );
            } else {
                // Insertar nueva pregunta
                $result = $wpdb->insert("{$wpdb->prefix}lg_response_options", $data_prepared, $format);
            }

            if ($result === false) {
                $errors = true;
                $error_messages[] = $wpdb->last_error;
            }

            $options_count++;
        }

        if ($errors) {
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar las opciones de respuesta:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
                <div id="message" class="notice updated">
                    <p><strong>Opciones de respuesta guardadas correctamente.</strong></p>
                </div>
            ';
        }

    }

    // PETICIONES GET
    $response_type_id = isset($_GET['response-type-id']) ? intval($_GET['response-type-id']) : 0;

    if ($response_type_id > 0) {
        $query_response_type = $wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}lg_responses_type WHERE response_type_id = %d
        ", $response_type_id);
        $response_type = $wpdb->get_row($query_response_type, ARRAY_A);

        $query_response_options = $wpdb->prepare("
            SELECT response_option_id, response_type_id, response_text, response_value
            FROM {$wpdb->prefix}lg_response_options
            WHERE response_type_id = %d
        ", $response_type_id);
        $response_options = $wpdb->get_results($query_response_options, ARRAY_A);
    } else {
        $response_type_id = 0;
    };
        
?>

<div class='wrap'>

    <?php if ($response_type_id == 0) : ?>
        <h1 id='response_type-title'>Error al cargar las opciones de respuesta</h1>
        <p>No se pudo encontrar las opciones de respuesta para este grupo de respuesta.</p>
        <a href="admin.php?page=response_type_list" class='button button-primary'>Ir a tipos de respuesta</a>

    <?php else: ?>
        <div style="display: flex; gap: 10px">
            <h1 id='response_type-title'>Opciones de respuesta para el grupo <b><?php echo esc_attr($response_type['name']);?> </b></h1>
            <div style="padding: 9px 0 4px">
                <button type="button" id="add-new-item" class="button button-secondary">Añadir pregunta</button>
            </div>
        </div>

        <hr class="wp-header-end"><br>

        <?php 
            if (!empty($message)) {
                echo $message;
            }
        ?>

        <form method='post' name='response_opcions-post' id='response_opcions-post' class='validate'>
            
            <input name='response_type_id' type='hidden' value='<?php echo esc_attr($response_type['response_type_id']);?>'>

            <table id="response_opcions-table" class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all-1" type="checkbox">
                            <label for="cb-select-all-1">
                                <span class="screen-reader-text">Seleccionar todo</span>
                            </label>
                        </td>
                        <th scope="col" id="response_text" class="manage-column column-primary" abbr="Opción">Opción de respuesta</th>
                        <th scope="col" id="response_value" class="manage-column" abbr="Valor">Valor de la respuesta</th>
                        <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                        </th>
                    </tr>
                </thead>

                <tbody id="tbody">
                    <?php
                        foreach ($response_options as $key => $value) {
                            $response_option_id = $value['response_option_id'];
                            $response_text = $value['response_text'];
                            $response_value = $value['response_value'];

                            echo "
                                <tr class='form-required'>

                                    <input name='response_option_id[]' type='hidden' value='$response_option_id'>

                                    <th scope='row' class='check-column'>
                                        <input id='cb-select-1' type='checkbox' name='item-cb-select' value='1'>
                                        <label for='cb-select-1'>
                                            <span class='screen-reader-text'>$response_option_id</span>
                                        </label>
                                    </th>

                                    <td scope='row' class='column-primary'>
                                        <input type='text' class='large-text' name='response_text[]' required value='$response_text' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off'>
                                        <button type='button' class='toggle-row'><span class='screen-reader-text'>Mostrar más detalles</span></button>
                                    </td>

                                    <td scope='row'>
                                        <input type='number' class='large-text' name='response_value[]' required value='$response_value' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off'>
                                    </td>

                                    <td data-colname='Acciones:'>
                                        <button type='button' data-delete-item='$response_option_id' class='button delete-button' value='Eliminar'>Eliminar</button>
                                    </td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
            
            <p class='submit'>
                <input type='submit' name='save-response_options' id='save-response_options' class='button button-primary' value='Guardar'>
                <a href="admin.php?page=response_type_list" class='button button-secondary'>Volver</a>
            </p>
        </form>

        <!-- Linea para clonar -->
        <table class="locked-info">
            <tbody>
                <tr id="tr-clone" class="form-required">

                    <input name='response_option_id[]' type='hidden'>

                    <th scope="row" class="check-column">
                        <input id="cb-select-1" type="checkbox" name="quiz-1" value="1">
                        <label for="cb-select-1">
                            <span class="screen-reader-text">1</span>
                        </label>
                    </th>

                    <td scope="row" class="column-primary" data-colname='Opción:'>
                        <input type="text" class="large-text" name="response_text[]" aria-required="true" autocapitalize="none" autocorrect="off" autocomplete="off">
                        <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                    </td>

                    <td scope="row" data-colname='Valor:'>
                        <input type="number" class="large-text" name="response_value[]" aria-required="true" autocapitalize="none" autocorrect="off" autocomplete="off">
                    </td>

                    <td data-colname='Acciones:'>
                        <button type="button" data-delete-new-item id="delete-item-id" class="button delete-button" value="Eliminar">Eliminar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<script>
    /* Crear nuevas líneas de preguntas */
    const newItemButton = document.querySelector('#add-new-item')
    const itemTbody = document.querySelector('#tbody')
    const trClone = document.querySelector('#tr-clone')
    
    newItemButton.addEventListener('click', () => {
        const newLine = trClone.cloneNode(true);
        const deleteNewItemButton = newLine.querySelector('[data-delete-new-item]')
        itemTbody.appendChild(newLine)
        deleteNewItemButton.addEventListener('click', (e) => {
            deleteItem(e.target)
        })
    })

    /* Eliminar líneas de preguntas */
    const deleteItem = (element) => {
        const response = confirm("¿Está seguro que desea eliminar la opción de respuesta?\nEsta acción puede impactar en las encuestas ya realizadas")
            if (response) {
                const Trline = element.parentElement.parentElement.remove()
            }
    }

    const deleteItemButtons = document.querySelectorAll('[data-delete-item]')
    deleteItemButtons.forEach(deleteItemButton => {
        deleteItemButton.addEventListener('click', (e) => {
            deleteItem(e.target)
        })
    })
    

</script>