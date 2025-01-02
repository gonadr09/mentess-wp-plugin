<?php
    global $wpdb;
    $message = '';

    $user = wp_get_current_user();

    // Función para escapar comillas dobles en CSV
    function esc_csv($data) {
        return str_replace('"', '""', $data); // Escapar comillas dobles
    }

    // Exportar CSV
    if (isset($_POST['export_csv']) && isset($_POST['quiz_id'])) {
        $quiz_id = intval($_POST['quiz_id']); // Sanitiza el ID del cuestionario

        // Preparar Header del CSV
        $questions_query = $wpdb->prepare("
            SELECT s.section_id, s.name AS section_name, s.order AS section_order, 
                q.question_id, q.question, q.order AS question_order
            FROM {$wpdb->prefix}lg_questions q
            INNER JOIN {$wpdb->prefix}lg_sections s ON q.section_id = s.section_id
            WHERE s.quiz_id = %d
            ORDER BY s.order ASC, q.order ASC;
        ", $quiz_id);
    
        $questions = $wpdb->get_results($questions_query);

        // Preparar resultados
        $query = "
        SELECT 
            uq.user_id,
            q.question,
            ur.response_text,
            ur.response_value,
            sec.name AS section_name,
            sec.order AS section_order,
            q.order AS question_order,
            uq.created_at AS timestamp
        FROM {$wpdb->prefix}lg_user_quiz AS uq
        INNER JOIN {$wpdb->prefix}lg_user_responses AS ur ON ur.user_quiz_id = uq.user_quiz_id
        INNER JOIN {$wpdb->prefix}lg_questions AS q ON q.question_id = ur.question_id
        INNER JOIN {$wpdb->prefix}lg_sections AS sec ON sec.section_id = q.section_id
        INNER JOIN {$wpdb->prefix}lg_quizzes AS quiz ON quiz.quiz_id = sec.quiz_id
        WHERE quiz.quiz_id = %d
        ORDER BY uq.user_id ASC, sec.order ASC, q.order ASC
        ";
        $results = $wpdb->get_results($wpdb->prepare($query, $quiz_id));

        // Nombre del archivo CSV
        $filename = 'resultados_cuestionario_14_' . date('Y-m-d_H-i-s') . '.csv';

        // Cabecera del archivo CSV (Timestamp + preguntas)
        $csv_output = "\xEF\xBB\xBF"; // Añade el BOM para que Excel detecte UTF-8
        $csv_output .= "Timestamp";
        foreach ($questions as $question) {
            $csv_output .= '; [' . esc_csv($question->section_order) . '.' . esc_csv($question->question_order) . '] ' . esc_csv($question->question);
        }
        $csv_output .= "\n";

        // ------
        // Agrupar respuestas por usuario, secciones y preguntas
        $user_responses = [];
        foreach ($results as $row) {
            $user_id = $row->user_id;
            if (!isset($user_responses[$user_id])) {
                $user_responses[$user_id] = [
                    'timestamp' => $row->timestamp,
                    'responses' => []
                ];
            }
            $key = $row->section_order . '-' . $row->question_order;
            $user_responses[$user_id]['responses'][$key] = esc_csv($row->response_text ? $row->response_text : $row->response_value);
        }

        // Resultados del cuestionario por usuario
        foreach ($user_responses as $user_id => $data) {
            $csv_output .= $data['timestamp'];
            // Ordenar las respuestas según el orden de las preguntas y secciones
            foreach ($questions as $question) {
                $key = $question->section_order . '-' . $question->question_order;
                $response = isset($data['responses'][$key]) ? $data['responses'][$key] : '';
                $csv_output .= ';' . $response;
            }
            $csv_output .= "\n";
        }

        // Limpiar la salida y enviar el archivo CSV
        ob_clean();
        flush();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $csv_output;
        exit;
    }

    // Obtener resultados
    $quiz_list = $wpdb->get_results("
        SELECT q.quiz_id, q.name, q.is_active
        FROM {$wpdb->prefix}lg_quizzes q
        WHERE q.is_active = 1
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
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
                <th scope="col" id="id" class="manage-column column-primary" abbr="Usuario">ID de la encuesta</th>
                <th scope="col" id="name" class="manage-column" abbr="Nombre">Nombre de la encuesta</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($quiz_list as $key => $value) {
                    $quiz_id = $value['quiz_id'];
                    $quiz_name = $value['name'];

                    echo "
                        <tr id='user-quiz-$quiz_id' class=''>
                            <td class='' data-colname='Nombre:'>$quiz_id</td>
                            <td class='' data-colname='Email:'>$quiz_name</td>
                            <td class='' data-colname='Exportar:'>
                                <form style='margin-bottom: 10px' method='post' action=''>
                                    <input type='hidden' name='quiz_id' value='$quiz_id'>
                                    <input type='submit' name='export_csv' class='button button-primary' value='Exportar a CSV'>
                                </form>
                    ";
                }
            ?>
        </tbody>
    </table>
</div>