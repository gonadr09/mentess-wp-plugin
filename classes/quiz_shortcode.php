<?php

    class quiz_shortcode {

        public function get_quiz($quiz_id) {
            global $wpdb;
            $table = "{$wpdb->prefix}lg_quizzes";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE `quiz_id` = %d", $quiz_id);
            $data = $wpdb->get_row($query, ARRAY_A);
            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        public function get_sections($quiz_id) {
            global $wpdb;
            $table = "{$wpdb->prefix}lg_sections";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE quiz_id = %d ORDER BY `order`", $quiz_id);
            $data = $wpdb->get_results($query,ARRAY_A);
            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        public function get_questions_of_quiz($quiz_id) {
            global $wpdb;

            // Obtener las preguntas junto con el tipo de respuesta
            $query = $wpdb->prepare("
                SELECT q.question_id, q.question, q.order, q.response_type_id, rt.response_type
                FROM {$wpdb->prefix}lg_questions q
                LEFT JOIN {$wpdb->prefix}lg_sections s ON q.section_id = s.section_id
                LEFT JOIN {$wpdb->prefix}lg_quizzes z ON s.quiz_id = z.quiz_id
                LEFT JOIN {$wpdb->prefix}lg_responses_type rt ON q.response_type_id = rt.response_type_id
                WHERE z.quiz_id = %d
                ORDER BY q.order
            ", $quiz_id);
            
            $data = $wpdb->get_results($query, ARRAY_A);
            
            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        public function get_questions_of_section($section_id) {
            global $wpdb;

            // Obtener las preguntas junto con el tipo de respuesta
            $query = $wpdb->prepare("
                SELECT q.question_id, q.question, q.order, q.response_type_id, rt.response_type
                FROM {$wpdb->prefix}lg_questions q
                LEFT JOIN {$wpdb->prefix}lg_responses_type rt ON q.response_type_id = rt.response_type_id
                WHERE q.section_id = %d
                ORDER BY q.order
            ", $section_id);
            $data = $wpdb->get_results($query, ARRAY_A);

            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        public function get_response_options_by_type() {
            global $wpdb;

            // Obtener todas las opciones de respuesta
            $query = "SELECT * FROM {$wpdb->prefix}lg_response_options";
            $response_options = $wpdb->get_results($query, ARRAY_A);

            if(empty($response_options)){
                $response_options = array();
            }

            // Organizar las opciones de respuesta por response_type_id
            $options_by_type = [];
            foreach ($response_options as $option) {
                $response_type_id = $option['response_type_id'];
                if (!isset($options_by_type[$response_type_id])) {
                    $options_by_type[$response_type_id] = [];
                }
                $options_by_type[$response_type_id][] = $option;
            }
            return $options_by_type;
        }


        public function form_open($quiz_id, $quiz_title, $user_quiz_id) {
            $html = "
                <br>
                <div class='wrap '>
                    <h1>$quiz_title</h1>
                    <br>
                    <form method='POST'>
                    <input type='hidden' name='quiz_id' value='$quiz_id'>
                    
            ";
            if ($user_quiz_id) {
                $html .= "<input type='hidden' name='user_quiz_id' value='$user_quiz_id'>";
            }
            return $html;
        }


        public function form_close() {
            $html = "
                    <input type='submit' id='quiz-responses-submit' name='quiz-responses-submit' class='btn btn-primary page-title-action' value='Enviar'>
                </form>
            </div>  
            ";
            return $html;
        }


        function from_input($value, $options_by_type){
            $question_id = $value['question_id'];
            $question = $value['question'];
            $order = $value['order'];
            $response_type_id = $value['response_type_id'];
            $response_type = $value['response_type'];

            $html = "";
            if ($response_type == "select"){
                $html = "
                    <div class='form-group'>
                        <label for='$question_id' class='form-label'> $order) $question</label>
                        <select class='form-select' id='$question_id' name='$question_id' required>
                            <option selected disabled value=''>-- Elige una opción --</option>
                ";
                if (isset($options_by_type[$response_type_id])) {
                    foreach ($options_by_type[$response_type_id] as $option) {
                        $response_option_id = $option['response_option_id'];
                        $response_text = $option['response_text'];
                        $response_value = $option['response_value'];        
                        $html .= "<option value='$response_value~$response_text'>$response_text</option>";
                    }
                }
                $html .= "
                        </select>
                    </div><br>
                ";
            } elseif ($response_type == 'number') {
                $html = "
                <div class='form-group'>
                    <label for='$question_id' class='form-label'>$order) $question</label>
                    <input type='number' class='form-control' name='$question_id' id='$question_id' required>
                </div><br>
            ";
            } else {
                $html = "
                    <div class='form-group'>
                        <label for='$question_id' class='form-label'>$order) $question</label>
                        <input type='text' class='form-control' name='$question_id' id='$question_id' required>
                    </div><br>
                ";
            }
            return $html;
        }


        function user_has_bought_product($user_id, $wc_product_id) {
            // Verificar si el usuario ha comprado el producto
            if (!function_exists('wc_customer_bought_product')) {
                return false;
            }
        
            $customer = get_userdata($user_id);
            if (!$customer) {
                return false;
            }
        
            $email = $customer->user_email;
            return wc_customer_bought_product($email, $user_id, $wc_product_id);
        }


        function get_user_quiz($quiz_id, $user_id) {
            global $wpdb;
            $table = "{$wpdb->prefix}lg_user_quiz";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE quiz_id = %d AND user_id = %d", $quiz_id, $user_id);
            $data = $wpdb->get_row($query, ARRAY_A);
            if(empty($data)){
                $data = array();
            }
            return $data;
        }

        
        function create_user_quiz($id){
            global $wpdb;
            $quiz = $this->get_quiz($id);
            $user_id = get_current_user_id();

            $wpdb->insert(
                "{$wpdb->prefix}lg_user_quiz",
                [
                    'user_id' => $user_id,
                    'quiz_id' => $quiz['quiz_id'],
                    'is_complete' => false
                ]
            );
            $user_quiz_id = $wpdb->insert_id;
            return $user_quiz_id;
        }


        function get_user_response($user_quiz_id, $question_id) {
            global $wpdb;
            $table = "{$wpdb->prefix}lg_user_responses";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE user_quiz_id = %d AND question_id = %d", $user_quiz_id, $question_id);
            $data = $wpdb->get_row($query, ARRAY_A);
            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        function get_user_responses($user_quiz_id) {
            global $wpdb;
            $table = "{$wpdb->prefix}lg_user_responses";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE user_quiz_id = %d", $user_quiz_id);
            $data = $wpdb->get_results($query, ARRAY_A);
            if(empty($data)){
                $data = array();
            }
            return $data;
        }


        function quiz_html_builder($quiz_id){
            // Obtener el ID del usuario actual
            $user_id = get_current_user_id();
            $user = wp_get_current_user();
            
            // Obtener la encuesta
            $quiz = $this->get_quiz($quiz_id);
            $quiz_name = $quiz['name'];
            $quiz_id = $quiz['quiz_id'];
            $wc_product_id = $quiz['wc_product_id'];
            
            // Verificar si el usuario ha comprado el producto
            if (!in_array('administrator', $user->roles) && !$this->user_has_bought_product($user_id, $wc_product_id)) {
                return "
                    <div class='my-5'>
                        <h3>No tienes accesos para ver la encuesta $quiz_name</h3>
                        <p>Solo los usuarios que hayan comprado esta encuesta tienen acceso para realizarla. Por favor, ingresa a nuestra tienda, busca la encuesta y comprala para poder acceder.</p>
                        <p><small>Si ya has comprado la encuesta y no puedes acceder, por favor, contactanos para resolver el problema.</small></p>
                    </div>
                ";
            }

            // Obtener la encuesta-usuario
            $user_quiz = $this->get_user_quiz($quiz_id, $user_id);
            $user_quiz_id = isset($user_quiz['user_quiz_id'])? $user_quiz['user_quiz_id'] : null;
            $user_quiz_is_complete = isset($user_quiz['is_complete'])? $user_quiz['is_complete'] : null;

            // Verificar que no esté completada
            if ($user_quiz_is_complete) {
                return "
                <div class='my-5'>
                    <h3>Resultados de la encuesta</h3>
                    <p>Gracias por completar la encuesta.</p>
                </div>
                ";
            }

            // Obtener todas las secciones de la encuesta
            $html_sections = "";
            $sections_list = $this->get_sections($quiz_id);
            foreach ($sections_list as $key => $value) {
                $section_id = $value['section_id'];
                $name = $value['name'];
                $order = $value['order'];

                $html_section_begin = "
                    <div>
                        <h3>Sección $order: $name</h3><br>
                ";
                $html_section_end = "<div><br>";

                // Obtener todas las preguntas de la sección
                $html_questions = "";
                $questions_list = $this->get_questions_of_section($section_id);
                $options_by_type = $this->get_response_options_by_type();
                foreach ($questions_list as $key => $value) {
                    $html_questions .= $this->from_input($value, $options_by_type);
                }

                $html_sections .= $html_section_begin;
                $html_sections .= $html_questions;
                $html_sections .= $html_section_end;
            }

            $html = $this->form_open($quiz_id, $quiz_name, $user_quiz_id);
            $html .= $html_sections;
            $html .= $this->form_close();

            return $html;
        }


        function check_if_quiz_is_complete($user_quiz_id, $questions_list) {
            $user_responses_list = $this->get_user_responses($user_quiz_id);
            $user_responses_count = count($user_responses_list);
            $questions_count = count($questions_list);
            if ($user_responses_count == $questions_count) {
                global $wpdb;
                $table = "{$wpdb->prefix}lg_user_responses";
                $result = $wpdb->update(
                    $table,
                    ['is_complete' => true],
                    ['user_quiz_id' => $user_quiz_id],
                    ['%d'],
                    ['%d']
                );
                return true;
            } else {
                return false;
            }
        }


        function save_form($post){
            global $wpdb;

            $quiz_id = isset($post['quiz_id'])? intval($post['quiz_id']) : null;
            $user_quiz_id = isset($post['user_quiz_id'])? intval($_POST['user_quiz_id']) : null;
            //$user_id = get_current_user_id();
            //$user_quiz_id = $_quiz_shortcode_instance->get_user_quiz($quiz_id, $user_id)
    
            if (!$user_quiz_id) {
                $user_quiz_id = $this->create_user_quiz($quiz_id);
            }
    
            $questions_list = $this->get_questions_of_quiz($quiz_id);
    
            foreach ($questions_list as $key => $value) {
                $question_id = $value['question_id'];
                if(isset($post[$question_id])){
                    $response_type = $value['response_type'];
    
                    if ($response_type == 'select') {
                        $input_value = $post[$question_id];
    
                        // Separar el valor de la respuesta del texto de la respuesta (ejemplo: '2~Sí')
                        list($response_value, $response_text) = explode('~', $input_value);
    
                        $data_prepared = [
                            'user_quiz_id' => $user_quiz_id,
                            'question_id' => $question_id,
                            'response_text' => $response_text,
                            'response_value' => intval($response_value)
                        ];
                    } else {
                        $response_text = $post[$question_id];
                        $data_prepared = [
                            'user_quiz_id' => $user_quiz_id,
                            'question_id' => $question_id,
                            'response_text' => $response_text,
                            'response_value' => 0,
                        ];
                    }
                    $format = ['%d', '%d', '%s', '%d'];
    
                    $user_response = $this->get_user_response($user_quiz_id, $question_id);
    
                    $table = "{$wpdb->prefix}lg_user_responses";
                    if ($user_response) {
                        // Actualizar respuesta-pregunta existente
                        $result = $wpdb->update(
                            $table,
                            $data_prepared,
                            ['response_id' => $user_response['response_id']],
                            $format,
                            ['%d']
                        );
                    } else {
                        // Insertar nueva respuesta-pregunta
                        $result = $wpdb->insert($table, $data_prepared, $format);
                    }
            
                    if ($result === false) {
                        $error_messages[] = $wpdb->last_error;
                        $error_message_text = implode('<br>', $error_messages);
                        $message = '
                            <div id="message" class="notice error">
                                <p><strong>Hubo un error al responder la encuesta:</strong></p>
                                <p>' . $error_message_text . '</p>
                            </div>
                        ';
                    } else {
                        $message = '
                        <div id="message" class="notice updated">
                            <p><strong>Respuestas correctamente guardadas.</strong></p>
                        </div>
                    ';
                    }
                }
            }

            $is_quiz_completed = $this->check_if_quiz_is_complete($user_quiz_id, $questions_list);

            if ($is_quiz_completed) {
                $message = $this->show_results($quiz_id, $user_quiz_id);
                return $message;
            }

            return $message;
        }

        function show_results($quiz_id, $user_quiz_id) {
            $results_per_category = $this->get_results_per_category($user_quiz_id);
            $sections_list = $this->get_sections($quiz_id);
            $quiz = $this->get_quiz($quiz_id);
            $general_answers = $this->get_general_answers($user_quiz_id);
            $quiz_name = $quiz['name'];

            $html_general_anser = '';
            foreach ($general_answers as $general_anwser){
                $question = $general_anwser['question'];
                $response_text = $general_anwser['response_text'];

                $html_general_anser .= "
                        <tr>
                            <td style='background-color: #467BE9; color: #FFF;'>$question</td>
                            <td>$response_text</td>
                        </tr>
                ";
            }
            $html_result = "
                <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

                <div class='container'>
                    <h1 class='text-center mt-5 mb-2'>$quiz_name</h1>
                    <h6 class='text-center mb-3'>Evaluación para la orientación profesional</h6>
                    <div class='col-9 mx-auto'>
                        <table class='table table-striped table-bordered'>
                            <tbody>
                                ". $html_general_anser ."
                            </tbody>
                        </table>
                    </div>
            ";

            foreach ($sections_list as $section) {
                if ($section['high_score'] > 0) {
                    $html_category_winners = '';
                    $html_list_result = '';

                    $chart_data_labels = array();
                    $chart_data_values = array();

                    foreach ($results_per_category as $category) {
                        if ($category['section_id'] == $section['section_id']) {
                            if ($category['total_value'] >= $section['high_score']){
                                $html_category_winners .= '
                                <div class="mt-4">
                                    <h5 style="color: #467be9">'. esc_html($category['subtitle_result']) .'</h5>
                                    <h5>'. esc_html($category['title_result']) .'</h5>
                                    <p>'. esc_html($category['text_result']) .'</p>
                                </div>
                                ';
                            }
                            $html_list_result .= '<li>' . esc_html($category['name']) . ': ' . esc_html($category['total_value']) . '</li>';
                            $chart_data_labels[] = $category['name'];
                            $chart_data_values[] = $category['total_value'];
                        }
                    }
                    $html_result .= '
                    <div>
                        <hr class="my-4 col-8 mx-auto">
                        <h2 class="text-center">Sección ' . $section['order'] . ': '. $section['name'] .'</h2>
                        <h6 class="text-center">Tus resultados revelan que:</h6>
                        '. $html_category_winners .'
                        <h4 class="mt-4">Listado</h4>
                        <ul>
                            '. $html_list_result .'
                        </ul>
                        '. $this->draw_chart($section, $chart_data_labels, $chart_data_values) .'
                    </div>
                    ';
                }
            }
            return $html_result;
        }


        function get_general_answers($user_quiz_id) {
            global $wpdb;
            $query = $wpdb->prepare("               
                SELECT usr_res.response_text, q.question, q.order, q.section_id
                FROM {$wpdb->prefix}lg_user_responses as usr_res
                JOIN {$wpdb->prefix}lg_questions as q ON usr_res.question_id = q.question_id
                JOIN {$wpdb->prefix}lg_sections as s ON q.section_id = s.section_id
                WHERE usr_res.user_quiz_id = %d AND s.high_score = 0
                ORDER BY q.order
            ", $user_quiz_id);
            $general_answers = $wpdb->get_results($query, ARRAY_A);
            return $general_answers;
        }


        function get_results_per_category($user_quiz_id) {
            global $wpdb;
            $query = $wpdb->prepare("               
                SELECT
                    cat.section_id,
                    cat.category_id,
                    cat.name,
                    cat.title_result,
                    cat.subtitle_result,
                    cat.text_result,
                    SUM(usr_res.response_value) as total_value
                FROM {$wpdb->prefix}lg_user_responses as usr_res
                JOIN {$wpdb->prefix}lg_questions as q ON usr_res.question_id = q.question_id
                JOIN {$wpdb->prefix}lg_categories as cat ON q.category_id = cat.category_id
                WHERE usr_res.user_quiz_id = %d
                GROUP BY cat.category_id
                ORDER BY total_value DESC
            ", $user_quiz_id);
            $results_per_category = $wpdb->get_results($query, ARRAY_A);
            return $results_per_category;
        }


        function draw_chart($section, $chart_data_labels, $chart_data_values) {
            $section_name = $section['name'];
            $section_id = $section['section_id'];
            $chart_type = $section['chart_type'];
            
            $min_param = '';
            if ($chart_type == 'radar' ) {
                $min_param = '
                    scales: {
                        r: {
                            angleLines: {
                                display: false
                            },
                            suggestedMin: 0,
                        }
                    }
                ';
            }

            $html = '
                <div class="col-6 mx-auto">
                <canvas id="chart-'. $section_id .'"></canvas>

                <script type="text/javascript">
                    const chart_data_labels'. $section_id .' = '. json_encode($chart_data_labels) .';
                    const chart_data_values'. $section_id .' = '. json_encode($chart_data_values) .';

                    const ctx'. $section_id .' = document.getElementById("chart-'. $section_id .'");
                    new Chart(ctx'. $section_id .', {
                        type: "'. $chart_type .'",
                        data: {
                            labels: chart_data_labels'. $section_id .',
                            datasets: [{
                                label: "'. $section_name .'",
                                data: chart_data_values'. $section_id .',
                            }],
                        },
                        options: {
                            indexAxis: "y",
                            '. $min_param .'
                        }
                    });
                </script>

                </div>
            ';
            return $html;
        }
    }
?>