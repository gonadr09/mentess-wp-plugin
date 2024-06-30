<?php
    global $wpdb;
    $message = '';

    // Eliminar sección
    if (isset($_POST['delete-category-submit'])) {
        $delete_category_id = intval($_POST['delete-category-id']);
        $result = $wpdb->delete("{$wpdb->prefix}lg_categories", array('category_id' => $delete_category_id), array('%d'));
        
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
    $category_list = $wpdb->get_results("
        SELECT c.category_id, c.name AS category_name, c.title_result, c.subtitle_result, c.text_result, s.section_id, s.name AS section_name
        FROM {$wpdb->prefix}lg_categories c
        LEFT JOIN {$wpdb->prefix}lg_sections s ON c.section_id = s.section_id
    ", ARRAY_A);

    if(empty($category_list)) {
        $category_list = array();
    }
?>

<div class="wrap">
    <?php
        echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a href="admin.php?page=post_category" class="page-title-action">Añadir nueva</a>
    <hr class="wp-header-end">
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox">
                    <label for="cb-select-all-1">
                        <span class="screen-reader-text">Seleccionar todo</span>
                    </label>
                </td>
                <th scope="col" id="section" class="manage-column column-primary" abbr="Sección">Categoría</th>
                <th scope="col" id="order" class="manage-column" abbr="Orden">Sección</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($category_list as $key => $value) {
                    $category_id = $value['category_id'];
                    $category_name = $value['category_name'];
                    $section_name = $value['section_name'];
                    echo "
                        <tr id='quiz-$category_id' class=''>
                            <th scope='row' class='check-column'>
                                <input id='cb-select-1' type='checkbox' name='quiz-$category_id' value='1'>
                                <label for='cb-select-1'>
                                    <span class='screen-reader-text'>$category_name</span>
                                </label>
                            </th>
                            <td class='column-primary has-row-actions' data-colname='name'>
                                <strong><a href='admin.php?page=post_category&id=$category_id' class='row-title'>$category_name</a></strong>
                                <div class='row-actions'>
                                    <span class='edit'><a href='admin.php?page=post_category&id=$category_id' aria-label='Editar'>Editar</a></span>
                                </div>
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Sección:'>$section_name</td>
                            <td data-colname='Acciones:'>
                                <div style='display: flex; gap: 10px'>
                                    <form method='post' name='delete-question-form' id='delete-question-form' class='validate' novalidate='novalidate'>
                                        <input name='delete-category-id' type='hidden' value='$category_id'>
                                        <input type='hidden' name='delete-category-submit' value='Eliminar'>
                                        <button type='button' data-delete-category class='button delete-button'>Eliminar</button>
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
        const deleteCategoryButtons = document.querySelectorAll('[data-delete-category]');
        deleteCategoryButtons.forEach(deleteCategoryButton => {
            deleteCategoryButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm("¿Está seguro que desea eliminar la categoría?\nEsta acción puede impactar en las encuestas ya realizadas");
                if (response) {
                    const form = deleteCategoryButton.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>