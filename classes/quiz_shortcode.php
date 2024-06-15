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

        public function get_questions($section_id) {
            global $wpdb;
            $query = $wpdb->prepare("
                SELECT 
                    q.question_id,
                    q.question,
                    q.order,
                    q.response_type_id,
                    rt.response_type,
                    ro.response_option_id,
                    ro.response_text,
                    ro.response_value
                FROM {$wpdb->prefix}lg_questions q
                LEFT JOIN {$wpdb->prefix}lg_responses_type rt ON q.response_type_id = rt.response_type_id
                LEFT JOIN {$wpdb->prefix}lg_response_options ro ON rt.response_type_id = ro.response_type_id
                WHERE q.section_id = %d
                ORDER BY q.order
            ", $section_id);
            $data = $wpdb->get_results($query,ARRAY_A);

            if(empty($data)){
                $data = array();
            }
            return $data;
        }

        public function form_open($quiz_title) {
            $html = "
                <br>
                <div class='wrap '>
                    <h1>$quiz_title</h1>
                    <br>
                    <form method='POST'>
            ";
            return $html;
        }

        public function form_close() {
            $html = "
                    <input type='submit' id='btn-submit' name='btn-submit' class='btn btn-primary page-title-action' value='Enviar'>
                </form>
            </div>  
            ";
            return $html;
        }

        function from_input($value){
            $question_id = $value['question_id'];
            $question = $value['question'];
            $order = $value['order'];
            $response_type_id = $value['response_type_id'];
            $response_type = $value['response_type'];
            $response_option_id = $value['response_option_id'];
            $response_text = $value['response_text'];
            $response_value = $value['response_value'];

            $html = "";
            if ($response_type == "select"){
                $html = "
                    <div class='form-group'>
                        <label for='$question_id' class='form-label'> $order) $question</label>
                        <select class='form-select' id='$question_id' name='$question_id' required>
                            <option value=''>-- Elige una opción --</option>
                            <option value='2'>Sí</option>
                            <option value='1'>Tal vez</option>
                            <option value='0'>No</option>
                        </select>
                    </div><br>
                ";
            } elseif ($response_text == 'number') {
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

        function quiz_html_builder($quiz_id){
            // Obtener la encuesta
            $quiz = $this->get_quiz($quiz_id);
            $quiz_name = $quiz['name'];

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
                $questions_list = $this->get_questions($section_id);
                foreach ($questions_list as $key => $value) {
                    $html_questions .= $this->from_input($value);
                }

                $html_sections .= $html_section_begin;
                $html_sections .= $html_questions;
                $html_sections .= $html_section_end;
            }

            $html = $this->form_open($quiz_name);
            $html .= $html_sections;
            $html .= $this->form_close();

            return $html;
        }

        function save_answer($data){
            global $wpdb;
            $table = "{$wpdb->prefix}encuestas_respuesta"; 
            return  $wpdb->insert($table,$data);
        }
    }
?>