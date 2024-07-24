<?php
global $wpdb;
$message = '';
$debug_messages = [];

/* CARGAR DATOS DE PRUEBA */
if (isset($_POST['create_data'])) {

    $type_response_text_id = '';
    $type_response_number_id = '';
    $type_response_study_level_id = '';
    $type_response_why_id = '';
    $type_response_radio_id = '';

    // Definir tipos de respuesta de prueba
    $response_types = [
        [
            'name' => "Texto",
            'response_type' => 'text',
        ],
        [
            'name' => "Número",
            'response_type' => 'number',
        ],
        [
            'name' => "Nivel de estudio",
            'response_type' => 'select',
            'options' => [
                [
                    'response_text' => "Escolar",
                    'response_value' => 0,
                ],
                [
                    'response_text' => "Universitario",
                    'response_value' => 0,
                ],
                [
                    'response_text' => "Otro",
                    'response_value' => 0,
                ]
            ],
        ],
        [
            'name' => "¿Por qué estas tomando este test?",
            'response_type' => 'select',
            'options' => [
                [
                    'response_text' => "No sé a que me quiero dedicar profesionalmente",
                    'response_value' => 0,
                ],
                [
                    'response_text' => "Quiero confirmar la carrera que me gusta",
                    'response_value' => 0,
                ],
                [
                    'response_text' => "Estoy cambiando de carrera profesional porque no me gusta la actual",
                    'response_value' => 0,
                ],
                [
                    'response_text' => "Otro",
                    'response_value' => 0,
                ]
            ],
        ],
        [
            'name' => "Sí, tal vez, no",
            'response_type' => 'radio',
            'options' => [
                [
                    'response_text' => "Sí",
                    'response_value' => 2,
                ],
                [
                    'response_text' => "Tal vez",
                    'response_value' => 1,
                ],
                [
                    'response_text' => "No",
                    'response_value' => 0,
                ]
            ],
        ]
    ];

    foreach ($response_types as $response_type_data) {
        $name = $response_type_data['name'];
        $response_type = $response_type_data['response_type'];
        $options = isset($response_type_data['options']) ? $response_type_data['options'] : null;
        unset($response_type_data['options']); // Remover 'options' antes de insertar en la tabla 'lg_responses_type'

        // Verificar si el tipo de respuesta ya existe
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}lg_responses_type WHERE name = %s AND response_type = %s", $name, $response_type);
        $existing_response_type = $wpdb->get_row($query);

        if (!$existing_response_type) {
            // Insertar el tipo de respuesta si no existe
            $response_type_format = ['%s', '%s'];
            $wpdb->insert("{$wpdb->prefix}lg_responses_type", $response_type_data, $response_type_format);
            $response_type_id = $wpdb->insert_id;

            if ($options) {
                foreach ($options as $response_option_data) {
                    $response_option_data['response_type_id'] = $response_type_id; // Añadir el response_type_id a cada opción de respuesta
                    $response_option_format = ['%s', '%d', '%d'];
                    $wpdb->insert("{$wpdb->prefix}lg_response_options", $response_option_data, $response_option_format);
                }
            }
        } else {
            // Si el tipo de respuesta ya existe, obtener su ID
            $response_type_id = $existing_response_type->response_type_id;
        }

        // Asignar los IDs a las variables correspondientes
        if ($name == 'Texto') {
            $type_response_text_id = $response_type_id;
        } elseif ($name == 'Número') {
            $type_response_number_id = $response_type_id;
        } elseif ($name == 'Nivel de estudio') {
            $type_response_study_level_id = $response_type_id;
        } elseif ($name == '¿Por qué estas tomando este test?') {
            $type_response_why_id = $response_type_id;
        } elseif ($name == 'Sí, tal vez, no') {
            $type_response_radio_id = $response_type_id;
        }
    }

    // Insertar nueva encuesta
    $quiz_data = [
        'name' => "Mentess Prueba",
        'is_active' => 0,
    ];
    $quiz_format = ['%s', '%d'];
    $wpdb->insert("{$wpdb->prefix}lg_quizzes", $quiz_data, $quiz_format);
    $quiz_id = $wpdb->insert_id;

    // Definir secciones de prueba
    $sections = [
        [
            'name' => 'Introducción',
            'description' => 'Información personal',
            'order' => 1,
            'high_score' => 0,
            'low_score' => 0,
            'chart_type' => 'bar',
            'categories' => [
                [
                    'name' => 'Introducción',
                    'title_result' => '',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => 'Nombre y apellido',
                            'order' => 1,
                            'response_type_id' => $type_response_text_id
                        ],
                        [
                            'question' => 'Edad',
                            'order' => 2,
                            'response_type_id' => $type_response_number_id
                        ],
                        [
                            'question' => 'Nivel de estudio',
                            'order' => 3,
                            'response_type_id' => $type_response_study_level_id
                        ],
                        [
                            'question' => 'Coloca el nombre de tu escuela o universidad',
                            'order' => 4,
                            'response_type_id' => $type_response_text_id
                        ],
                        [
                            'question' => '¿A qué te gustaría dedicarte profesionalmente en el futuro?',
                            'order' => 5,
                            'response_type_id' => $type_response_text_id
                        ],
                        [
                            'question' => '¿Por qué estas tomando este test?',
                            'order' => 6,
                            'response_type_id' => $type_response_why_id
                        ],
                    ]
                ]
            ]
        ],

        # ---- Sección 2 -----

        [
            'name' => 'Motivaciones',
            'description' => 'Hemos identificado las áreas en las que te sientes más entusiasmada/o y motivada/o para desarrollarte. Entender tus motivaciones es clave para elegir un camino profesional que no solo te interese, sino que también te permita desarrollarte y crecer personal y profesionalmente.',
            'order' => 2,
            'chart_type' => 'bar',
            'high_score' => 8,
            'low_score' => 4,
            'categories' => [
                [
                    'name' => 'Tecnología y desarrollo de softwares',
                    'title_result' => 'Tecnología y desarrollo de softwares',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te sientes atraído por el campo de la robótica y la automatización, y cómo estas tecnologías pueden aplicarse en contextos científicos y de investigación?',
                            'order' => 1,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar el potencial de la inteligencia artificial y el aprendizaje automático para optimizar procesos y descubrir patrones en datos científicos?',
                            'order' => 57,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => 'Te gusta trabajar con tus manos y te sientes cómodo utilizando herramientas y equipos para construir o reparar dispositivos tecnológicos?',
                            'order' => 38,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría participar en el desarrollo de software y aplicaciones que faciliten la recopilación, análisis y visualización de datos científicos?',
                            'order' => 76,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa la seguridad de la información en el mundo digital y te interesa proteger los sistemas informáticos de amenazas y ataques cibernéticos?',
                            'order' => 19,
                            'response_type_id' => $type_response_radio_id
                        ]
                    ],
                ],
                [
                    'name' => 'Creatividad y medios digitales ',
                    'title_result' => 'Creatividad y medios digitales ',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te atrae la idea de utilizar herramientas digitales para expresar tu creatividad y generar contenido multimedia innovador?',
                            'order' => 2,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te emociona la posibilidad de trabajar en proyectos que integren arte, diseño y tecnología para crear experiencias visuales y narrativas cautivadoras?',
                            'order' => 58,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona con habilidades de comunicación visual y narrativa efectivas para transmitir mensajes a través de medios digitales?',
                            'order' => 39,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa el impacto social y cultural de las tecnologías digitales y te interesa utilizarlas para promover la diversidad y la inclusión?',
                            'order' => 77,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de analizar las tendencias del mercado digital y comprender cómo las personas interactúan con las tecnologías digitales?',
                            'order' => 20,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Videojuegos y Entretenimiento Interactivo',
                    'title_result' => 'Videojuegos y Entretenimiento Interactivo',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te entusiasma la idea de contribuir al desarrollo de videojuegos y experiencias interactivas mediante la creación de arte digital, diseño de personajes o entornos virtuales?',
                            'order' => 3,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Estás interesado en explorar las posibilidades creativas de la realidad virtual (VR) y la realidad aumentada (AR) en el diseño de juegos y aplicaciones interactivas?',
                            'order' => 59,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te emociona la idea de trabajar diseñando y produciendo contenido audiovisual para videojuegos, incluyendo cinemáticas, trailers y efectos visuales?',
                            'order' => 40,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de desarrollar videojuegos, programar, realizar animaciones, que podrían aplicarse en proyectos creativos?',
                            'order' => 78,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa el impacto social y cultural de los videojuegos y te interesa utilizarlos para promover valores positivos y experiencias enriquecedoras?',
                            'order' => 21,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Salud y bienestar',
                    'title_result' => 'Salud y bienestar',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona la idea de mejorar la salud y el bienestar de las personas a través de diferentes intervenciones y estrategias?',
                            'order' => 4,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te entusiasma contribuir al avance del conocimiento científico en el área de la salud y el bienestar mediante la investigación y el desarrollo de nuevas tecnologías?',
                            'order' => 60,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa trabajar en una amplia gama de procedimientos quirúrgicos que abarcan diferentes partes del cuerpo?',
                            'order' => 41,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta gratificante la idea de trabajar directamente con pacientes, brindando apoyo emocional y asistencia práctica para mejorar su bienestar en diferentes realidades sociales y culturales?',
                            'order' => 79,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de aprender sobre el funcionamiento del cuerpo humano y las diferentes condiciones de salud?',
                            'order' => 22,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Energías renovables y sostenibles',
                    'title_result' => 'Energías renovables y sostenibles',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te gustaría contribuir a la lucha contra el cambio climático y promover un futuro sostenible a través del uso de energías renovables?',
                            'order' => 42.,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes motivado/a por la posibilidad de desarrollar políticas y estrategias que promuevan el uso de energías renovables y la sostenibilidad ambiental a nivel local o global?',
                            'order' => 61,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta atractivo el campo de la energía sostenible desde una perspectiva empresarial, ya sea emprendiendo proyectos propios o trabajando en empresas que lideran la transición hacia un futuro más sostenible?',
                            'order' => 46,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te llama la atención trabajar con tus manos y solucionar problemas prácticos relacionados con la instalación y mantenimiento de sistemas de energía renovable, como paneles solares o turbinas eólicas?',
                            'order' => 80.,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te atrae la idea de investigar nuevas tecnologías y métodos para aprovechar fuentes de energía renovable, como la solar, eólica o hidroeléctrica?',
                            'order' => 23,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Impacto social sin fines de lucro',
                    'title_result' => 'Impacto social sin fines de lucro',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te sientes motivado/a por la posibilidad de trabajar en organizaciones sin fines de lucro dedicadas a abordar problemas sociales, como la pobreza, la falta de vivienda o la educación desigual?',
                            'order' => 5,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes comprometido/a con la defensa de los derechos humanos y la justicia social, y te gustaría contribuir a iniciativas que promuevan la igualdad y la equidad?',
                            'order' => 62,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te entusiasma trabajar en áreas como la educación, la salud, la protección del medio ambiente o el desarrollo comunitario para generar impacto social?',
                            'order' => 43,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te apasiona trabajar para generar un cambio positivo en el mundo y mejorar la vida de las personas? (',
                            'order' => 81,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría relacionarte con comunidades desfavorecidas o grupos vulnerables para entender sus necesidades y diseñar programas y servicios que sean para su beneficio?',
                            'order' => 24,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Emprendimientos y startups',
                    'title_result' => 'Emprendimientos y startups',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona la idea de crear tu propio negocio o trabajar en un entorno dinámico y cambiante ?',
                            'order' => 6,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te entusiasma la idea de convencer a clientes potenciales e inversionistas sobre el valor de tu producto o servicio?',
                            'order' => 63,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa desarrollar habilidades de comunicación efectiva, negociación y marketing para promover tu negocio?',
                            'order' => 44.,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de utilizar herramientas tecnológicas y software de oficina para gestionar tareas administrativas y financieras?',
                            'order' => 82,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => 'te gusta planificarte, solucionar problemas problemas y gestionar tu tiempo para cumplir con deadlines y objetivos específicos?',
                            'order' => 25,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Ecommerce y retail',
                    'title_result' => 'Ecommerce y retail',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te llama la atención el mundo del comercio electrónico y te interesa trabajar en el desarrollo de tiendas online o plataformas de venta digital?',
                            'order' => 7,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta interesante la perspectiva de trabajar en un entorno, donde puedas desempeñar funciones clave como la gestión de inventario, la atención al cliente y la coordinación de estrategias de marketing digital?',
                            'order' => 64,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría trabajar de manera organizada, metódica y con atención al detalle para garantizar la eficiencia de las operaciones en una tienda online ?',
                            'order' => 45,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa la seguridad y protección de datos en el entorno del comercio electrónico y te interesa desarrollar soluciones tecnológicas para prevenir fraudes y proteger la información de los clientes?',
                            'order' => 83,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te llama la atención la idea de analizar datos de ventas, tráfico web y comportamiento del consumidor para optimizar la experiencia de compra en plataformas ecommerce?',
                            'order' => 26,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Educacion - e learning',
                    'title_result' => 'Educacion - e learning',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona la idea de contribuir a la educación de las personas y mejorar sus vidas a través de la enseñanza y el aprendizaje?',
                            'order' => 8,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de trabajar con herramientas y plataformas digitales para crear contenido educativo interactivo y atractivo, que motive a los estudiantes a aprender y participar activamente en su educación?',
                            'order' => 84,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona con habilidades de comunicación efectivas, empatía y paciencia para trabajar con estudiantes de diferentes edades y niveles educativos?',
                            'order' => 47,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa la inclusión educativa y te interesa trabajar con estudiantes con necesidades educativas especiales o en contextos de vulnerabilidad social?',
                            'order' => 94,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar cómo la tecnología puede ser utilizada para personalizar el aprendizaje y adaptarse a las necesidades individuales de los estudiantes, promoviendo un enfoque inclusivo y equitativo?',
                            'order' => 27,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Marketing y publicidad',
                    'title_result' => 'Marketing y publicidad',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te resulta sorprendente el mundo del marketing y la publicidad, y te interesa desarrollar estrategias creativas para comunicar mensajes a diferentes audiencias?',
                            'order' => 9,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te llama la atención la idea de analizar las tendencias del mercado, comprender el comportamiento del consumidor y crear campañas publicitarias atractivas?',
                            'order' => 65,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te entusiasma la idea de utilizar herramientas digitales, redes sociales y plataformas online para desarrollar, implementar y medir el impacto de las campañas de marketing?',
                            'order' => 48,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar nuevas formas de comunicación y publicidad en el entorno digital, como el marketing de influencers, la realidad aumentada (AR) y la inteligencia artificial (IA)?',
                            'order' => 85,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta interesante la perspectiva de trabajar en un entorno creativo, donde puedas colaborar con diseñadores gráficos y otros profesionales para desarrollar campañas publicitarias impactantes y memorables?',
                            'order' => 28,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Moda apparel',
                    'title_result' => 'Moda apparel',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona la idea de trabajar en la creación y diseño de prendas de moda, experimentando con materiales, texturas y formas para desarrollar colecciones innovadoras y vanguardistas?',
                            'order' => 10,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes atraído/a por la posibilidad de investigar y analizar tendencias de moda y comportamiento del consumidor, utilizando datos y análisis para anticipar las demandas del mercado y diseñar estrategias de marketing efectivas?',
                            'order' => 75,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta interesante la perspectiva de trabajar en la producción y gestión de eventos de moda, desde desfiles de pasarela hasta ferias comerciales, para promover marcas y crear experiencias memorables para los consumidores?',
                            'order' => 66,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de trabajar de manera manual y precisa para confeccionar prendas y accesorios con diferentes técnicas de costura o creación de estilos únicos en el cabello?',
                            'order' => 86,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona creativa, innovadora y con buenas habilidades de comunicación para expresar ideas y conectar con las personas a través de la moda?',
                            'order' => 29,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Turismo - hospitalidad',
                    'title_result' => 'Turismo - hospitalidad',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te encanta el mundo del turismo, la atención al cliente y la creación de experiencias memorables para los viajeros?',
                            'order' => 11,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de conocer nuevas culturas, interactuar con personas de diferentes países y brindar un servicio de calidad en el ámbito turístico?',
                            'order' => 67,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes motivado/a por la posibilidad de trabajar en la promoción y comercialización de destinos turísticos, utilizando estrategias persuasivas y creativas para atraer turistas y aumentar el flujo de visitantes?',
                            'order' => 49,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => 'Te interesa explorar nuevas tecnologías y plataformas digitales para mejorar la experiencia del viajero, como aplicaciones móviles, realidad virtual o sistemas de reserva en línea, desde una perspectiva técnica o empresarial?',
                            'order' => 87.,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa investigar y analizar las tendencias emergentes en la industria del turismo, como el turismo sostenible, el turismo de salud y bienestar, o el turismo de aventura, para desarrollar estrategias innovadoras y atractivas para los viajeros?',
                            'order' => 30,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],

                [
                    'name' => 'Alimentos y bebidas',
                    'title_result' => 'Alimentos y bebidas',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te gustaría trabajar en la creación y preparación de alimentos y bebidas, explorando nuevas recetas, técnicas de cocina y presentaciones innovadoras para satisfacer las demandas de los clientes?',
                            'order' => 12,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa aprender sobre los procesos de producción y control de calidad en la industria de alimentos y bebidas, asegurándote de que los productos cumplan con los estándares de seguridad y frescura exigidos por los consumidores?',
                            'order' => 68,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta interesante la perspectiva de emprender en el sector de alimentos y bebidas, ya sea creando tu propia marca de productos gourmet, estableciendo un restaurante temático o innovando en servicios de catering?',
                            'order' => 50,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría trabajar en la producción agrícola de manera responsable y sostenible, minimizando el impacto negativo en el medio ambiente?',
                            'order' => 88,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de conocer diferentes culturas a través de su gastronomía, experimentar nuevos sabores y aprender técnicas culinarias?',
                            'order' => 31,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Deportes fitness',
                    'title_result' => 'Deportes fitness',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te sientes motivado/a por la posibilidad de trabajar en la creación y diseño de programas de entrenamiento físico, utilizando conocimientos técnicos sobre ejercicios, nutrición y salud para ayudar a otros a alcanzar sus objetivos de bienestar?',
                            'order' => 13,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => 'Te interesa explorar las nuevas tendencias en la industria del fitness y los deportes, como el entrenamiento funcional, el mindfulness y la nutrición personalizada, desde una perspectiva técnica o empresarial?',
                            'order' => 69,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te atrae la idea de trabajar como entrenador personal o instructor de fitness, brindando apoyo y motivación a personas de todas las edades y niveles de condición física para que alcancen sus metas de salud y bienestar?',
                            'order' => 51,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te resulta interesante la perspectiva de emprender en el sector del fitness y los deportes, ya sea estableciendo tu propio estudio de entrenamiento, desarrollando una marca o creando una plataforma digital para conectar a entrenadores y clientes?',
                            'order' => 89,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa aprender sobre la gestión y administración de instalaciones deportivas, como gimnasios, centros de entrenamiento o clubes deportivos, asegurándote de que operen de manera eficiente y brinden una experiencia satisfactoria a los usuarios?',
                            'order' => 32,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'finanzas y fitch',
                    'title_result' => 'finanzas y fitch',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona el mundo de las finanzas, la gestión del dinero y el desarrollo de soluciones innovadoras para optimizar los servicios financieros?',
                            'order' => 14,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes motivado/a por la posibilidad de trabajar en el desarrollo y la implementación de estrategias de marketing y ventas en el sector financiero, utilizando técnicas para promover productos y servicios financieros innovadores?',
                            'order' => 70,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona con habilidades analíticas, numéricas y de comunicación para trabajar en la industria financiera?',
                            'order' => 52.,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta trabajar de manera organizada, metódica y con atención al detalle para realizar tareas como análisis financiero, gestión de inversiones o atención al cliente en el ámbito financiero? (',
                            'order' => 90,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría emprender en el sector de las finanzas, ya sea creando una startup tecnológica para servicios financieros, desarrollando una aplicación de gestión financiera o lanzando un blog sobre educación financiera y consejos de inversión?',
                            'order' => 33,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Legal, gobierno y servicios públicos',
                    'title_result' => 'Legal, gobierno y servicios públicos',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te consideras un defensor del mundo del derecho, la justicia y la contribución al bien común ?',
                            'order' => 15,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona con habilidades de comunicación oral y escrita, pensamiento crítico y capacidad para trabajar en equipo en la industria de servicios legales, gobierno y servicios públicos?',
                            'order' => 71,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de trabajar de manera organizada, metódica y con atención al detalle para realizar tareas como investigación legal, gestión de expedientes, atención al cliente o administración pública?',
                            'order' => 53,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa el derecho digital, la inteligencia artificial, el blockchain y el uso de tecnologías para mejorar la prestación de servicios legales y públicos?',
                            'order' => 91,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa el derecho digital, la inteligencia artificial, el blockchain y el uso de tecnologías para mejorar la prestación de servicios legales y públicos?',
                            'order' => 34,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Ingieneria e infraestructura',
                    'title_result' => 'Ingieneria e infraestructura',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona el mundo de la ingeniería, el diseño y la construcción de proyectos que mejoren la calidad de vida de las personas?',
                            'order' => 16,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de resolver problemas complejos, trabajar en equipo y crear soluciones innovadoras',
                            'order' => 72,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de trabajar de manera manual y precisa para realizar tareas como diseño de proyectos, construcción de obras, supervisión de equipos o mantenimiento de infraestructura?',
                            'order' => 54,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa la ingeniería sostenible, la construcción con materiales ecológicos, la eficiencia energética en la infraestructura y el uso de tecnologías limpias?',
                            'order' => 92,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de analizar el impacto ambiental de los proyectos de ingeniería, el desarrollo de soluciones sostenibles y la búsqueda de un futuro más verde en la industria de la infraestructura?',
                            'order' => 35,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Utilidades - minería y gas',
                    'title_result' => 'Utilidades - minería y gas',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te gusta la idea de trabajar en el campo de la minería y extracción de recursos como el gas, aplicando habilidades técnicas manuales para operar maquinaria pesada y garantizar la seguridad en el lugar de trabajo?',
                            'order' => 17,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar el uso de tecnologías avanzadas, como drones, sensores remotos y sistemas de información geográfica (GIS), para la exploración y el mapeo de yacimientos minerales?',
                            'order' => 73,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te atrae la idea de investigar nuevas técnicas y métodos de extracción de recursos, utilizando enfoques científicos e innovadores para maximizar la eficiencia y minimizar el impacto ambiental?',
                            'order' => 55,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa investigar y desarrollar tecnologías innovadoras para el tratamiento y purificación del agua, buscando soluciones más eficientes y sostenibles para asegurar el acceso a agua potable de calidad?',
                            'order' => 95,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te atrae la idea de explorar nuevas fuentes de energía renovable y alternativas para la generación de electricidad, como la energía hidroeléctrica, solar o eólica, desde una perspectiva científica e investigativa?',
                            'order' => 36,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Vias y transporte',
                    'title_result' => 'Vias y transporte',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona el mundo de los motores, la mecánica automotriz y el diseño de vehículos innovadores?',
                            'order' => 18,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te llama la atención explorar el uso de tecnologías de transporte inteligente, como sistemas de gestión del tráfico y vehículos autónomos, para mejorar la eficiencia y seguridad en las redes viales?',
                            'order' => 74,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría trabajar en la investigación y desarrollo de nuevas formas de transporte y movilidad, como el transporte público eléctrico, la infraestructura para vehículos eléctricos o sistemas de transporte de alta velocidad?',
                            'order' => 56,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de trabajar con tus manos, resolver problemas mecánicos y diagnosticar fallas en automóviles o motos?',
                            'order' => 93,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿ Te llama la atención convertirte en piloto de autos o motos de velocidad?',
                            'order' => 37,
                            'response_type_id' => $type_response_radio_id
                        ]
                    ]
                ]
            ]
        ],


        # --------------------
        # ---- Sección 3 -----
        # --------------------


        [
            'name' => 'Estilos y atributos',
            'description' => 'Cada uno de estos estilos y atributos te ayuda a entender mejor en qué eres realmente buena y qué te apasiona. Conocer tus súper habilidades según las industrias que te motivan te permite encontrar una carrera que no solo se alinie con tus intereses, sino que también te permita usar tus talentos al máximo. Es como tener un mapa que te guía hacia tu destino ideal, donde puedes brillar y hacer una diferencia.',
            'order' => 3,
            'chart_type' => 'polarArea',
            'high_score' => 12,
            'low_score' => 6,
            'categories' => [
                [
                    'name' => 'Tecnología y digitalización',
                    'title_result' => 'Tecnología y digitalización',
                    'subtitle_result' => '¡Eres un Prodigio Digital!',
                    'text_result' => 'Tus resultados revelan un talento increíble en tecnología y digitalización. Disfrutas resolviendo problemas complejos mediante el uso de tecnología y análisis de datos, descomponiendo desafíos en partes más pequeñas para encontrar soluciones eficientes. Te sientes a gusto utilizando software y herramientas digitales para analizar información y tomar decisiones inteligentes. Tu curiosidad por explorar tecnologías emergentes te hace destacar. Además, tienes un ojo agudo para analizar datos de mercado y tendencias, desarrollando estrategias de marketing que son efectivas y creativas e investigar nuevas metodologías de enseñanza y herramientas tecnológicas para mejorar la experiencia de aprendizaje en línea. En resumen, ¡estás preparado/a para liderar en la era digital y hacer grandes cosas con tus habilidades tecnológicas!',
                    'questions' => [
                        [
                            'question' => '¿Te sientes atraído/a por resolver problemas complejos utilizando tecnología y análisis de datos?',
                            'order' => 1,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría analizar datos de mercado y tendencias para desarrollar estrategias de marketing efectivas?',
                            'order' => 26,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de descomponer problemas en partes más pequeñas para encontrar soluciones eficientes?',
                            'order' => 31,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes cómodo/a utilizando software y herramientas digitales para analizar datos y tomar decisiones informadas?',
                            'order' => 21,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar nuevas tecnologías como la realidad virtual o la realidad aumentada en el contexto de los videojuegos y entretenimiento ?',
                            'order' => 16,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gustaría aprender sobre herramientas de análisis de datos para comprender el comportamiento del consumidor en entornos de comercio electrónico?',
                            'order' => 11,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa investigar nuevas metodologías de enseñanza y herramientas tecnológicas para mejorar la experiencia de aprendizaje en línea?',
                            'order' => 6,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ],
                ],
                [
                    'name' => 'Impacto Social y Sostenibilidad',
                    'title_result' => 'Impacto Social y Sostenibilidad',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te apasiona el cuidado de la salud y tienes interés en aprender sobre diferentes enfoques médicos y terapéuticos?',
                            'order' => 2,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te preocupa el medio ambiente y estás interesado en aprender sobre fuentes de energía limpia y renovable?',
                            'order' => 32,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te motiva trabajar en proyectos que generen un impacto positivo en la sociedad y en comunidades desfavorecidas?',
                            'order' => 27,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Tienes interés en aprender sobre la producción de alimentos y bebidas, desde la siembra y cosecha hasta la elaboración y distribución?',
                            'order' => 22,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te apasiona la idea de utilizar la ciencia y la tecnología para mejorar la forma en que cultivamos alimentos?',
                            'order' => 17,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta investigar sobre nuevas tecnologías para mejorar la eficiencia y la sostenibilidad en la generación y uso de energía?',
                            'order' => 12,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Tienes habilidades para trabajar en equipo y colaborar con profesionales de la salud en la atención de pacientes?',
                            'order' => 7,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ],
                ],
                [
                    'name' => 'Negocios y Finanzas',
                    'title_result' => 'Negocios y Finanzas',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Consideras que tienes habilidades para crear planes para negocios, incluyendo vender productos o servicios y qué les gusta a las personas?',
                            'order' => 3,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => 'Te gusta involucrarte con personas de diferentes grupos y defender lo que es justo para todos?',
                            'order' => 33,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Se te da bien atender a las personas y hacer que se sientan felices y a gusto en cualquier entorno?',
                            'order' => 28,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Tienes habilidades para identificar tendencias de moda y entender las preferencias del consumidor ?',
                            'order' => 23,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona ética y responsable, capaz de manejar información confidencial ?',
                            'order' => 18,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Estás pendiente del mercado financiero y comprendes los conceptos básicos de inversión, ahorro y gestión del riesgo?',
                            'order' => 13,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona con habilidades interpersonales y de comunicación para interactuar con personas de diversas culturas?',
                            'order' => 8,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ],
                ],
                [
                    'name' => 'Ingeniería y Manufactura',
                    'title_result' => 'Ingeniería y Manufactura',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Eres bueno/a identificando problemas y encontrando soluciones creativas en cualquier tipo de proyectos?',
                            'order' => 4,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te emociona la idea de buscar formas más limpias y eficientes de extraer y procesar recursos naturales?',
                            'order' => 34,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de explorar nuevas tecnologías que puedan hacer que los sistemas de transporte sean más modernos y amigables con el medio ambiente?',
                            'order' => 29,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te emociona la idea de utilizar tecnologías avanzadas, como la automatización y la robótica, para optimizar los procesos de fabricación?',
                            'order' => 24,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta la idea de participar en la construcción y desarrollo de edificios y estructuras que impacten en la comunidad?',
                            'order' => 19,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Valoras la seguridad en el lugar de trabajo y te preocupas por seguir protocolos y normativas para garantizarla?',
                            'order' => 14,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta aprender sobre cómo funcionan las máquinas y equipos utilizados en la construcción, minería y la extracción de gas?',
                            'order' => 9,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ],
                ],
                [
                    'name' => 'Creatividad y Entretenimiento',
                    'title_result' => 'Creatividad y Entretenimiento',
                    'subtitle_result' => '',
                    'text_result' => '',
                    'questions' => [
                        [
                            'question' => '¿Te emociona la idea de generar nuevas ideas y soluciones creativas para desafíos en diversas áreas como videojuegos y medios digitales',
                            'order' => 5,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona imaginativa y dispuesta a pensar fuera de la caja para encontrar soluciones originales?',
                            'order' => 35,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de experimentar con diferentes formas de expresión artística y visual para comunicar ideas y conceptos?',
                            'order' => 30,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta trabajar en entornos donde puedas colaborar con personas de diferentes disciplinas para crear proyectos innovadores?',
                            'order' => 25,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te interesa explorar cómo la creatividad puede impulsar el desarrollo en industrias como el diseño, el entretenimiento y la publicidad?',
                            'order' => 20,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Tienes habilidades en áreas como la actuación, el canto, la composición musical o la producción de eventos?',
                            'order' => 15,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas de expresar tu creatividad mediante el dibujo y la creación de diseños tanto de forma manual como utilizando software de diseño?',
                            'order' => 10,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ]
            ]
        ],


        # --------------------
        # ---- Sección 4 -----
        # --------------------


        [
            'name' => "Perfiles",
            'description' => "Vamos a descubrir tu propio superpoder de personalidad. Aquí identificamos los rasgos únicos que te hacen ser tú y cómo estos pueden guiarte hacia la carrera ideal. Vamos a desglosar cada uno de estos perfiles para que puedas entender mejor tus fortalezas y cómo pueden ayudarte en tu futuro profesional.",
            'order' => 4,
            'chart_type' => 'radar',
            'high_score' => 8,
            'low_score' => 4,
            'categories' => [
                [
                    'name' => 'Personalidad orientada a tareas (Perfil Metodológico):',
                    'title_result' => 'Personalidad orientada a tareas (Perfil Metodológico):',
                    'subtitle_result' => 'Metodológico',
                    'text_result' => '¡Eres un/a Maestro/a de la Organización y Eficiencia!',
                    'questions' => [
                        [
                            'question' => '¿Prefieres trabajar en un entorno estructurado y seguir un plan detallado para alcanzar tus objetivos?',
                            'order' => 1,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes satisfecho/a cuando completas una tarea en el tiempo previsto y según los estándares establecidos?',
                            'order' => 16,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Disfrutas dividiendo proyectos grandes en tareas más pequeñas y estableciendo plazos para cada una?',
                            'order' => 20,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Eres meticuloso/a en la organización de tu espacio de trabajo y en la gestión de tu tiempo?',
                            'order' => 11,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes más cómodo/a trabajando de manera independiente para completar tus responsabilidades?',
                            'order' => 6,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Personalidad orientada a las personas (Perfil Colaborativo):',
                    'title_result' => 'Personalidad orientada a las personas (Perfil Colaborativo):',
                    'subtitle_result' => 'Colaborativo',
                    'text_result' => '¡Eres un/a Maestro/a de la Colaboración y el Trabajo en Equipo!',
                    'questions' => [
                        [
                            'question' => '¿Te gusta trabajar en equipo y compartir ideas con tus compañeros para resolver problemas?',
                            'order' => 2,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Consideras importante escuchar activamente a los demás y tomar en cuenta sus opiniones en el trabajo en equipo?',
                            'order' => 21,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes motivado/a por el éxito colectivo y celebrar los logros del equipo más que los individuales?',
                            'order' => 17,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Eres hábil para comunicarte de manera clara y efectiva con diferentes personas en diversas situaciones?',
                            'order' => 12,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes cómodo/a asumiendo roles de liderazgo en equipos y ayudando a coordinar esfuerzos para alcanzar metas comunes?',
                            'order' => 7,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Personalidad analítica (Perfil Lógico):',
                    'title_result' => 'Personalidad analítica (Perfil Lógico):',
                    'subtitle_result' => 'Lógico',
                    'text_result' => '¡Eres un/a Maestro/a del Análisis y la Lógica!',
                    'questions' => [
                        [
                            'question' => '¿Disfrutas desglosando problemas complejos en pasos más pequeños y analizando cada componente por separado?',
                            'order' => 3,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Prefieres tomar decisiones basadas en datos y hechos verificables en lugar de en intuiciones o suposiciones?',
                            'order' => 25,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes atraído/a por resolver acertijos, problemas de lógica o rompecabezas para ejercitar tu mente?',
                            'order' => 22,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Eres meticuloso/a al analizar información y encontrar patrones o tendencias ocultas?',
                            'order' => 13,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te consideras una persona crítica que cuestiona las premisas y busca evidencia sólida antes de llegar a una conclusión?',
                            'order' => 8,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Personalidad creativa (Perfil Innovador):',
                    'title_result' => 'Personalidad creativa (Perfil Innovador):',
                    'subtitle_result' => 'Innovador',
                    'text_result' => '¡Eres un/a Explorador/a de la Creatividad y la Innovación!',
                    'questions' => [
                        [
                            'question' => '¿Te inspiras en diferentes formas de arte, música o literatura para encontrar soluciones a problemas cotidianos?',
                            'order' => 4,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Prefieres pensar fuera de lo convencional y proponer ideas originales aunque puedan parecer poco convencionales?',
                            'order' => 23,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes más cómodo/a en entornos que fomentan la experimentación y la libertad para expresarte?',
                            'order' => 18,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Eres creativo/a en la búsqueda de soluciones innovadoras para desafíos y problemas?',
                            'order' => 14,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta explorar nuevas ideas y perspectivas, incluso si implican abandonar las formas tradicionales de hacer las cosas?',
                            'order' => 9,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ],
                [
                    'name' => 'Personalidad emprendedora (Perfil Visionario):',
                    'title_result' => 'Personalidad emprendedora (Perfil Visionario):',
                    'subtitle_result' => 'Visionario',
                    'text_result' => '¡Eres un/a Maestro/a de la Innovación y el Emprendimiento!',
                    'questions' => [
                        [
                            'question' => '¿Eres valiente al asumir riesgos y te emociona la posibilidad de enfrentarte a desafíos difíciles?',
                            'order' => 5,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Prefieres buscar oportunidades nuevas y emocionantes en lugar de conformarte con lo establecido?',
                            'order' => 24,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te sientes más motivado/a por la posibilidad de crear algo nuevo y hacer un impacto significativo?',
                            'order' => 19,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Eres flexible y puedes adaptarte rápidamente a cambios inesperados o circunstancias imprevistas?',
                            'order' => 15,
                            'response_type_id' => $type_response_radio_id
                        ],
                        [
                            'question' => '¿Te gusta liderar y motivar a otros hacia una visión compartida, incluso cuando el camino hacia el éxito no está claro?',
                            'order' => 10,
                            'response_type_id' => $type_response_radio_id
                        ],
                    ]
                ]
            ]
        ]
    ];

    foreach ($sections as $section_data) {
        $categories = $section_data['categories'];
        unset($section_data['categories']);
        $section_data['quiz_id'] = $quiz_id; // Añadir el quiz_id a cada sección
        $section_format = ['%s', '%s', '%d', '%s', '%d', '%d', '%d'];
        $result = $wpdb->insert("{$wpdb->prefix}lg_sections", $section_data, $section_format);
        if ($result === false) {
            $debug_messages[] = 'Error al insertar sección "' . $section_data['name'] . '" | ERROR: ' . $wpdb->last_error;
            break;
        }
        $section_id = $wpdb->insert_id;
        $debug_messages[] = 'Sección "'. $section_data['name'] .'" insertada correctamente con ID: ' . $section_id;

        foreach ($categories as $category_data) {
            $questions = $category_data['questions'];
            unset($category_data['questions']);
            $category_data['section_id'] = $section_id; // Añadir el section_id a cada categoría
            $category_format = ['%s', '%s', '%s', '%s', '%d',];
            $result = $wpdb->insert("{$wpdb->prefix}lg_categories", $category_data, $category_format);
            if ($result === false) {
                $debug_messages[] = '- Error al insertar categoría "' . $category_data['name'] . '" | ERROR: ' . $wpdb->last_error;
                break 2;
            }
            $category_id = $wpdb->insert_id;
            $debug_messages[] = '- Categoría "'. $category_data['name'] .'" insertada correctamente con ID: ' . $category_id;

            foreach ($questions as $question_data) {
                $question_data['section_id'] = $section_id; // Añadir el section_id a cada pregunta
                $question_data['category_id'] = $category_id; // Añadir el category_id a cada pregunta
                $question_format = ['%s', '%d', '%d', '%d', '%d'];
                $result = $wpdb->insert("{$wpdb->prefix}lg_questions", $question_data, $question_format);
                if ($result === false) {
                    $debug_messages[] = 'Error al insertar pregunta:  ' . $question_data['question'] . ' | ERROR: ' . $wpdb->last_error;
                    break 3;
                }
                $question_id = $wpdb->insert_id;
            }
        }
    }

    // Mostrar mensajes de depuración en el frontend
    if (empty($debug_messages)) {
        $debug_messages[] = 'Datos de prueba creados exitosamente.';
        $message = '<div class="notice notice-success is-dismissible"><p>' . implode('<br>', $debug_messages) . '</p></div>';
    } else {
        $message = '<div class="notice notice-warning"><p>' . implode('<br>', $debug_messages) . '</p></div>';
    }
    
}

?>


<!-- HTML -->

<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <!-- <a href="admin.php?page=post_quiz" class="page-title-action">Añadir nuevo</a> -->
    <hr class="wp-header-end">
    <?php
    if (!empty($message)) {
        echo $message;
    }
    ?>

    <form method="post">
        <p>¿Desea crear una nueva encuesta con datos de prueba?</p>
        <button type="submit" name="create_data" class="button button-primary">Confirmar</button>
        <a href="admin.php?page=mentess" class="button">Volver</a>
    </form>

</div>