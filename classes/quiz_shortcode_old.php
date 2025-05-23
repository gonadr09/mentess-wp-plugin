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

                <div class='wrap lg-container'>

                    <h1 class='text-center' style='color: #3879F1'>$quiz_title</h1>

                    <br>



                    <p>Este cuestionario está diseñado para ayudarte a explorar tu vocación. Responde con sinceridad según tus verdaderas preferencias.

                    ¡Recuerda, no hay respuestas correctas o incorrectas, solo descubrimientos sobre ti mismo/a!</p>



                    <p><b>Consejos para responder:</b></p>

                    <ul>

                    <li>Sé auténtico/a: Responde según tus verdaderos gustos e inclinaciones.</li>

                    <li>Piensa en lo que disfrutas: Considera lo que te apasiona y te hace sentir motivado/a.</li>

                    <li>Imagina tu futuro: Visualiza cómo te gustarí­a pasar tu tiempo en el ámbito profesional.</li>

                    </ul>



                    <p><b>Instrucciones:</b></p>

                    <ul>

                    <li>Marca <b>Sí</b> si la afirmación describe algo que realmente disfrutas o te gustarí­a hacer.</li>

                    <li>Marca <b>No</b> si la afirmación no se ajusta a tus intereses o habilidades.</li>

                    <li>Marca <b>Tal vez</b> si aún no estás seguro/a o no has pensado en esa situación.</li>

                    </ul>



                    <p>¡Disfruta del viaje de autoexploración y descubrimiento!</p>



                    <br>



                    <form method='POST' class='mb-4'>

                    <input type='hidden' name='quiz_id' value='$quiz_id'>

                    

            ";

            if ($user_quiz_id) {

                $html .= "<input type='hidden' name='user_quiz_id' value='$user_quiz_id'>";

            }

            return $html;

        }





        public function form_close() {

            $html = "

                    <button type='submit' id='quiz-responses-submit' name='quiz-responses-submit' class='btn btn-primary page-title-action'>

                        Enviar formulario 

                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-send-fill' viewBox='0 0 16 16'>

                        <path d='M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z'/>

                        </svg>

                    </button>

                </form>

            </div>  

            ";

            return $html;

        }





        function from_input($value, $options_by_type, $user_responses_map){

            $question_id = $value['question_id'];

            $question = $value['question'];

            $order = $value['order'];

            $response_type_id = $value['response_type_id'];

            $response_type = $value['response_type'];



            $user_response_value = isset($user_responses_map[$question_id]) ? $user_responses_map[$question_id]['response_value'] : null;

            $user_response_text = isset($user_responses_map[$question_id]) ? $user_responses_map[$question_id]['response_text'] : null;



            $html = "";

            if ($response_type == "select"){

                $html = "

                    <div id='question-$question_id' class='form-group'>

                        <label for='$question_id' class='form-label'> $order) $question</label>

                        <select class='form-select' id='$question_id' name='$question_id'>

                            <option selected disabled value=''>-- Elige una opción --</option>

                ";

                if (isset($options_by_type[$response_type_id])) {

                    foreach ($options_by_type[$response_type_id] as $option) {

                        $response_option_id = $option['response_option_id'];

                        $response_text = $option['response_text'];

                        $response_value = $option['response_value'];

                        $selected = ($response_value == $user_response_value) ? "selected" : "";

                        $html .= "<option value='$response_value~$response_text' $selected>$response_text</option>";

                    }

                }

                $html .= "

                        </select>

                    </div><br>

                ";

            } elseif ($response_type == "radio"){

                $html = "

                    <div id='question-$question_id' class='radio-input'>

                        <p class='form-label'> $order) $question</p>

                        <div class='combo-radio'>

                ";

                if (isset($options_by_type[$response_type_id])) {

                    foreach ($options_by_type[$response_type_id] as $option) {

                        $response_option_id = $option['response_option_id'];

                        $response_text = $option['response_text'];

                        $response_value = $option['response_value'];

                        $checked = ($response_value == $user_response_value) ? "checked" : "";

                        /* <div class='form-check form-check-inline border'>

                                <input class='form-check-input' type='radio' name='$question_id' id='$question_id-$response_option_id' autocomplete='off' value='$response_value~$response_text'>

                                <label class='form-check-label' for='$question_id-$response_option_id'>$response_text</label>

                            </div>

                              <label class="label"> */

                        $html .= "

                            <label class='label'>

                                <input type='radio' id='$question_id-$response_option_id' name='$question_id' value='$response_value~$response_text' $checked/>

                                <p class='text'>$response_text</p>

                            </label>

                        ";

                    }

                }

                $html .= "

                    </div></div><br><br>

                ";

            } elseif ($response_type == 'number') {

                $html = "

                <div id='question-$question_id' class='form-group'>

                    <label for='$question_id' class='form-label'>$order) $question</label>

                    <input type='number' class='form-control' name='$question_id' id='$question_id' value='$user_response_text'>

                </div><br>

            ";

            } else {

                $html = "

                    <div id='question-$question_id' class='form-group'>

                        <label for='$question_id' class='form-label'>$order) $question</label>

                        <input type='text' class='form-control' name='$question_id' id='$question_id' value='$user_response_text'>

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





        function check_view_by_user_quiz_id($user_quiz_id) {

            $quiz_id = 0;

            // Si hay parametro user_quiz_id y si es administrador, cambiamos user_quiz_id por el del parametro

            if ($user_quiz_id) {

                global $wpdb;

                $table = "{$wpdb->prefix}lg_user_quiz";

                $query = $wpdb->prepare("SELECT * FROM $table WHERE user_quiz_id = %d", $user_quiz_id);

                $data = $wpdb->get_row($query, ARRAY_A);

                if (!empty($data) && $data['is_complete'] && (current_user_can('edit_others_posts') || get_current_user_id() == $data['user_id']) ) {

                        $user_quiz_id = $data['user_quiz_id'];

                        $quiz_id = $data['quiz_id'];

                } else {

                    $user_quiz_id = 0;

                }

            }

            return array('quiz_id' => $quiz_id, 'user_quiz_id' => $user_quiz_id);

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



            // Obtener la encuesta-usuario

            $user_quiz = $this->get_user_quiz($quiz_id, $user_id);

            $user_quiz_id = isset($user_quiz['user_quiz_id'])? $user_quiz['user_quiz_id'] : null;

            $user_quiz_is_complete = isset($user_quiz['is_complete'])? $user_quiz['is_complete'] : null;



            // Verificar que no esté completada

            if ($user_quiz_is_complete) {

                    return $this->show_results($quiz_id, $user_quiz_id);

            }

            

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



            // Obtener todas las secciones de la encuesta

            $html_sections = "";

            $sections_list = $this->get_sections($quiz_id);

            foreach ($sections_list as $key => $value) {

                $section_id = $value['section_id'];

                $name = $value['name'];

                $order = $value['order'];



                $html_section_begin = "

                    <div>

                        <h3 style='color: #3879F1'>$name</h3><br>

                ";

                $html_section_end = "</div><br>";



                // Obtener todas las preguntas de la sección

                $html_questions = "";

                $questions_list = $this->get_questions_of_section($section_id);

                $options_by_type = $this->get_response_options_by_type();



                # Crear array asociativo de respuestas del usuario {question_id: respuesta}

                $user_responses = $this->get_user_responses($user_quiz_id);

                $user_responses_map = [];

                foreach ($user_responses as $response) {

                    $user_responses_map[$response['question_id']] = $response;

                }

                foreach ($questions_list as $key => $value) {

                    $html_questions .= $this->from_input($value, $options_by_type, $user_responses_map);

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





        function update_if_quiz_is_complete($user_quiz_id, $questions_list) {

            $user_responses_list = $this->get_user_responses($user_quiz_id);

            $user_responses_count = count($user_responses_list);

            $questions_count = count($questions_list);

            if ($user_responses_count == $questions_count) {

                global $wpdb;

                $table = "{$wpdb->prefix}lg_user_quiz";

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

    

                    if ($response_type == 'select' or $response_type == 'radio') {

                        $input_value = $post[$question_id];



                        // Separar el valor de la respuesta del texto de la respuesta (ejemplo: '2~Sí')

                        list($response_value, $response_text) = explode('~', $input_value);

    

                        // Verificar que response_value y response_text no estén vacíos

                        if ($response_value !== '' && $response_text !== '') {

                            $data_prepared = [

                                'user_quiz_id' => $user_quiz_id,

                                'question_id' => $question_id,

                                'response_text' => $response_text,

                                'response_value' => intval($response_value)

                            ];

                        }

                    } else {

                        $response_text = $post[$question_id];

                        // Verificar que response_text no esté vacío

                        if ($response_text !== '') {

                            $data_prepared = [

                                'user_quiz_id' => $user_quiz_id,

                                'question_id' => $question_id,

                                'response_text' => $response_text,

                                'response_value' => 0,

                            ];

                        }

                    }



                    // Si data_prepared no está vacío, guardar en la base de datos

                    if (!empty($data_prepared)) {

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

                                <div class="lg-container mt-4">

                                    <div id="message" class="alert alert-danger mb-0">

                                        <strong>Hubo un error al responder la encuesta:</strong>

                                        <small>' . $error_message_text . '</small>

                                    </div>

                                </div>

                            ';

                        } else {

                            $message = '

                            <div class="lg-container mt-4">

                                <div id="message" class="alert alert-primary mb-0" role="alert">

                                    Respuestas guardadas correctamente. Aún te quedan preguntas por responder para finalizar el cuestionario.

                                </div>

                            </div>

                        ';

                        }

                    }

                }

            }



            $is_quiz_completed = $this->update_if_quiz_is_complete($user_quiz_id, $questions_list);



            if ($is_quiz_completed) {

                $message = $this->show_results($quiz_id, $user_quiz_id);

                return $message;

            } else {

                $message .= $this->quiz_html_builder($quiz_id);

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



            $html_general_answer = '';

            foreach ($general_answers as $general_anwser){

                $question = $general_anwser['question'];

                $response_text = $general_anwser['response_text'];



                $html_general_answer .= "

                        <tr>

                            <td style='background-color: #467BE9; color: #FFF;'>$question</td>

                            <td>$response_text</td>

                        </tr>

                ";

            }

            $html_result = "

                <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>



                <div id='pdf' class='mt-3 lg-container'>

                    <div class='d-flex justify-content-center justify-content-md-end gap-2'>

			<a class='btn btn-sm btn-primary' target='_blank' href='https://scholar-shine.com/wp-content/uploads/2025/03/Guia-de-resultados-de-Mentess.pdf'>
				Guía de resultados
			</a>

                        <button id='generate-pdf' class='btn btn-sm btn-danger'>

                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-file-earmark-pdf-fill' viewBox='0 0 16 16'>

                            <path d='M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z'/>

                            <path fill-rule='evenodd' d='M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103'/>

                            </svg>

                            Exportar a PDF

                        </button>

                    </div>



                    <div data-pdf='quiz-title-section'>

                        <div class='d-flex justify-content-center mt-3'>

                            <img id='logo_quiz' class='mx-auto' width='70px' src='" . esc_html($quiz['logo_url']) . "'></img>

                        </div>

                        <h1 class='text-center mt-2 mb-2' style='color: #4275DD;'>$quiz_name</h1>

                        <h5 class='text-center mb-3' style='color: #4275DD'>Evaluación para la orientación profesional</h5>

                    </div>

            ";



            foreach ($sections_list as $section) {

                // Comprobar si es una sección general (sin puntajes) o una seccion con puntajes

                if ($section['high_score'] <= 0) {

                    $html_result .= "

                    <hr class='my-4 col-8 mx-auto'>

                    <div data-pdf='general-answers-section'>

                        <h2 class='text-center'>". $section['name'] ."</h2>

                        <h6 class='text-center'>" . $section['description'] . "</h6>

                        <div class='col-lg-9 mx-auto'>

                            <table class='table table-striped table-bordered'>

                                <tbody>

                                    ". $html_general_answer ."

                                </tbody>

                            </table>

                        </div>

                    </div>

                    ";

                } else {

                    $html_category_winners = '';

                    $html_list_result = '';



                    $html_backup_winner = '';

                    $score_backup_winner = 0;



                    $chart_data_labels = array();

                    $chart_data_values = array();



                    foreach ($results_per_category as $category) {

                        if ($category['section_id'] == $section['section_id']) {

                            if ($category['total_value'] >= $section['high_score'] && $category['title_result'] != '') {

                                $html_category_winners .= '

                                <div class="mt-4" data-pdf="category-winner-text-section">

                                    <img width="100px" src="'. esc_html($category['image_url']) .'"></img>

                                    <h3 style="color: #467be9">'. esc_html($category['subtitle_result']) .'</h3>

                                    <h4>'. esc_html($category['title_result']) .'</h4>

                                    <p>'. esc_html($category['text_result']) .'</p>

                                </div>

                                ';

                            } elseif ($category['total_value'] > $score_backup_winner && $category['title_result'] != ''){

                                $html_backup_winner = '

                                    <div class="mt-4" data-pdf="category-winner-text-section">

                                        <img width="100px" src="'. esc_html($category['image_url']) .'"></img>

                                        <h3 style="color: #467be9">'. esc_html($category['subtitle_result']) .'</h3>

                                        <h4>'. esc_html($category['title_result']) .'</h4>

                                        <p>'. esc_html($category['text_result']) .'</p>

                                    </div>

                                ';

                                $score_backup_winner = $category['total_value'];

                            } elseif ($category['total_value'] == $score_backup_winner && $category['title_result'] != ''){

                                $html_backup_winner .= '

                                    <div class="mt-4" data-pdf="category-winner-text-section">

                                        <img width="100px" src="'. esc_html($category['image_url']) .'"></img>

                                        <h3 style="color: #467be9">'. esc_html($category['subtitle_result']) .'</h3>

                                        <h4>'. esc_html($category['title_result']) .'</h4>

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

                    <div data-pdf="scored-answers-section">

                        <hr class="my-4 col-8 mx-auto">

                        <h2 class="text-center">'. $section['name'] .'</h2>

                        <h6 class="text-center">' . $section["description"] . '</h6>

                        '. $this->draw_chart($section, $chart_data_labels, $chart_data_values) .'

                        '. ($html_category_winners ? $html_category_winners : $html_backup_winner) .'

                    </div>

                    ';

                }

            }



            $html_result .= '

            <div>

                <img id="poster_quiz" class="d-none" src="' . esc_html($quiz['poster_url']) . '"></img>

            </div>

            ';



            $end_doc = $this->write_end_doc();

            $html_result .= $end_doc;



            $recommend_quiz = $this->recommend_quiz_form($quiz_id);

            $html_result .= $recommend_quiz;



            return $html_result;

        }



        function write_end_doc() {

            $svg_width = '24px';

            

            // Recupera el ID del logo desde la personalización del tema

            $logo_id = get_theme_mod('custom_logo');

            

            // Si hay un logo configurado, obtenemos la URL de la imagen

            if ($logo_id) {

                $logo_url = wp_get_attachment_image_url($logo_id, 'full');

            } else {

                $logo_url = ''; // Si no hay logo configurado, dejamos la URL vacía

            }

            

            return '

            <div data-pdf="end-section" class="mt-5">

                <hr class="my-5 col-8 mx-auto">

                <h2 class="text-center mb-3" style="color: #467be9">¡Te espera un futuro brillante!</h2>

                <p>Gracias por confiar en Mentess para explorar tu camino profesional. Este informe combina tus motivaciones, estilos y atributos, y perfiles, reflejando lo que te apasiona, en qué eres realmente bueno/a y las características únicas de tu personalidad.<br><br>

                Ahora cuentas con una guía que no solo identifica tus áreas de interés, sino también tus fortalezas y cómo puedes aprovecharlas para prosperar en el campo que elijas. Tus motivaciones te inspiran, tus estilos y atributos te ayudan a destacar, y tus perfiles definen cómo puedes enfrentar desafíos y alcanzar tus metas.<br><br>

                Con esta claridad, estás preparado/a para enfocar tu energía en áreas que realmente resuenen contigo. Esto no solo te permitirá encontrar una carrera que te apasione, sino también marcar la diferencia con tu talento y habilidades únicas.<br><br>

                ¡El mundo necesita personas como tú!, Sigue adelante con confianza, sabiendo que tienes todo lo necesario para construir un futuro brillante y significativo.</p>

                <div data-pdf="signature" class="d-flex flex-column gap-2 mt-5 mb-5" style="inline-size: fit-content; justify-self: center;">

                    <div id="logo-row" class="d-flex justify-content-center">

                        <img width="150px" src="'. $logo_url .'" alt="Mentess Logo"></img>

                    </div>

                    <div id="instragram-row" class="d-flex justify-content-center gap-1">

                        <svg width="'. $svg_width .'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>

                        <span>@scholarshine</span>

                    </div>

                    <div id="email-row" class="d-flex justify-content-center gap-1">

                        <svg width="'. $svg_width .'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 112c-8.8 0-16 7.2-16 16l0 22.1L220.5 291.7c20.7 17 50.4 17 71.1 0L464 150.1l0-22.1c0-8.8-7.2-16-16-16L64 112zM48 212.2L48 384c0 8.8 7.2 16 16 16l384 0c8.8 0 16-7.2 16-16l0-171.8L322 328.8c-38.4 31.5-93.7 31.5-132 0L48 212.2zM0 128C0 92.7 28.7 64 64 64l384 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64L64 448c-35.3 0-64-28.7-64-64L0 128z"/></svg>

                        <span>hola@scholar-shine.com</span>

                    </div>

                    <div id="website-row" class="d-flex justify-content-center gap-1">

                        <svg width="'. $svg_width .'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M352 256c0 22.2-1.2 43.6-3.3 64l-185.3 0c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64l185.3 0c2.2 20.4 3.3 41.8 3.3 64zm28.8-64l123.1 0c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64l-123.1 0c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32l-116.7 0c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0l-176.6 0c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0L18.6 160C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192l123.1 0c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64L8.1 320C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6l176.6 0c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352l116.7 0zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6l116.7 0z"/></svg>

                        <span>www.scholar-shine.com</span>

                    </div>

                    <div id="mobile-row" class="d-flex justify-content-center gap-1">

                        <svg width="'. $svg_width .'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M16 64C16 28.7 44.7 0 80 0L304 0c35.3 0 64 28.7 64 64l0 384c0 35.3-28.7 64-64 64L80 512c-35.3 0-64-28.7-64-64L16 64zM224 448a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM304 64L80 64l0 320 224 0 0-320z"/></svg>

                        <span>+(507) 6316-4796</span>

                    </div>

                </div>

            </div>

            ';

        }

        

        function recommend_quiz_form($quiz_id) {

            $current_user = wp_get_current_user();

            $user_name = $current_user->first_name; // Nombre

            $user_lastname = $current_user->last_name; // Apellido



            return '

                <section class="share-section" style="background-color: #3879F1;border-radius: 20px;margin: 70px 20px;color: #FFF;text-align: center;padding: 25px;">

                    <h2 style="color: #fff">¿Te gustó el Test? ¡Compártelo!</h2>

                    <p style="color: #fff">Ayúdanos a que más personas descubran su vocación. Invita a un amigo, compañero o familiar a realizarlo.</p>

                    <form class="share-form" method="POST">

                        <label style="color: #fff" for="email">Ingresa el correo electrónico de tu conocido:</label>

                        <div style="display: flex;gap: 10px;justify-content: center;flex-flow: wrap;">

                            <input class="form-control" type="email" id="email" name="email" placeholder="ejemplo@correo.com" required="" style="margin: 10px 0px 10px;max-width: 250px;">

                            <input type="hidden" name="user_name" value="'. esc_attr($user_name) .'">

                            <input type="hidden" name="user_lastname" value="'. esc_attr($user_lastname) .'">

                            <input type="hidden" name="quiz_id" value="'. esc_attr($quiz_id) .'">

                            <button class="btn" type="submit" style="margin: 9px 0px;background-color: #FFE187;">Enviar</button>

                        </div>

                    </form>

                </section>';

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

                    cat.image_url,

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
            $display_legend = $chart_type == 'pie' || $chart_type == 'doughnut' || $chart_type == 'polarArea' ? true : false;
            
            $borderColor = ['#477BE9', '#FCE192', '#FF5A72', '#98BEFF', '#34A2B9', '#DF2D3D', '#D951B5', '#EF4136', '#FDB316', '#58CBEA', '#2AA9CA', '#264C93'];
            $opacity = "A0";
            # iterar los border colors y devolver un array con los colores con opacidad
            $backgroundColor = [];
            foreach ($borderColor as $color) {
                $backgroundColor[] = $color . $opacity;
            }


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
                <div class="col-12 col-lg-8 mx-auto mt-3" style="overflow: auto">
                    <div class="chart-container mx-auto" style="position: relative; height: 300px; width: 500px">
                        <canvas class="canvas-chart" id="chart-'. $section_id .'" data-pdf="canvas-chart"></canvas>
                    </div>
                </div>

                <script type="text/javascript">
                    let chart_data_labels'. $section_id .' = '. json_encode($chart_data_labels) .';
                    let chart_data_values'. $section_id .' = '. json_encode($chart_data_values) .';

                    let ctx'. $section_id .' = document.getElementById("chart-'. $section_id .'");
                    let chart'. $section_id .' = new Chart(ctx'. $section_id .', {
                        type: "'. $chart_type .'",
                        data: {
                            labels: chart_data_labels'. $section_id .',
                            datasets: [{
                                label: "'. $section_name .'",
                                data: chart_data_values'. $section_id .',
                                backgroundColor: '. json_encode($backgroundColor) .',
                                borderColor: '. json_encode($borderColor) .',
                                borderWidth: 1
                            }],
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: "top",
                                    display: '. json_encode($display_legend) .',
                                },
                                // title: {
                                //     display: true,
                                //     text: "'. $section_name .'"
                                // }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        display: true, // Asegura que los labels del eje Y se muestren
                                        autoSkip: false, // Evita que Chart.js oculte algunos labels autom�ticamente
                                        font: {
                                            size: 11 // Ajusta el tama�o de la fuente si es necesario
                                        },
                                        
                                    }
                                }
                            },
                            elements: {
                                bar: {
                                    barThickness: 65, // Aumenta la altura de las barras
                                }
                            },
                            indexAxis: "y",
                            responsive: true,
                            maintainAspectRatio: false,
                            '. $min_param .'
                        }
                    });
                </script>
            ';

            /*
                                        plugins: [{
                                afterDraw: function(chart) {
                                    let image = new Image();
                                    image.crossOrigin = "Anonymous"; // Si aplica para tu caso
                                    image.src = ctx'. $section_id .'.toDataURL("image/png");
                                    document.getElementById("chart-image-'. $section_id .'").src = image.src;
                                    
                                }
                            }]
            */

            return $html;        }    }

?>