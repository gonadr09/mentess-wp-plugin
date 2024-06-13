<?php
    global $wpdb;

    // Eliminar sección
    if (isset($_POST['delete-section-submit'])) {
        $delete_section_id = intval($_POST['delete-section-id']);
        $wpdb->delete("{$wpdb->prefix}lg_sections", array('section_id' => $delete_section_id), array('%d'));
    }

    // Obtener resultados
    $quiz_list = $wpdb->get_results("
        SELECT s.section_id, s.name AS section_name, s.order, s.high_score, s.low_score, q.name AS quiz_name
        FROM {$wpdb->prefix}lg_sections s
        INNER JOIN {$wpdb->prefix}lg_quizzes q ON s.quiz_id = q.quiz_id
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
    }
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a href="admin.php?page=post_section" class="page-title-action">Añadir nuevo</a>
    <hr class="wp-header-end">
    <ul class="subsubsub">
        <li class="all"><a href="plugins.php?plugin_status=all" class="current" aria-current="page">Todos <span class="count">(2)</span></a> |</li>
        <li class="active"><a href="plugins.php?plugin_status=active">Activos <span class="count">(2)</span></a> |</li>
        <li class="auto-update-disabled"><a href="plugins.php?plugin_status=auto-update-disabled">Desactivados <span class="count">(2)</span></a></li>
    </ul>

 
    <form class="search-form search-plugins" method="get">
        <p class="search-box">
            <label class="screen-reader-text" for="plugin-search-input">Buscar</label>
            <input type="search" id="plugin-search-input" class="wp-filter-search" name="s" value="" placeholder="Buscar..." aria-describedby="live-search-desc">
            <input type="submit" id="search-submit" class="button hide-if-js" value="Search Installed Plugins">
        </p>
    </form>

    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox">
                    <label for="cb-select-all-1">
                        <span class="screen-reader-text">Seleccionar todo</span>
                    </label>
                </td>
                <th scope="col" id="section" class="manage-column column-primary" abbr="Sección">Sección</th>
                <th scope="col" id="order" class="manage-column" abbr="Orden">Orden</th>
                <th scope="col" id="quiz" class="manage-column" abbr="Encuesta">Encuesta</th>
                <th scope="col" id="scores" class="manage-column" abbr="Rango">Rango medio</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($quiz_list as $key => $value) {
                    $section_id = $value['section_id'];
                    $quiz_id = $value['quiz_name'];
                    $name = $value['section_name'];
                    $order = $value['order'];
                    $low_score = $value['low_score'];
                    $high_score = $value['high_score'];
                    echo "
                        <tr id='quiz-$section_id' class=''>
                            <th scope='row' class='check-column'>
                                <input id='cb-select-1' type='checkbox' name='quiz-$section_id' value='1'>
                                <label for='cb-select-1'>
                                    <span class='screen-reader-text'>$name</span>
                                </label>
                            </th>
                            <td class='column-primary has-row-actions' data-colname='name'>
                                <strong><a href='admin.php?page=post_section&id=$section_id' class='row-title'>$name</a></strong>
                                <div class='row-actions'>
                                    <span class='edit'><a href='admin.php?page=post_section&id=$section_id' aria-label='Editar'>Editar</a> | </span>
                                    <span class='trash'><a href='#' class='submitdelete' aria-label='Mover “$name ” a la papelera'>Eliminar</a> | </span>
                                    <span class='view'><a href='admin.php?page=questions&section-id=$section_id' rel='bookmark' aria-label='Preguntas'>Preguntas</a></span>
                                </div>
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Encuesta:'>$order</td>
                            <td class='' data-colname='Orden:'>$quiz_id</td>
                            <td class='' data-colname='Puntajes:'>$low_score - $high_score</td>
                            <td data-colname='Acciones:'>
                                <div style='display: flex; gap: 10px'>
                                    <a href='admin.php?page=questions&section-id=$section_id' class='button button-secondary' aria-label='Preguntas'>Preguntas</a>
                                    <form method='post' name='delete-question-form' id='delete-question-form' class='validate' novalidate='novalidate'>
                                        <input name='delete-section-id' type='hidden' value='$section_id'>
                                        <input type='hidden' name='delete-section-submit' value='Eliminar'>
                                        <button type='button' data-delete-section class='button delete-button'>Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    ";
                }
            ?>
        </tbody>
    </table>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteSectionButtons = document.querySelectorAll('[data-delete-section]');
        deleteSectionButtons.forEach(deleteSectionButton => {
            deleteSectionButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm("¿Está seguro que desea eliminar la sección?\nEsta acción puede impactar en las encuestas ya realizadas");
                if (response) {
                    const form = deleteSectionButton.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>