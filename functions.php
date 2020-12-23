<?php

add_action('wp_enqueue_scripts', 'theme_styles'); // подключаем стили
add_action('wp_enqueue_scripts', 'theme_scripts'); // подключаем скрипты
add_action('after_setup_theme', 'theme_register_nav_menu'); // подключаем меню
add_action('widgets_init', 'register_my_widgets' ); // подключаем виджеты

add_action('wp_ajax_send_mail', 'send_mail');
add_action('wp_ajax_nopriv_send_mail', 'send_mail');


//form через ajax
function send_mail() {
  $contactName = $_POST['contactName'];
  $contactEmail = $_POST['contactEmail'];
  $contactSubject = $_POST['contactSubject'];
  $contactMessage = $_POST['contactMessage'];

  // подразумевается что $to, $subject, $message уже определены...
  $to = get_option('admin_email');

  // удалим фильтры, которые могут изменять заголовок $headers
  remove_all_filters( 'wp_mail_from' );
  remove_all_filters( 'wp_mail_from_name' );

  $headers = array(
    'From: Me Myself <me@example.net>',
    'content-type: text/html',
    'Cc: John Q Codex <jqc@wordpress.org>',
    'Cc: iluvwp@wordpress.org', // тут можно использовать только простой email адрес
  );

  wp_mail( $to, $contactSubject, $contactMessage, $headers );
  wp_die();

}

// замена сепаратора в title
add_filter( 'document_title_separator', 'my_sep' );
function my_sep( $sep ){
  $sep = ' | ';
	return $sep;
}

//вывод отдельной статьи и добавили спасибо
add_filter('the_content', 'test_content');
function test_content($content) {
  $content .= "Спасибо за прочтение статьи";
  return $content; 
}

function register_my_widgets(){

	register_sidebar( array(
		'name'          => 'Left Sidebar',
		'id'            => "left_sidebar",
		'description'   => 'Описание сайдбара',
    'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h5 class="widgettitle">',
		'after_title'   => "</h5>\n"
	) );
}

