<?php
/*
 * Plugin Name:         Mentess Explorer
 * Plugin URI:          https://scholar-shine.com/
 * Description:         Test Psicométrico Psicológico de Orientación Vocacional creado por Scholar Shine
 * Versión:             1.0
 * Requires at least:   6.5.3
 * Requires PHP:        8.1.23
 * Author:              Scholar Shine
 * Author URI:          https://scholar-shine.com/
 * Text Domain:         scholar-shine
 * Domain Path:         /languages 
*/

// Requires
require_once dirname(__FILE__) . '/classes/quiz_shortcode.php';

// Incluir Dompdf
require_once plugin_dir_path(__FILE__) . '/dependencies/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

require_once plugin_dir_path(__FILE__) . '/dependencies/tcpdf/tcpdf.php';


// Función para generar PDF
function generar_pdf() {
    if (!isset($_GET['generate_pdf']) || !isset($_GET['user_quiz_id'])) {
        return;
    }

    wp_enqueue_style('mentess-quiz-form-css', plugin_dir_url(__FILE__) . 'admin/css/mentess-quiz-form.css');
    wp_enqueue_style('bootstrap.min', plugin_dir_url(__FILE__) . 'admin/css/bootstrap.min.css');

    $_quiz_shortcode_instance = new quiz_shortcode;
    $user_quiz_id = isset($_GET['user_quiz_id']) ? intval($_GET['user_quiz_id']) : null;

    if (!$user_quiz_id) {
        return;
    }

    $result = $_quiz_shortcode_instance->check_view_by_user_quiz_id($user_quiz_id);
    $user_quiz_id = $result['user_quiz_id'];
    $quiz_id = $result['quiz_id'];

    if (!$user_quiz_id || !$quiz_id) {
        return;
    }
    
    $html_content = $_quiz_shortcode_instance->show_results($result['quiz_id'], $result['user_quiz_id']);
    
    // dompdf
/*     $dompdf = new Dompdf();
    $dompdf->loadHtml($html_content);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('resultado.pdf', array('Attachment' => 0)); */

    // Crear instancia de TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // Establecer información del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Resultado del Quiz');
    $pdf->SetSubject('Resultado del Quiz');
    $pdf->SetKeywords('Quiz, Resultado, TCPDF, PHP');
    // Agregar una página
    $pdf->AddPage();
    // Escribir el contenido HTML en el documento PDF
    $pdf->writeHTML($html_content, true, false, true, false, '');
    // Nombre del archivo PDF para descarga
    $filename = 'resultado.pdf';
    // Enviar el PDF al navegador para descarga
    $pdf->Output($filename, 'D');
    // Agregar el botón para generar el PDF
    
    exit;
}

// Acción para manejar la generación de PDF
add_action('init', 'generar_pdf');

