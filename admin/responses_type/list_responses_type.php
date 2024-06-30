<?php
    global $wpdb;
    $message = '';

    // Eliminar Tipo de respuesta
    if (isset($_POST['delete-item-submit'])) {
        $delete_item_id = intval($_POST['delete-item-id']);
        $result = $wpdb->delete("{$wpdb->prefix}lg_responses_type", array('response_type_id' => $delete_item_id), array('%d'));

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
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Tipo de respuesta:'>$response_type</td>
                            <td data-colname='Acciones:'>
                                <div style='display: flex; gap: 10px'>";
                                if ($response_type == 'select' or $response_type == 'radio') {
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