<?php
    global $wpdb;

    $quiz_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_quizzes
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
    }
?>

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a href="admin.php?page=post_quiz" class="page-title-action">Añadir nuevo</a>
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
                <th scope="col" id="name" class="manage-column column-primary" abbr="name">Título</th>
                <th scope="col" id="shortcode" class="manage-column" abbr="shortcode">Shortcode</th>
                <th scope="col" id="is_active" class="manage-column" abbr="is_active">Activo</th>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
                foreach ($quiz_list as $key => $value) {
                    $id = $value['quiz_id'];
                    $name = $value['name'];
                    $shortcode = $value['shortcode'];
                    $is_active = $value['is_active'];
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
                                    <span class='edit'><a href='admin.php?page=post_quiz&id=$id' aria-label='Editar'>Editar</a> | </span>
                                    <span class='trash'><a href='#' class='submitdelete' aria-label='Mover “$name ” a la papelera'>Eliminar</a> | </span>
                                    <span class='view'><a href='#' rel='bookmark' aria-label='Ver “$name ”'>Ver</a></span>
                                </div>
                                <button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button>
                            </td>
                            <td class='' data-colname='Shortcode:'>$shortcode</td>
                            <td class='' data-colname='Activo:'>";

                    if ($is_active > 0) {
                        echo "<span class='dashicons dashicons-yes-alt'></span></td></tr>";
                    } else {
                        echo "<span class='dashicons dashicons-no'></span></td></tr>";
                    }
                }
            ?>
        </tbody>
    </table>
</div>