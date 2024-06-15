<?php
    global $wpdb;

    // Peticiones POST
    if (isset($_POST['save-section'])) {
        $section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : null;
        $quiz_post = intval($_POST['quiz']);
        $name_post = sanitize_text_field($_POST['name']);
        $order_post = intval($_POST['order']);
        //$responses_type_post = $_POST['responses_type']; // agregar sanitize_text_field
        $high_score_post = intval($_POST['high_score']);
        $low_score_post = intval($_POST['low_score']);

        $errors = false;

        $data_prepared = [
            'quiz_id' => $quiz_post,
            'name' => $name_post,
            'order' => $order_post,
            //'responses_type' => $responses_type_post,
            'high_score' => $high_score_post,
            'low_score' => $low_score_post,
        ];

        $format = ['%d', '%s', '%d', '%s', '%d', '%d'];
        
        if ($section_id) {
            // Actualizar sección existente
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_sections",
                $data_prepared,
                ['section_id' => $section_id],
                $format,
                ['%d']
            );
        } else {
            // Insertar nueva sección
            $result = $wpdb->insert("{$wpdb->prefix}lg_sections", $data_prepared, $format);
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
                <p><strong>Sección guardada correctamente.</strong></p>
            </div>
        ';
        }
    }


    // Peticiones GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $section_id = '';
    $quiz_id = '';
    $name = '';
    $order = '';
    //$responses_type = '';
    $high_score = '';
    $low_score = '';
    $title = 'Agregar';

    $quiz_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_quizzes
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
    }

    if ($id > 0) {
        $query = $wpdb->prepare("
            SELECT s.section_id, s.name AS section_name, s.order, s.high_score, s.low_score, q.quiz_id, q.name AS quiz_name
            FROM {$wpdb->prefix}lg_sections s
            INNER JOIN {$wpdb->prefix}lg_quizzes q ON s.quiz_id = q.quiz_id
            WHERE s.section_id = %d
        ", $id);
        $section = $wpdb->get_row($query, ARRAY_A);
    
        if ($section) {
            $name = $section['section_name'];
            $quiz_id = $section['quiz_id'];
            $quiz_name = $section['quiz_name'];
            $order = $section['order'];
            //$responses_type = $section['responses_type'];
            $high_score = $section['high_score'];
            $low_score = $section['low_score'];
            $title = "Guardar";
        }
    }
?>

<div class='wrap'>
    <!-- Section -->
    <h1 id='section-title'><?php echo esc_attr($title); ?> sección</h1>
    <hr class="wp-header-end"><br>
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <form method='post' name='section-post' id='section-post' class='validate' novalidate='novalidate'>
        <input name='action' type='hidden' value='section-post'>
        <?php if ($id > 0) : ?>
            <input name='section_id' type='hidden' value='<?php echo esc_attr($id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' value='<?php echo esc_attr($name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='order'>Orden</label></th>
                    <td><input type='number' class='small-text' name='order' type='text' id='order' value='<?php echo esc_attr($order); ?>'></td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='quiz'>Encuesta</label></th>
                    <td>
                        <select name="quiz" id="quiz">
                            <option value=''>-- Elige una opción --</option>
                            <?php
                                
                                foreach ($quiz_list as $key => $value) {
                                    $option_quiz_id = $value['quiz_id'];
                                    $option_quiz_name = $value['name'];
                                    $selected = $option_quiz_id == $quiz_id ? 'selected' : '';
                                    echo "
                                        <option value='$option_quiz_id' $selected>$option_quiz_name</option>
                                    ";                                
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='low_score'>Puntaje menor</label></th>
                    <td><input type="number" class='small-text' name='low_score' type='text' id='low_score' value='<?php echo esc_attr($low_score); ?>'></td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='high_score'>Puntaje mayor</label></th>
                    <td><input type="number" class='small-text' name='high_score' type='text' id='high_score' value='<?php echo esc_attr($high_score); ?>'></td>
                </tr>

                <tr>
                    <th>Referencias de puntajes:</th>
                    <td>
                        <b>Bajo:</b> Cualquier resultado menor o igual a "puntaje menor".<br>
                        <b>Medio:</b> Cualquier resultado entre el "puntaje menor" y el "puntaje mayor".<br>
                        <b>Alto:</b> Cualquier resultado mayor o igual a "puntaje mayor".<br>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class='submit'>
            <input type='submit' name='save-section' id='save-section' class='button button-primary' value='Guardar'>
            <a href="admin.php?page=section_list" class="button button-secondary">Volver</a>
        </p>
    </form>
</div>