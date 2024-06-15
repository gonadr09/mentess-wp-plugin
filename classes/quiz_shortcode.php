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
            $table = "{$wpdb->prefix}lg_questions";
            $query = $wpdb->prepare("SELECT * FROM $table WHERE section_id = %d ORDER BY `order`", $section_id);
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

        function from_input($question_id, $question, $order, $response_type){
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
                    $question_id = $value['question_id'];
                    $question = $value['question'];
                    $order = $value['order'];
                    $response_type = $value['response_type'];
                    
                    $html_questions .= $this->from_input($question_id, $question, $order, $response_type);
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