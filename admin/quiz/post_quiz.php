<?php
    global $wpdb;

    // Peticiones POST
    if (isset($_POST['save_quiz'])) {
        $quiz_id_post = !empty($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
        $name_post = sanitize_text_field($_POST['name']);
        $is_active_post = isset($_POST['is_active']) ? 1 : 0;
        $wc_product_id_post = intval($_POST['wc_product_id']);
        $poster_url = !empty($_POST['poster_url']) ? sanitize_text_field($_POST['poster_url']) : null;
        $logo_url = !empty($_POST['logo_url']) ? sanitize_text_field($_POST['logo_url']) : null;
        //$shortcode_post = sanitize_text_field($_POST['shortcode']) ? $quiz_id_post : null;

        // Procesar la imagen subida
        if (!empty($_FILES['poster_image']['name'])) {
            if ($_FILES['poster_image']['error'] == 0) {
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['path'] . '/' . basename($_FILES['poster_image']['name']);
                
                if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $upload_path)) {
                    $poster_url = $upload_dir['url'] . '/' . basename($_FILES['poster_image']['name']);
                } else {
                    echo "Hubo un error al subir la imagen de portada.";
                }
            } else {
                echo "Hubo un error con la subida de la imagen de portada.";
            }
        }
        
        if (!empty($_FILES['logo_image']['name'])) {
            if ($_FILES['logo_image']['error'] == 0) {
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['path'] . '/' . basename($_FILES['logo_image']['name']);
                
                if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $upload_path)) {
                    $logo_url = $upload_dir['url'] . '/' . basename($_FILES['logo_image']['name']);
                } else {
                    echo "Hubo un error al subir la imagen del logo.";
                }
            } else {
                echo "Hubo un error con la subida de la imagen del logo.";
            }
        }

        //print_r($shortcode_post);

/* 
        if ($shortcode_post == null) {        
            $query = "SELECT quiz_id FROM {$wpdb->prefix}lg_quizzes ORDER BY quiz_id DESC limit 1";
            $result = $wpdb->get_results($query,ARRAY_A);
            $nextId = $result[0]['quiz_id'] + 1;
            $shortcode_post = "[QUIZ id='$nextId']";
        } else {
            $shortcode_post = "[QUIZ id='$shortcode_post']";
        } */

        $errors = false;

        $data_prepared = [
            'name' => $name_post,
            'wc_product_id' => $wc_product_id_post,
            'is_active' => $is_active_post,
            'poster_url' => $poster_url,
            'logo_url' => $logo_url
        ];

        $format = ['%s', '%d', '%d', '%s', '%s'];
        
        if ($quiz_id_post) {
            // Actualizar encuesta existente
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_quizzes",
                $data_prepared,
                ['quiz_id' => $quiz_id_post],
                $format,
                ['%d']
            );
        } else {
            // Insertar nueva encuesta
            $result = $wpdb->insert("{$wpdb->prefix}lg_quizzes", $data_prepared, $format);
        }

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar la encuesta:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Encuesta guardada correctamente.</strong></p>
            </div>
        ';
        }
    }

    // Peticiones GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $quiz_id = '';
    $name = '';
    $is_active = '';
    $checked = '';
    $wc_product_id = '';
    $post_title = '';
    $title = 'Agregar';
    $logo_url = '';
    $poster_url = '';

    $product_list = $wpdb->get_results("
        SELECT ID, post_title, post_type FROM {$wpdb->prefix}posts WHERE post_type = 'product'
    ", ARRAY_A);

    if(empty($quiz_list)) {
        $quiz_list = array();
    }

    if ($id > 0) {
        $query = $wpdb->prepare("
        SELECT q.quiz_id, q.name, q.is_active, q.wc_product_id, p.post_title, q.poster_url, q.logo_url
        FROM {$wpdb->prefix}lg_quizzes q
        LEFT JOIN {$wpdb->prefix}posts p ON q.wc_product_id = p.ID
        WHERE q.quiz_id = %d
        ", $id);

        $quiz = $wpdb->get_row($query, ARRAY_A);
    
        if ($quiz) {
            $quiz_id = $quiz['quiz_id'];
            $name = $quiz['name'];
            $is_active = $quiz['is_active'];
            $checked = $quiz['is_active'] ? "checked" : "";
            $wc_product_id = $quiz['wc_product_id'];
            $post_title = $quiz['post_title'];
            $title = "Editar";
            $poster_url = $quiz['poster_url'];
            $logo_url = $quiz['logo_url'];
        }
    }
?>

<div class='wrap'>
    <h1 id='quiz-title'><?php echo esc_attr($title); ?> encuesta</h1>
    <hr class="wp-header-end"><br>
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <form method='post' name='quiz-post' id='quiz-post' class='validate' enctype="multipart/form-data">
        <input name='action' type='hidden' value='quiz-post'>
        <?php if ($id > 0) : ?>
            <input name='quiz_id' type='hidden' value='<?php echo esc_attr($id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' required value='<?php echo esc_attr($name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <?php
                    if ($quiz_id)
                        echo "
                        <tr class='form-required'>
                            <th scope='row'><label for='shortcode'>Shortcode</label></th>
                            <td><p>[QUIZ id='$quiz_id']</p></td>
                        </tr>
                    ";
                ?>
                <tr class='form-field'>
                    <th scope='row'><label for='wc_product_id'>Producto</label></th>
                    <td>
                        <select name="wc_product_id" id="wc_product_id" required>
                            <option selected disabled value=''>-- Elige una opción --</option>
                            <?php
                                foreach ($product_list as $key => $value) {
                                    $option_product_id = $value['ID'];
                                    $option_product_name = $value['post_title'];
                                    $selected = $option_product_id == $wc_product_id ? 'selected' : '';
                                    echo "
                                        <option value='$option_product_id' $selected>(ID: $option_product_id) $option_product_name</option>
                                    ";                                
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                <th scope='row'><label for='image'>Portada</label></th>
                    <td>
                        <img width="100px" src='<?php echo esc_attr($poster_url); ?>'><br>
                        <input type='file' name='poster_image' id='poster_image' accept='image/*'>
                        <input type='hidden' name='poster_url' value='<?php echo esc_attr($poster_url); ?>'>
                    </td>
                </tr>
                <th scope='row'><label for='image'>Logo</label></th>
                    <td>
                        <img width="100px" src='<?php echo esc_attr($logo_url); ?>'><br>
                        <input type='file' name='logo_image' id='logo_image' accept='image/*'>
                        <input type='hidden' name='logo_url' value='<?php echo esc_attr($logo_url); ?>'>
                    </td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='is_active'>Activo</label></th>
                    <td>
                        <input type='checkbox' name='is_active' id='is_active' <?php echo esc_attr($checked); ?>>
                        <label for='is_active'></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'>
            <input type='submit' name='save_quiz' id='save_quiz' class='button button-primary' value='<?php echo ($id > 0) ? "Actualizar encuesta" : "Agregar encuesta"; ?>'>
            <a href="admin.php?page=mentess" class="button button-secondary">Volver</a>
        </p>
    </form>
</div>