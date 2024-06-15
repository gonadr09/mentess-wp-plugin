<?php
    global $wpdb;

    // Eliminar sección
    if (isset($_POST['delete-category-submit'])) {
        $delete_category_id = intval($_POST['delete-category-id']);
        $wpdb->delete("{$wpdb->prefix}lg_categories", array('category_id' => $delete_category_id), array('%d'));
    }

    // Obtener resultados
    $category_list = $wpdb->get_results("
        SELECT c.category_id, c.name AS category_name, c.title_result, c.subtitle_result, c.text_result, s.section_id, s.name AS section_name
        FROM {$wpdb->prefix}lg_categories c
        INNER JOIN {$wpdb->prefix}lg_sections s ON c.section_id = s.section_id
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
                                <strong><a href='admin.php?page=post_section&id=$category_id' class='row-title'>$category_name</a></strong>
                                <div class='row-actions'>
                                    <span class='edit'><a href='admin.php?page=post_section&id=$category_id' aria-label='Editar'>Editar</a> | </span>
                                    <span class='trash'><a href='#' class='submitdelete' aria-label='Mover “$category_name ” a la papelera'>Eliminar</a> | </span>
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