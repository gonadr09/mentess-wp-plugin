<?php
    global $wpdb;

    // PETICIONES POST
    if (isset($_POST['save-questions'])) {
        $section_id = intval($_POST['question_section']);
        $question_list = $_POST['question'];
        $question_id_list = isset($_POST['question_id']) ? $_POST['question_id'] : [];
        $question_count = 0;
        $errors = false;

        // Obtener todas las preguntas de la base de datos para esta sección
        $query_questions = $wpdb->prepare("
            SELECT question_id FROM {$wpdb->prefix}lg_questions WHERE section_id = %d ORDER BY order
        ", $section_id);
        $questions_db = $wpdb->get_results($query_questions, ARRAY_A);

        // Convertir la lista de preguntas de la base de datos a un array de IDs
        $questions_db_ids = array_map(function($question) {
            return $question['question_id'];
        }, $questions_db);

        // Eliminar preguntas que ya no están presentes en el formulario
        foreach ($questions_db_ids as $question_id) {
            if (!in_array($question_id, $question_id_list)) {
                $wpdb->delete("{$wpdb->prefix}lg_questions", array('question_id' => $question_id), array('%d'));
            }
        }
    
        // Guardar las preguntas (nuevas y actualizadas)
        foreach ($question_list as $key => $value) {
            $question_id = !empty($_POST['question_id'][$question_count]) ? intval($_POST['question_id'][$question_count]) : null;
            $question = sanitize_text_field($_POST['question'][$question_count]);
            $question_order = intval($_POST['question_order'][$question_count]);
            $question_category = intval($_POST['question_category'][$question_count]);
            $response_type = sanitize_text_field($_POST['response_type'][$question_count]);

            $data_prepared = [
                'section_id' => $section_id,
                'category_id' => $question_category,
                'question' => $question,
                'order' => $question_order,
                'response_type' => $response_type
            ];

            $format = ['%d', '%d', '%s', '%d', '%s'];
            
            if ($question_id) {
                // Actualizar pregunta existente
                $result = $wpdb->update(
                    "{$wpdb->prefix}lg_questions",
                    $data_prepared,
                    ['question_id' => $question_id],
                    $format,
                    ['%d']
                );
            } else {
                // Insertar nueva pregunta
                $result = $wpdb->insert("{$wpdb->prefix}lg_questions", $data_prepared, $format);
            }


            if ($result === false) {
                $errors = true;
                $error_messages[] = $wpdb->last_error;
            }

            $question_count++;
        }

        if ($errors) {
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar las preguntas:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
                <div id="message" class="notice updated">
                    <p><strong>Preguntas guardadas correctamente.</strong></p>
                </div>
            ';
        }

    }

    // PETICIONES GET
    $section_id = isset($_GET['section-id']) ? intval($_GET['section-id']) : 0;

    if ($section_id > 0) {
        $query_section = $wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}lg_sections s WHERE s.section_id = %d
        ", $section_id);
        $section = $wpdb->get_row($query_section, ARRAY_A);

        $query_questions = $wpdb->prepare("
            SELECT q.question_id, q.question, q.order, q.category_id, q.response_type, c.name AS category_name
            FROM {$wpdb->prefix}lg_questions q
            INNER JOIN {$wpdb->prefix}lg_categories c ON q.category_id = c.category_id
            WHERE q.section_id = %d
            ORDER BY q.order ASC
        ", $section_id);
        $questions_of_section = $wpdb->get_results($query_questions, ARRAY_A);

        $categories_list = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}lg_categories
        ", ARRAY_A);
    } else {
        $section_id = 0;
    };
        
?>


