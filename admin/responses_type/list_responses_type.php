<?php
    global $wpdb;

    // Eliminar Tipo de respuesta
    if (isset($_POST['delete-item-submit'])) {
        $delete_item_id = intval($_POST['delete-item-id']);
        $wpdb->delete("{$wpdb->prefix}lg_responses_type", array('response_type_id' => $delete_item_id), array('%d'));
    }

    // Obtener resultados
    $responses_type_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_responses_type
    ", ARRAY_A);

    if(empty($responses_type_list)) {
        $responses_type_list = array();
    }
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a href="admin.php?page=post_response_type" class="page-title-action">Añadir nuevo</a>
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
                <th scope="col" id="name" class="manage-column column-primary" abbr="Nombre">Nombre</th>
                <th scope="col" id="response_type" class="manage-column" abbr="Tipo de respuesta">Tipo de respuesta</th>
                <th scope="col" id="actions" class="manage-column" abbr="Acciones">Acciones</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($responses_type_list as $key => $value) {
                    $response_type_id = $value['response_type_id'];
                    $name = $value['name'];
                    $response_type = $value['response_type'];
                    echo "
                        <tr id='response-type-$response_type_id' class=''>
                            <th scope='row' class='check-column'>
                                <input id='cb-select-1' type='checkbox' name='response-type-$response_type_id' value='1'>
                                <label for='cb-select-1'>
                                    <span class='screen-reader-text'>$name</span>
                                </label>
                            </th>
                            <td class='column-primary has-row-actions' data-colname='name'>
                                <strong><a href='admin.php?page=post_response_type&id=$response_type_id' class='row-title'>$name</a></strong>
                                <div class='row-actions'>
                                    <span class='edit'><a href='admin.php?page=post_response_type&id=$response_type_id' aria-label='Editar'>Editar</a> | </span>
                                    <span class='trash'><a href='#' class='submitdelete' aria-label='Mover “$name ” a la papelera'>Eliminar</a> | </span>
                                </div>
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Tipo de respuesta:'>$response_type</td>
                            <td data-colname='Acciones:'>
                                <div style='display: flex; gap: 10px'>";
                                if ($response_type == 'select') {
                                    echo "<a href='admin.php?page=response_options&response-type-id=$response_type_id' class='button button-secondary' aria-label='Opciones'>Opciones</a>";
                                }
                                echo "
                                    <form method='post' name='delete-form' id='delete-form' class='validate' novalidate='novalidate'>
                                        <input name='delete-item-id' type='hidden' value='$response_type_id'>
                                        <input type='hidden' name='delete-item-submit' value='Eliminar'>
                                        <button type='button' data-delete-item class='button delete-button'>Eliminar</button>
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
        const deleteButtons = document.querySelectorAll('[data-delete-item]');
        deleteButtons.forEach(deleteButton => {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const response = confirm("¿Está seguro que desea eliminar el tipo de respuesta?\nEsta acción puede impactar en las encuestas ya realizadas");
                if (response) {
                    const form = deleteButton.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>