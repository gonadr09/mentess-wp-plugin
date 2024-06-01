<?php
    global $wpdb;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $name = '';
    $shortcode = '';
    $is_active = '';
    $cheked = '';
    $title = 'Agregar';

    if ($id > 0) {
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}lg_quizzes WHERE `quiz_id` = %d", $id);
        $quiz = $wpdb->get_row($query, ARRAY_A);
    
        if ($quiz) {
            $name = $quiz['name'];
            $shortcode = $quiz['shortcode'];
            $is_active = $quiz['is_active'];
            $cheked = $quiz['is_active'] ? "checked" : "";
            $title = "Editar";
        }
    }
?>

<div class='wrap'>
    <h1 id='quiz-title'><?php echo esc_attr($title); ?> encuesta</h1>

    <div id='ajax-response'></div>

    <form method='post' name='quiz-post' id='quiz-post' class='validate' novalidate='novalidate'>
        <input name='action' type='hidden' value='quiz-post'>
        <?php if ($id > 0) : ?>
            <input name='quiz_id' type='hidden' value='<?php echo esc_attr($id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' value='<?php echo esc_attr($name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='shortcode'>Shortcode</label></th>
                    <td><input class='regular-text' name='shortcode' type='text' id='shortcode' value='<?php echo esc_attr($shortcode); ?>'></td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='is_active'>Activo</label></th>
                    <td>
                        <input type='checkbox' name='is_active' id='is_active' value='<?php echo esc_attr($is_active); ?>' <?php echo esc_attr($cheked); ?>>
                        <label for='is_active'></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'><input type='submit' name='save_quiz' id='save_quiz' class='button button-primary' value='<?php echo ($id > 0) ? "Actualizar encuesta" : "Agregar encuesta"; ?>'></p>
    </form>
</div>