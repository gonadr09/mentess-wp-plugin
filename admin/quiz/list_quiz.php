<?php
    global $wpdb;
    $message = '';

    // Eliminar encuesta
    if (isset($_POST['delete-quiz-submit'])) {
        $delete_quiz_id = intval($_POST['delete-quiz-id']);
        $result = $wpdb->delete("{$wpdb->prefix}lg_quizzes", array('quiz_id' => $delete_quiz_id), array('%d'));

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al eliminar:</strong></p>
                    <p>Verifica que un usuario no haya respondido una encuesta relacionada con este item.</p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Eliminado correctamente.</strong></p>
            </div>
        ';
        }
    }

    // Obtener resultados
    $quiz_list = $wpdb->get_results("
        SELECT q.quiz_id, q.name, q.is_active, q.wc_product_id, p.post_title
        FROM {$wpdb->prefix}lg_quizzes q
        LEFT JOIN {$wpdb->prefix}posts p ON q.wc_product_id = p.ID
    ", ARRAY_A);
/*     $quiz_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_quizzes
    ", ARRAY_A); */

    if(empty($quiz_list)) {
        $quiz_list = array();
    }
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a href="admin.php?page=post_quiz" class="page-title-action">Añadir nuevo</a>
    <a href="admin.php?page=create_quiz_example" class="page-title-action">Datos de prueba</a>
    <hr class="wp-header-end">
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <!-- 
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
     -->
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox">
                    <label for="cb-select-all-1">
                        <span class="screen-reader-text">Seleccionar todo</span>
                    </label>
                </td>
                <th scope="col" id="name" class="manage-column column-primary" abbr="name">Título</th>
                <th scope="col" id="shortcode" class="manage-column" abbr="shortcode">Shortcode</th>
                <th scope="col" id="product" class="manage-column" abbr="product">Producto</th>
                <th scope="col" id="is_active" class="manage-column" abbr="is_active">Activo</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($quiz_list as $key => $value) {
                    $id = $value['quiz_id'];
                    $name = $value['name'];
                    $is_active = $value['is_active'];
                    $wc_product_id = $value['wc_product_id'];
                    $post_title = $value['post_title'];
                    echo "
                        <tr id='quiz-$id' class=''>
                            <th scope='row' class='check-column'>
                                <input id='cb-select-1' type='checkbox' name='quiz-$id' value='1'>
                                <label for='cb-select-1'>
                                    <span class='screen-reader-text'>$name</span>
                                </label>
                            </th>
                            <td class='column-primary has-row-actions' data-colname='name'>
                                <strong><a href='admin.php?page=post_quiz&id=$id' class='row-title'>$name</a></strong>
                                <div class='row-actions'>
                                    <span class='edit'><a href='admin.php?page=post_quiz&id=$id' aria-label='Editar'>Editar</a></span>
                                </div>
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Shortcode:'>[QUIZ id='$id']</td>
                            <td class='' data-colname='Producto:'>($wc_product_id) $post_title</td>
                            <td class='' data-colname='Activo:'>";

                    if ($is_active > 0) {
                        echo "<span class='dashicons dashicons-yes-alt' style='color: green'></span></td>";
                    } else {
                        echo "<span class='dashicons dashicons-dismiss' style='color: #b32d2e'></span></td>";
                    }
                    echo "
                        <td>
                            <div style='display: flex; gap: 10px'>
                                <form method='post' name='delete-question-form' id='delete-question-form' class='validate' novalidate='novalidate'>
                                    <input name='delete-quiz-id' type='hidden' value='$id'>
                                    <input type='hidden' name='delete-quiz-submit' value='Eliminar'>
                                    <button type='button' data-delete-quiz class='button delete-button'>Eliminar</button>
                                </form>
                            </div>
                        </td>
                    ";
                }
            ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteSectionButtons = document.querySelectorAll('[data-delete-quiz]');
        deleteSectionButtons.forEach(deleteSectionButton => {
            deleteSectionButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm("¿Está seguro que desea eliminar la encuesta?\nEsta acción borrará las secciones, las preguntas y respuestas vinculadas a ella");
                if (response) {
                    const form = deleteSectionButton.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>