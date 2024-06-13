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

function lg_activate_plugin() {
    global $wpdb;

    // Crear Tabla Encuestas
    $sql_lg_quizzes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_quizzes (
        `quiz_id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(45) NOT NULL,
        `shortcode` VARCHAR(45) NOT NULL,
        `is_active` BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (`quiz_id`)
    );";
    $wpdb->query($sql_lg_quizzes);

    // Crear Tabla Secciones
    $sql_lg_sections = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_sections (
        `section_id` INT NOT NULL AUTO_INCREMENT,
        `quiz_id` INT NOT NULL,
        `name` VARCHAR(45) NOT NULL,
        `order` INT NOT NULL,
        `responses_type` ENUM('Texto', 'Valor') NOT NULL,
        `high_score` INT,
        `low_score` INT,
        PRIMARY KEY (`section_id`),
        FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE CASCADE
    );";
    $wpdb->query($sql_lg_sections);

    // Crear Tabla Categorías
    $sql_lg_categories = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_categories (
        `category_id` INT NOT NULL AUTO_INCREMENT,
        `section_id` INT NOT NULL,
        `name` VARCHAR(45) NOT NULL,
        `title_result` VARCHAR(100) NOT NULL,
        `subtitle_result` VARCHAR(100) NOT NULL,
        `text_result` TEXT NOT NULL,
        PRIMARY KEY (`category_id`),
        FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE CASCADE,
    );";
    $wpdb->query($sql_lg_categories);

    // Crear Tabla Preguntas
    $sql_lg_questions = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_questions (
        `question_id` INT NOT NULL AUTO_INCREMENT,
        `section_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        `question` VARCHAR(255) NOT NULL,
        `order` INT NOT NULL,
        PRIMARY KEY (`question_id`),
        FOREIGN KEY (`section_id`) REFERENCES {$wpdb->prefix}lg_sections(`section_id`) ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES {$wpdb->prefix}lg_categories(`category_id`) ON DELETE CASCADE
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
        FOREIGN KEY (`user_id`) REFERENCES {$wpdb->prefix}users(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`quiz_id`) REFERENCES {$wpdb->prefix}lg_quizzes(`quiz_id`) ON DELETE CASCADE
    );";
    $wpdb->query($sql_lg_user_quiz);

    // Crear Tabla Respuestas de Usuario
    $sql_lg_user_responses = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lg_user_responses (
        `response_id` INT NOT NULL AUTO_INCREMENT,
        `user_quiz_id` BIGINT(20) UNSIGNED NOT NULL,
        `question_id` INT NOT NULL,
        `answer` ENUM('sí', 'tal vez', 'no') NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`response_id`),
        FOREIGN KEY (`user_quiz_id`) REFERENCES {$wpdb->prefix}users(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`question_id`) REFERENCES {$wpdb->prefix}lg_user_quiz(`user_quiz_id`) ON DELETE CASCADE
    );";
    $wpdb->query($sql_lg_user_responses);
    
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
        'mentess', // Parent slug (same as menu slug of the main menu)
        'Agregar encuesta', // Page title
        'Agregar encuesta', // Submenu title
        'manage_options', // Capability
        'post_quiz', // Submenu slug
        'post_quiz_page' // Function to display the submenu page content
    );

    // Listar secciones
    add_submenu_page(
        'mentess', // Parent slug (same as menu slug of the main menu)
        'Secciones de la encuesta', // Page title
        'Secciones', // Submenu title
        'manage_options', // Capability
        'section_list', // Submenu slug
        'section_list_page' // Function to display the submenu page content
    );

    // Agregar secciones
    add_submenu_page(
        'mentess', // Parent slug (same as menu slug of the main menu)
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

// Function to display submenu page content
function post_section_page() {
    include plugin_dir_path(__FILE__).'admin/section/post_section.php';
}

// Función para mostrar el contenido de la página de preguntas
function questions_page() {
    include plugin_dir_path(__FILE__).'admin/questions/questions.php';
}

add_action('admin_menu', 'lg_create_admin_menu');


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