function lg_activate_plugin() {

    // Crear la página para mostrar resultados
    $results_page = array(
        'post_title'    => 'Resultados de la encuesta',
        'post_content'  => '[show_results]', // El shortcode para mostrar resultados
        'post_status'   => 'publish',
        'post_type'     => 'page',
    );

    // Verificar si la página ya existe
    $existing_page = get_posts([
        'title'        => 'Resultados de la encuesta',
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'numberposts'  => 1
    ]);

    // Insertar la página en la base de datos si no existe
    if (empty($existing_page)) {
        wp_insert_post($results_page);
    }

    // Definir tablas del plugin de cuestionario
    
    // Importar la función dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $error_messages = [];

    // Definir las tablas y ejecutar dbDelta() para cada una
    $tables = array(
        "lg_quizzes" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_quizzes (
            `quiz_id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(45) NOT NULL,
            `is_active` BOOLEAN DEFAULT FALSE,
            `wc_product_id` BIGINT(20) UNSIGNED,
            PRIMARY KEY (`quiz_id`),
            FOREIGN KEY (`wc_product_id`) REFERENCES {$wpdb->prefix}posts(`ID`) ON DELETE SET NULL
        ) $charset_collate;",
        "lg_sections" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_sections (
            `section_id` INT NOT NULL AUTO_INCREMENT,
            `quiz_id` INT NOT NULL,
            `name` VARCHAR(45) NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `order` INT NOT NULL,
            `high_score` INT,
            `low_score` INT,
            `chart_type` ENUM('bar', 'doughnut', 'pie', 'polarArea', 'radar') NOT NULL,
            PRIMARY KEY (`section_id`),
            FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE RESTRICT
        ) $charset_collate;",
        "lg_categories" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_categories (
            `category_id` INT NOT NULL AUTO_INCREMENT,
            `section_id` INT NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `title_result` VARCHAR(100),
            `subtitle_result` VARCHAR(100),
            `text_result` TEXT,
            `image_url` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`category_id`),
            FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE RESTRICT
        ) $charset_collate;",
        "lg_responses_type" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_responses_type (
            `response_type_id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(45) NOT NULL,
            `response_type` ENUM('text', 'number', 'select', 'radio') NOT NULL,
            PRIMARY KEY (`response_type_id`)
        ) $charset_collate;",
        "lg_response_options" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_response_options (
            `response_option_id` INT NOT NULL AUTO_INCREMENT,
            `response_type_id` INT NOT NULL,
            `response_text` VARCHAR(100) NOT NULL,
            `response_value` INT NOT NULL,
            PRIMARY KEY (`response_option_id`),
            FOREIGN KEY (`response_type_id`) REFERENCES {$wpdb->prefix}lg_responses_type(`response_type_id`) ON DELETE CASCADE
        ) $charset_collate;",
        "lg_questions" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_questions (
            `question_id` INT NOT NULL AUTO_INCREMENT,
            `section_id` INT NOT NULL,
            `category_id` INT NOT NULL,
            `question` VARCHAR(255) NOT NULL,
            `order` INT NOT NULL,
            `response_type_id` INT,
            PRIMARY KEY (`question_id`),
            FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE CASCADE,
            FOREIGN KEY (`category_id`) REFERENCES {$wpdb->prefix}lg_categories(`category_id`) ON DELETE CASCADE,
            FOREIGN KEY (`response_type_id`) REFERENCES {$wpdb->prefix}lg_responses_type(`response_type_id`) ON DELETE SET NULL
        ) $charset_collate;",
        "lg_user_quiz" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_user_quiz (
            `user_quiz_id` INT NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `quiz_id` INT NOT NULL,
            `is_complete` BOOLEAN DEFAULT FALSE,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_quiz_id`),
            FOREIGN KEY (`user_id`) REFERENCES {$wpdb->prefix}users(`ID`) ON DELETE RESTRICT,
            FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE RESTRICT
        ) $charset_collate;",
        "lg_user_responses" => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_user_responses (
            `response_id` INT NOT NULL AUTO_INCREMENT,
            `user_quiz_id` INT NOT NULL,
            `question_id` INT NOT NULL,
            `response_text` VARCHAR(100) NOT NULL,
            `response_value` INT,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`response_id`),
            FOREIGN KEY (`user_quiz_id`) REFERENCES {$wpdb->prefix}lg_user_quiz(`user_quiz_id`) ON DELETE CASCADE,
            FOREIGN KEY (`question_id`) REFERENCES {$wpdb->prefix}lg_questions(`question_id`) ON DELETE RESTRICT
        ) $charset_collate;"
    );

    // Ejecutar dbDelta() para cada tabla y capturar errores
    foreach ($tables as $table_name => $sql) {
        #dbDelta($sql);
        $wpdb->query($sql);
        if ($wpdb->last_error !== '') {
            $error_messages[] = "Error creando tabla {$table_name}: " . $wpdb->last_error;
        }
    }

    // Mostrar errores si los hay
    if (!empty($error_messages)) {
        $error_message_text = implode('<br>', $error_messages);
        wp_die('<div class="error"><p>Error al activar el plugin:</p><p>' . $error_message_text . '</p></div>');
    }
}


function lg_deactivate_plugin() {
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'lg_activate_plugin');
register_deactivation_hook(__FILE__, 'lg_deactivate_plugin');

function lg_create_admin_menu() {
    // Main menu page
    add_menu_page(
        'Mentess Explorer', // Page title
        'Mentess', // Menu title
        'manage_options', // Capability
        'mentess', // Menu slug
        'mentess_main_page', // Function to display the page content
        plugin_dir_url(__FILE__).'admin/img/plugin-icon-20x20.png', // Icon URL
        '1' // Position
    );

    // Agregar encuesta
    add_submenu_page(
        null, // Parent slug (same as menu slug of the main menu)
        'Agregar encuesta', // Page title
        'Agregar encuesta', // Submenu title
        'manage_options', // Capability
        'post_quiz', // Submenu slug
        'post_quiz_page' // Function to display the submenu page content
    );

    // Listar secciones
    add_submenu_page(
        'mentess', // Parent slug (same as menu slug of the main menu)
        'Secciones', // Page title
        'Secciones', // Submenu title
        'manage_options', // Capability
        'section_list', // Submenu slug
        'section_list_page' // Function to display the submenu page content
    );

    // Agregar secciones
    add_submenu_page(
        null, // Parent slug (same as menu slug of the main menu)
        'Agregar sección', // Page title
        'Agregar sección', // Submenu title
        'manage_options', // Capability
        'post_section', // Submenu slug
        'post_section_page' // Function to display the submenu page content
    );

    // Página de preguntas
    add_submenu_page(
        null, // No se muestra en el menú
        'Preguntas', // Page title
        'Preguntas', // Submenu title
        'manage_options', // Capability
        'questions', // Submenu slug
        'questions_page' // Function to display the submenu page content
    );

    // Listar categorias
    add_submenu_page(
        'mentess', // Parent slug (same as menu slug of the main menu)
        'Categorías', // Page title
        'Categorías', // Submenu title
        'manage_options', // Capability
        'category_list', // Submenu slug
        'category_list_page' // Function to display the submenu page content
    );

    // Agregar categoria
    add_submenu_page(
        null, // Parent slug (same as menu slug of the main menu)
        'Agregar categoría', // Page title
        'Agregar categoría', // Submenu title
        'manage_options', // Capability
        'post_category', // Submenu slug
        'post_category_page' // Function to display the submenu page content
    );

    // Listar tipos de respuesta
    add_submenu_page(
        'mentess', // Parent slug (same as menu slug of the main menu)
        'Tipos de respuesta', // Page title
        'Tipos de respuesta', // Submenu title
        'manage_options', // Capability
        'response_type_list', // Submenu slug
        'response_type_list_page' // Function to display the submenu page content
    );

    // Agregar tipos de respuesta
    add_submenu_page(
        null, // Parent slug (same as menu slug of the main menu)
        'Agregar tipo de respuesta', // Page title
        'Agregar tipo de respuesta', // Submenu title
        'manage_options', // Capability
        'post_response_type', // Submenu slug
        'post_response_type_page' // Function to display the submenu page content
    );

    // Página de opciones de respuestas
    add_submenu_page(
        null, // No se muestra en el menú
        'Opciones de respuesta', // Page title
        'Opciones de respuesta', // Submenu title
        'manage_options', // Capability
        'response_options', // Submenu slug
        'response_options_page' // Function to display the submenu page content
    );

    // Página encuestas respondidas
    add_submenu_page(
        'mentess', // No se muestra en el menú
        'Encuestas respondidas', // Page title
        'Encuestas respondidas', // Submenu title
        'manage_options', // Capability
        'user_quiz_list', // Submenu slug
        'user_quiz_list_page' // Function to display the submenu page content
    );

}

// Function to display main menu page content
function mentess_main_page() {
    include plugin_dir_path(__FILE__).'admin/quiz/list_quiz.php';
}

// Function to display submenu page content
function post_quiz_page() {
    include plugin_dir_path(__FILE__).'admin/quiz/post_quiz.php';
}

function section_list_page() {
    include plugin_dir_path(__FILE__).'admin/section/list_section.php';
}

function post_section_page() {
    include plugin_dir_path(__FILE__).'admin/section/post_section.php';
}

function category_list_page() {
    include plugin_dir_path(__FILE__).'admin/category/list_category.php';
}

function post_category_page() {
    include plugin_dir_path(__FILE__).'admin/category/post_category.php';
}

function response_type_list_page() {
    include plugin_dir_path(__FILE__).'admin/responses_type/list_responses_type.php';
}

function post_response_type_page() {
    include plugin_dir_path(__FILE__).'admin/responses_type/post_responses_type.php';
}

// Función para mostrar el contenido de la página de preguntas
function questions_page() {
    include plugin_dir_path(__FILE__).'admin/questions/questions.php';
}

function response_options_page() {
    include plugin_dir_path(__FILE__).'admin/response_options/response_options.php';
}

function user_quiz_list_page() {
    include plugin_dir_path(__FILE__).'admin/user_quiz/list_user_quiz.php';
}

add_action('admin_menu', 'lg_create_admin_menu');



// Shortcodes
function show_shortcode($atts){
    wp_enqueue_style('mentess-quiz-form-css', plugin_dir_url(__FILE__) . 'admin/css/mentess-quiz-form.css');
    wp_enqueue_style('bootstrap.min', plugin_dir_url(__FILE__) . 'admin/css/bootstrap.min.css');

    // Registrar y encolar html2canvas
    wp_register_script('html2canvas', plugin_dir_url(__FILE__) . 'admin/js/html2canvas.min.js', [], null, true);
    wp_enqueue_script('html2canvas');

    // Registrar y encolar jsPDF
    wp_register_script('jspdf', plugin_dir_url(__FILE__) . 'admin/js/jspdf.umd.min.js', ['html2canvas'], null, true);
    wp_enqueue_script('jspdf');

    // Registrar y encolar jsPDF-AutoTable
    wp_register_script('jspdf-autotable', plugin_dir_url(__FILE__) . 'admin/js/jspdf.plugin.autotable.js', ['jspdf'], null, true);
    wp_enqueue_script('jspdf-autotable');
    
    // Registrar y encolar dompurify
    wp_register_script('cell', plugin_dir_url(__FILE__) . 'admin/js/cell.js', ['jspdf'], null, true);
    wp_enqueue_script('cell');
    
    // Registrar y encolar tu script personalizado
    wp_register_script('my-plugin-pdf', plugin_dir_url(__FILE__) . 'admin/js/exportToPDF.js', ['cell', 'jspdf', 'html2canvas'], null, true);
    wp_enqueue_script('my-plugin-pdf');

    $_quiz_shortcode_instance = new quiz_shortcode;
    $id = intval($atts['id']); //obtener el id por parametro

    //Form POST
    if(isset($_POST['quiz-responses-submit'])){
        return $_quiz_shortcode_instance->save_form($_POST);
    }

    // Form GET
    $user_quiz_id = isset($_GET['user_quiz_id']) ? intval($_GET['user_quiz_id']) : null;
    if ($user_quiz_id) {
        $result = $_quiz_shortcode_instance->check_view_by_user_quiz_id($user_quiz_id);

        if ($result['quiz_id'] && $result['user_quiz_id']) {
            $html = $_quiz_shortcode_instance->show_results($result['quiz_id'], $result['user_quiz_id']);
        } else {
            $html = "<h6>Acceso restringido</h6><p>Usted no puede acceder a este resultado.</p>";
        }

    } else {
        $html = $_quiz_shortcode_instance->quiz_html_builder($id);
    }

    return $html;

}

add_shortcode("QUIZ","show_shortcode");

// Shortcode para mostrar resultados
function show_results_shortcode($atts) {
    wp_enqueue_style('mentess-quiz-form-css', plugin_dir_url(__FILE__) . 'admin/css/mentess-quiz-form.css');
    wp_enqueue_style('bootstrap.min', plugin_dir_url(__FILE__) . 'admin/css/bootstrap.min.css');

    // Registrar y encolar html2canvas
    wp_register_script('html2canvas', plugin_dir_url(__FILE__) . 'admin/js/html2canvas.min.js', [], null, true);
    wp_enqueue_script('html2canvas');

    // Registrar y encolar jsPDF
    wp_register_script('jspdf', plugin_dir_url(__FILE__) . 'admin/js/jspdf.umd.min.js', ['html2canvas'], null, true);
    wp_enqueue_script('jspdf');

    // Registrar y encolar jsPDF-AutoTable
    wp_register_script('jspdf-autotable', plugin_dir_url(__FILE__) . 'admin/js/jspdf.plugin.autotable.js', ['jspdf'], null, true);
    wp_enqueue_script('jspdf-autotable');

    // Registrar y encolar dompurify
    wp_register_script('cell', plugin_dir_url(__FILE__) . 'admin/js/cell.js', ['jspdf'], null, true);
    wp_enqueue_script('cell');

    // Registrar y encolar tu script personalizado
    wp_register_script('my-plugin-pdf', plugin_dir_url(__FILE__) . 'admin/js/exportToPDF.js', ['cell', 'jspdf', 'html2canvas'], null, true);
    wp_enqueue_script('my-plugin-pdf');

    $_quiz_shortcode_instance = new quiz_shortcode;
    $user_quiz_id = isset($_GET['user_quiz_id']) ? intval($_GET['user_quiz_id']) : null;

    if ($user_quiz_id) {
        $result = $_quiz_shortcode_instance->check_view_by_user_quiz_id($user_quiz_id);

        if ($result['quiz_id'] && $result['user_quiz_id']) {
            $html = $_quiz_shortcode_instance->show_results($result['quiz_id'], $result['user_quiz_id']);
        } else {
            $html = "<h6>Acceso restringido</h6><p>Usted no puede acceder a este resultado.</p>";
        }
    } else {
        $html = "<h6>Acceso restringido</h6><p>Usted no puede acceder a este resultado.</p>";
    }

    return $html;
}
add_shortcode('show_results', 'show_results_shortcode');


// Función para encolar el CSS del plugin
function lg_enqueue_css() {
    // Registrar el archivo CSS
    wp_register_style(
        'mentess-admin-css', // Handle del CSS
        plugin_dir_url(__FILE__) . 'admin/css/mentess-admin.css', // Ruta al archivo CSS
        array(), // Dependencias (puede ser un array vacío si no hay dependencias)
        '1.0.0', // Versión del CSS
        'all' // Media
    );
    
    // Encolar el archivo CSS
    wp_enqueue_style('mentess-admin-css');
}

// Hook para encolar el CSS en el área de administración
add_action('admin_enqueue_scripts', 'lg_enqueue_css');


/*

    // Crear Tabla Encuestas
    $sql_lg_quizzes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_quizzes (
        `quiz_id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(45) NOT NULL,
        `is_active` BOOLEAN DEFAULT FALSE,
        `wc_product_id` BIGINT(20) UNSIGNED
        PRIMARY KEY (`quiz_id`),
        FOREIGN KEY (`wc_product_id`) REFERENCES {$wpdb->prefix}posts(`ID`) ON DELETE SET NULL
    ) DEFAULT CHARSET=utf8;";
    $wpdb->query($sql_lg_quizzes);

    // Crear Tabla Secciones
    $sql_lg_sections = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_sections (
        `section_id` INT NOT NULL AUTO_INCREMENT,
        `quiz_id` INT NOT NULL,
        `name` VARCHAR(45) NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `order` INT NOT NULL,
        `high_score` INT,
        `low_score` INT,
        `chart_type` ENUM('bar', 'doughnut', 'pie', 'polarArea', 'radar') NOT NULL,
        PRIMARY KEY (`section_id`),
        FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE RESTRICT
    );";
    $wpdb->query($sql_lg_sections);

    // Crear Tabla Categorías
    $sql_lg_categories = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_categories (
        `category_id` INT NOT NULL AUTO_INCREMENT,
        `section_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `title_result` VARCHAR(100) NOT NULL,
        `subtitle_result` VARCHAR(100) NOT NULL,
        `text_result` TEXT NOT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`category_id`),
        FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE RESTRICT
    );";
    $wpdb->query($sql_lg_categories);

    // Crear Tabla Tipo de Respuestas
    $sql_lg_responses_type = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_responses_type (
        `response_type_id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(45) NOT NULL,
        `response_type` ENUM('text', 'number', 'select', 'radio') NOT NULL,
        PRIMARY KEY (`response_type_id`)
    );";
    $wpdb->query($sql_lg_responses_type);

    // Crear Tabla Opción de Respuestas
    $sql_lg_response_options = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_response_options (
        `response_option_id` INT NOT NULL AUTO_INCREMENT,
        `response_type_id` INT NOT NULL,
        `response_text` VARCHAR(100) NOT NULL,
        `response_value` INT NOT NULL,
        PRIMARY KEY (`response_option_id`),
        FOREIGN KEY (`response_type_id`) REFERENCES {$wpdb->prefix}lg_responses_type(`response_type_id`) ON DELETE CASCADE
    );";
    $wpdb->query($sql_lg_response_options);

    // Crear Tabla Preguntas
    $sql_lg_questions = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_questions (
        `question_id` INT NOT NULL AUTO_INCREMENT,
        `section_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        `question` VARCHAR(255) NOT NULL,
        `order` INT NOT NULL,
        `response_type_id` INT NOT NULL,
        PRIMARY KEY (`question_id`),
        FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES {$wpdb->prefix}lg_categories(`category_id`) ON DELETE CASCADE,
        FOREIGN KEY (`response_type_id`) REFERENCES {$wpdb->prefix}lg_responses_type(`response_type_id`) ON DELETE SET NULL
    );";
    $wpdb->query($sql_lg_questions);


    // Crear Tabla Encuesta del Usuario
    $sql_lg_user_quiz = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_user_quiz (
        `user_quiz_id` INT NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `quiz_id` INT NOT NULL,
        `is_complete` BOOLEAN DEFAULT FALSE,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_quiz_id`),
        FOREIGN KEY (`user_id`) REFERENCES {$wpdb->prefix}users(`ID`) ON DELETE RESTRICT,
        FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE RESTRICT
    );";
    $wpdb->query($sql_lg_user_quiz);

    // Crear Tabla Respuestas de Usuario
    $sql_lg_user_responses = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_user_responses (
        `response_id` INT NOT NULL AUTO_INCREMENT,
        `user_quiz_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        `response_text` VARCHAR(100) NOT NULL,
        `response_value` INT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`response_id`),
        FOREIGN KEY (`user_quiz_id`) REFERENCES {$wpdb->prefix}lg_user_quiz(`user_quiz_id`) ON DELETE CASCADE,
        FOREIGN KEY (`question_id`) REFERENCES {$wpdb->prefix}lg_questions(`question_id`) ON DELETE RESTRICT
    );";
    $wpdb->query($sql_lg_user_responses);
}

*/