<div class='wrap'>

    <?php if ($section_id == 0) : ?>
        <h1 id='section-title'>Error al cargar preguntas</h1>
        <p>No se pudo encontrar las preguntas de esta sección.</p>
        <a href="admin.php?page=section_list" class='button button-primary'>Ir a secciones</a>

    <?php else: ?>
        <div style="display: flex; gap: 10px">
            <h1 id='section-title'>Preguntas de la sección <b><?php echo esc_attr($section['name']);?> </b></h1>
            <div style="padding: 9px 0 4px">
                <button id="add-new-question" class="button button-secondary">Añadir pregunta</button>
            </div>
        </div>

        <hr class="wp-header-end"><br>

        <?php 
            if (!empty($message)) {
                echo $message;
            }
        ?>

        <form method='post' name='questions-post' id='questions-post' class='validate' novalidate='novalidate'>
            
            <input name='question_section' type='hidden' value='<?php echo esc_attr($section['section_id']);?>'>

            <table id="questions-table" class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all-1" type="checkbox">
                            <label for="cb-select-all-1">
                                <span class="screen-reader-text">Seleccionar todo</span>
                            </label>
                        </td>
                        <th scope="col" id="question" class="manage-column column-primary" abbr="Pregunta">Pregunta</th>
                        <th scope="col" id="order" class="manage-column column-comments" abbr="Orden">Orden</th>
                        <th scope="col" id="order" class="manage-column" abbr="Tipo respuesta" style="width: 167px">Tipo respuesta</th>
                        <th scope="col" id="category" class="manage-column" abbr="Categoría" style="width: 260px">Categoría</th>
                        <th scope="col" id="actions" class="manage-column column-author" abbr="is_active">Acciones</th>
                        </th>
                    </tr>
                </thead>

                <tbody id="questions-tbody">
                    <?php
                        foreach ($questions_of_section as $key => $value) {
                            $question_id = $value['question_id'];
                            $category_id = $value['category_id'];
                            $question = $value['question'];
                            $order = $value['order'];
                            $category_name = $value['category_name'];
                            $response_type = $value['response_type'];

                            $response_text_selected = $response_type == 'text' ? 'selected' : '';
                            $response_select_selected = $response_type == 'select' ? 'selected' : '';

                            echo "
                                <tr class='form-required'>

                                    <input name='question_id[]' type='hidden' value='$question_id'>

                                    <th scope='row' class='check-column'>
                                        <input id='cb-select-1' type='checkbox' name='question-cb-select' value='1'>
                                        <label for='cb-select-1'>
                                            <span class='screen-reader-text'>$question_id</span>
                                        </label>
                                    </th>

                                    <td scope='row' class='column-primary'>
                                        <input type='text' class='large-text' name='question[]' value='$question' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off'>
                                        <button type='button' class='toggle-row'><span class='screen-reader-text'>Mostrar más detalles</span></button>
                                    </td>

                                    <td scope='row'>
                                        <input type='number' class='large-text' name='question_order[]' value='$order' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off'>
                                    </td>

                                    <td scope='row' data-colname='Tipo respuesta:'>
                                        <select name='response_type[]'>
                                            <option value=''>-- Elige una opción --</option>
                                            <option value='text' $response_text_selected>text</option>
                                            <option value='select' $response_select_selected>select</option>
                                        </select>
                                    </td>

                                    <td scope='row' data-colname='Categoría:'>                  
                                        <select name='question_category[]'>
                                            <option value=''>-- Elige una opción --</option>
                                            ";

                                            foreach ($categories_list as $key => $value) {
                                                $option_category_id = $value['category_id'];
                                                $option_category_name = $value['name'];
                                                $selected = $option_category_id == $category_id ? 'selected' : '';
                                                echo "
                                                    <option value='$option_category_id' $selected>$option_category_name</option>
                                                ";                                
                                            }
                                        
                                        echo "
                                        </select>
                                    </td>

                                    <td data-colname='Acciones:'>
                                        <button type='button' data-delete-question='$question_id' class='button delete-button' value='Eliminar'>Eliminar</button>
                                    </td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
            
            <p class='submit'>
                <input type='submit' name='save-questions' id='save-questions' class='button button-primary' value='Guardar'>
                <a href="admin.php?page=section_list" class='button button-secondary'>Volver</a>
            </p>
        </form>

        <!-- Linea para clonar -->
        <table class="locked-info">
            <tbody>
                <tr id="tr-clone" class="form-required">

                    <input name='question_id[]' type='hidden'>

                    <th scope="row" class="check-column">
                        <input id="cb-select-1" type="checkbox" name="quiz-1" value="1">
                        <label for="cb-select-1">
                            <span class="screen-reader-text">1</span>
                        </label>
                    </th>

                    <td scope="row" class="column-primary">
                        <input type="text" class="large-text" name="question[]" aria-required="true" autocapitalize="none" autocorrect="off" autocomplete="off">
                        <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                    </td>

                    <td scope="row" data-colname='Orden:'>
                        <input type="number" class="large-text" name="question_order[]" aria-required="true" autocapitalize="none" autocorrect="off" autocomplete="off">
                    </td>

                    <td scope='row' data-colname='Tipo respuesta:'>
                        <select name='response_type[]'>
                            <option value=''>-- Elige una opción --</option>
                            <option value='text'>text</option>
                            <option value='select'>select</option>
                        </select>
                    </td>

                    <td scope="row" data-colname='Categoría:'>                  
                        <select name="question_category[]">
                            <option value="">-- Elige una opción --</option>
        
                            <?php
                                foreach ($categories_list as $key => $value) {
                                    $option_category_id = $value['category_id'];
                                    $option_category_name = $value['name'];
                                    echo "
                                        <option value='$option_category_id'>$option_category_name</option>
                                    ";
                                };
                            ?>

                        </select>
                    </td>

                    <td data-colname='Acciones:'>
                        <button type="button" data-delete-new-question='' id="delete-question-1" class="button delete-button" value="Eliminar">Eliminar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<script>
    /* Crear nuevas líneas de preguntas */
    const newQuestionButton = document.querySelector('#add-new-question')
    const questionsTbody = document.querySelector('#questions-tbody')
    const trClone = document.querySelector('#tr-clone')
    
    newQuestionButton.addEventListener('click', () => {
        const newLine = trClone.cloneNode(true);
        const deleteNewQuestionButton = newLine.querySelector('[data-delete-new-question]')
        questionsTbody.appendChild(newLine)
        deleteNewQuestionButton.addEventListener('click', (e) => {
            deleteQuestion(e.target)
        })
    })

    /* Eliminar líneas de preguntas */
    const deleteQuestion = (element) => {
        const response = confirm("¿Está seguro que desea eliminar la pregunta?\nEsta acción puede impactar en las encuestas ya realizadas")
            if (response) {
                const Trline = element.parentElement.parentElement.remove()
            }
    }

    const deleteQuestionButtons = document.querySelectorAll('[data-delete-question]')
    deleteQuestionButtons.forEach(deleteQuestionButton => {
        deleteQuestionButton.addEventListener('click', (e) => {
            deleteQuestion(e.target)
        })
    })
    

</script>