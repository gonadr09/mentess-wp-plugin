<?php
    global $wpdb;

    // Peticiones POST
    if (isset($_POST['save-category'])) {
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $section_id = intval($_POST['section']);
        $name = sanitize_text_field($_POST['name']);
        $image_url = !empty($_POST['image_url']) ? sanitize_text_field($_POST['image_url']) : null;
        $title_result = sanitize_text_field($_POST['title_result']);
        $subtitle_result = sanitize_text_field($_POST['subtitle_result']);
        $text_result = sanitize_text_field($_POST['text_result']);

        // Procesar la imagen subida
        if (!empty($_FILES['image']['name'])) {
            // Comprueba si hay algún error en la subida
            if ($_FILES['image']['error'] == 0) {
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['path'] . '/' . basename($_FILES['image']['name']);
                
                // Mover el archivo subido al directorio de uploads
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = $upload_dir['url'] . '/' . basename($_FILES['image']['name']);
                } else {
                    echo "Hubo un error al subir la imagen.";
                }
            } else {
                echo "Hubo un error con la subida de la imagen.";
            }
        }

        $errors = false;

        $data_prepared = [
            'section_id' => $section_id,
            'name' => $name,
            'image_url' => $image_url,
            'title_result' => $title_result,
            'subtitle_result' => $subtitle_result,
            'text_result' => $text_result,
        ];

        $format = ['%d', '%s', '%s', '%s', '%s', '%s'];
        
        if ($category_id) {
            // Actualizar sección existente
            $result = $wpdb->update(
                "{$wpdb->prefix}lg_categories",
                $data_prepared,
                ['category_id' => $category_id],
                $format,
                ['%d']
            );
        } else {
            // Insertar nueva sección
            $result = $wpdb->insert("{$wpdb->prefix}lg_categories", $data_prepared, $format);
            $category_id = $wpdb->insert_id; // Obtener el ID de la nueva categoría
        }

        if ($result === false) {
            $error_messages[] = $wpdb->last_error;
            $error_message_text = implode('<br>', $error_messages);
            $message = '
                <div id="message" class="notice error">
                    <p><strong>Hubo un error al guardar la categoría:</strong></p>
                    <p>' . $error_message_text . '</p>
                </div>
            ';
        } else {
            $message = '
            <div id="message" class="notice updated">
                <p><strong>Categoría guardada correctamente.</strong> <a href="admin.php?page=category_list">Haz clic aquí para ver el listado</a></p>
            </div>
        ';
        }
    }

    // Peticiones GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $category_id = '';
    $section_id = '';
    $category_name = '';
    $title_result = '';
    $subtitle_result = '';
    $text_result = '';
    $section_name = '';
    $image_url = '';
    $title = 'Agregar';

    $section_list = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}lg_sections ORDER BY `order`
    ", ARRAY_A);

    if(empty($section_list)) {
        $quiz_list = array();
    }

    if ($id > 0) {
        $query = $wpdb->prepare("
            SELECT c.category_id, c.name AS category_name, c.image_url, c.title_result, c.subtitle_result, c.text_result, s.section_id, s.name AS section_name
            FROM {$wpdb->prefix}lg_categories c
            LEFT JOIN {$wpdb->prefix}lg_sections s ON c.section_id = s.section_id
            WHERE c.category_id = %d
        ", $id);
        $section = $wpdb->get_row($query, ARRAY_A);
    
        if ($section) {
            $category_id = $section['category_id'];
            $category_name = $section['category_name'];
            $image_url = $section['image_url'];
            $title_result = $section['title_result'];
            $subtitle_result = $section['subtitle_result'];
            $text_result = $section['text_result'];
            $section_id = $section['section_id'];
            $section_name = $section['section_name'];
            $title = "Editar";
        }
    }
?>

<div class='wrap'>
    <!-- Categoría -->
    <h1 id='category-title'> <?php echo esc_attr($title); ?> categoría</h1>
    <hr class="wp-header-end"><br>
    <?php 
        if (!empty($message)) {
            echo $message;
        }
    ?>
    <form method='post' name='category-post' id='category-post' class='validate' enctype="multipart/form-data">
        <input name='action' type='hidden' value='category-post'>
        <?php if ($id > 0) : ?>
            <input name='category_id' type='hidden' value='<?php echo esc_attr($id); ?>'>
        <?php endif; ?>
        <table class='form-table' role='presentation'>
            <tbody>
                <tr class='form-required'>
                    <th scope='row'><label for='name'>Nombre</label></th>
                    <td><input class='regular-text' name='name' type='text' id='name' required value='<?php echo esc_attr($category_name); ?>' aria-required='true' autocapitalize='none' autocorrect='off' autocomplete='off' maxlength='60'></td>
                </tr>
                <tr>
                    <th scope='row'><label for='image'>Imagen</label></th>
                    <td>
                        <img width="100px" src='<?php echo esc_attr($image_url); ?>'><br>
                        <input type='file' name='image' id='image' accept='image/*'>
                        <input type='hidden' name='image_url' value='<?php echo esc_attr($image_url); ?>'>
                    </td>
                </tr>
                <tr class='form-field'>
                    <th scope='row'><label for='section'>Sección</label></th>
                    <td>
                        <select name="section" id="section" required>
                            <option selected disabled value=''>-- Elige una opción --</option>
                            <?php
                                
                                foreach ($section_list as $key => $value) {
                                    $option_section_id = $value['section_id'];
                                    $option_section_name = $value['name'];
                                    $selected = $option_section_id == $section_id ? 'selected' : '';
                                    echo "
                                        <option value='$option_section_id' $selected>$option_section_name</option>
                                    ";                                
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='title_result'>Título del resultado</label></th>
                    <td><input class='regular-text' name='title_result' type='text' id='title_result' value='<?php echo esc_attr($title_result); ?>'></td>
                </tr>

                <tr class='form-required'>
                    <th scope='row'><label for='subtitle_result'>Subtítulo del resultado</label></th>
                    <td><input class='regular-text' name='subtitle_result' type='text' id='subtitle_result' value='<?php echo esc_attr($subtitle_result); ?>'></td>
                </tr>
                <tr class='form-required'>
                    <th scope='row'><label for='text_result'>Texto del resultado</label></th>
                    <td>
                        <textarea class='large-text' name='text_result' type='text' id='text_result' rows='8' value='<?php echo esc_attr($text_result); ?>'><?php echo esc_attr($text_result); ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class='submit'>
            <input type='submit' name='save-category' id='save-category' class='button button-primary' value='Guardar'>
            <a href="admin.php?page=category_list" class="button button-secondary">Volver</a>
        </p>
    </form>
</div>