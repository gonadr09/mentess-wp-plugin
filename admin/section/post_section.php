<?php
    global $wpdb;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $section_id = '';
    $quiz_id = '';
    $name = '';
    $order = '';
    $title = 'Agregar';

    $quiz_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_quizzes
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
    }

    if ($id > 0) {
        $query = $wpdb->prepare("
            SELECT s.section_id, s.name AS section_name, s.order, q.quiz_id, q.name AS quiz_name
            FROM {$wpdb->prefix}lg_sections s
            INNER JOIN {$wpdb->prefix}lg_quizzes q ON s.quiz_id = q.quiz_id
            WHERE s.section_id = %d
        ", $id);
        $quiz = $wpdb->get_row($query, ARRAY_A);
    
        if ($quiz) {
            $name = $quiz['section_name'];
            $quiz_id = $quiz['quiz_id'];
            $quiz_name = $quiz['quiz_name'];
            $order = $quiz['order'];
            $title = "Guardar";
        }
    }
?>

<div class='wrap'>
    <h1 id='quiz-title'><?php echo esc_attr($title); ?> sección</h1>

    <div id='ajax-response'></div>

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
                    <td><input class='regular-text' name='order' type='text' id='order' value='<?php echo esc_attr($order); ?>'></td>
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
            </tbody>
        </table>
        <p class='submit'><input type='submit' name='save_quiz' id='save_quiz' class='button button-primary' value='Guardar'></p>
    </form>
</div>