function theme_register_nav_menu() {
  register_nav_menu('top', 'верхнее меню');
  register_nav_menu('links', 'ссылки');
  register_nav_menu('footer', 'нижнее меню');
  add_theme_support('title-tag'); // добавляет title страницам header
  add_theme_support('post-thumbnails', array('post', 'portfolio')); // добавляет миниатюру на пост
  add_theme_support('post-formats', array('video', 'aside', 'chat', 'gallery'));
  add_image_size( 'post-thumb', 1300, 500, true ); // размер миниатюры поста
  add_filter('navigation_markup_template', 'my_navigation_template', 10, 2);
  
  add_action( 'init', 'register_post_types' );
  function register_post_types(){
    register_post_type( 'portfolio', [
      'label'  => null,
      'labels' => [
        'name'               => 'Портфолио', // основное название для типа записи
        'singular_name'      => 'Портфолио', // название для одной записи этого типа
        'add_new'            => 'Добавить работу', // для добавления новой записи
        'add_new_item'       => 'Добавление работы', // заголовка у вновь создаваемой записи в админ-панели.
        'edit_item'          => 'Редактирование работы', // для редактирования типа записи
        'new_item'           => 'Новая работа', // текст новой записи
        'view_item'          => 'Смотреть работу', // для просмотра записи этого типа.
        'search_items'       => 'Искать работу в портфолио', // для поиска по этим типам записи
        'not_found'          => 'Не найдено', // если в результате поиска ничего не было найдено
        'not_found_in_trash' => 'Не найдено в корзине', // если не было найдено в корзине
        'parent_item_colon'  => '', // для родителей (у древовидных типов)
        'menu_name'          => 'Портфолио', // название меню
      ],
      'description'         => 'Это наши работы в портфолио',
      'public'              => true,
      'publicly_queryable'  => true, // зависит от public
      'exclude_from_search' => true, // зависит от public
      'show_ui'             => true, // зависит от public
      'show_in_nav_menus'   => true, // зависит от public
      'show_in_menu'        => true, // показывать ли в меню адмнки
      'show_in_admin_bar'   => true, // зависит от show_in_menu
      'show_in_rest'        => true, // добавить в REST API. C WP 4.7
      'rest_base'           => null, // $post_type. C WP 4.7
      'menu_position'       => 4,
      'menu_icon'           => 'dashicons-format-gallery',
      //'capability_type'   => 'post',
      //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
      //'map_meta_cap'      => null, // Ставим true чтобы включить дефолтный обработчик специальных прав
      'hierarchical'        => false,
      'supports'            => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt'], // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
      'taxonomies'          => [ 'skills' ],
      'has_archive'         => false,
      'rewrite'             => true,
      'query_var'           => true,
    ] );
  }


  // хук для регистрации
add_action( 'init', 'create_taxonomy' );
function create_taxonomy(){
	// список параметров: wp-kama.ru/function/get_taxonomy_labels
	register_taxonomy( 'skills', [ 'portfolio', 'post' ], [ 
		'label'                 => '', // определяется параметром $labels->name
		'labels'                => [
			'name'              => 'Навыки',
			'singular_name'     => 'Навык',
			'search_items'      => 'Найти навык',
			'all_items'         => 'Все навыкиs',
			'view_item '        => 'Смотреть навыки',
			'parent_item'       => 'Родительский навык',
			'parent_item_colon' => 'Родительский Навык:',
			'edit_item'         => 'Изменить навык',
			'update_item'       => 'Обновить навык',
			'add_new_item'      => 'Добавить новый навык',
			'new_item_name'     => 'Новое имя навыка',
			'menu_name'         => 'Навыки',
		],
		'description'           => 'Навыки, которые использовались', // описание таксономии
		'public'                => true,
		'publicly_queryable'    => null, // равен аргументу public
		'hierarchical'          => false,
		'rewrite'               => true
	] );
}

  add_action( 'init', 'skils_for_portfolio');
  function skils_for_portfolio(){
    register_taxonomy_for_object_type( 'skills', 'portfolio' );

    // Тоже самое можно сделать и с "Рубриками"
    // unregister_taxonomy_for_object_type( 'category', 'post' );
}

  // удаляет Н2 из шаблона пагинации
  function my_navigation_template($template, $class) {
    return '
    <nav class="navigation %1$s" role="navigation">
    <div class="nav-links">%3$s</div>
    </nav>';
  }

  // выводит пагинацию
  the_posts_pagination(array(
    'end_size' => 2,
  ));
}

function theme_styles() {
  wp_enqueue_style('styles', get_stylesheet_uri());
  wp_enqueue_style('default', get_template_directory_uri() . '/assets/css/default.css');
  wp_enqueue_style('layout', get_template_directory_uri() . '/assets/css/layout.css');
}

function theme_scripts() {
  wp_deregister_script('jquery');
  wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
  wp_enqueue_script('jquery');
  wp_enqueue_script('flexslider', get_template_directory_uri() . '/assets/js/jquery.flexslider.js', ['jquery'], null, true);
  wp_enqueue_script('doubletaptogo', get_template_directory_uri() . '/assets/js/doubletaptogo.js', ['jquery'], null, true);
  wp_enqueue_script('init', get_template_directory_uri() . '/assets/js/init.js', ['jquery'], null, true);
  wp_enqueue_script('modernizr', get_template_directory_uri() . '/assets/js/modernizr.js', null, null, false);
  wp_enqueue_script('main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], null, true);

}

// // зарегистрировали свое придуманное действие
// add_action('my_action', 'action_function');
// function action_function() {
//   echo 'Я тут!';
// }

// // шорткод
// add_shortcode('my_short', 'short_function');
// function short_function() {
//   return 'я шорткод!';
// }
