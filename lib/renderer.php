<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Renderer {
  /**
   * The settings to be used when rendering the gallery.
   *
   * @var array
   */
  protected $_settings;

  /**
   * The ID of the Vimeography gallery being rendered.
   *
   * @var int
   */
  protected $_gallery_id;

  /**
   * The meta headers for the active Vimeography theme
   *
   * @var array
   */
  protected $_active_theme;

  /**
   * The theme class instance for the current active Vimeography theme
   *
   * @var object
   */
  protected $_view;

  /**
   * The Mustache template being used to render the Vimeo data.
   *
   * @var [type] A mustache template or partial
   */
  protected $_template;

  /**
   * Backwards compatibility variable for Vimeography Pro < 0.6.3
   * This is an instanceof $_template
   *
   * @var [type]
   */
  protected $_theme;

  /**
   * Creates the rendering engine, called from the Vimeography Shortcode.
   *
   * $settings should contain at least
   *  - theme = theme name to use
   * optionals are:
   *  - partial =  if not full theme would be rendered
   *
   * @param array  $settings   The shortcode gallery settings
   * @param int    $gallery_id The ID of the Vimeography gallery being loaded.
   *
   * @throws Vimeography_Exception
   */
  public function __construct($settings, $gallery_id) {
    $this->_settings   = $settings;
    $this->_gallery_id = $gallery_id;

    if (! $this->_view) {
      $this->_view = new stdClass();
    }
  }

  /**
   * Retrieve and set the meta headers for the current active theme.
   *
   * @param array  Gallery settings
   */
  protected function _set_active_theme($settings) {
    if ( ! isset( $settings['theme'] ) ) {
      throw new Vimeography_Exception(
        __('You must specify a theme in either the admin panel or the shortcode.', 'vimeography')
      );
    }

    $vimeography = Vimeography::get_instance();
    $vimeography->addons->set_active_theme( $settings['theme'] );

    $this->_active_theme = $vimeography->addons->active_theme;
  }

  /**
   * Loads the theme class for the current active theme.
   *
   * @param  array $theme  Meta headers for the active theme
   * @return void
   */
  protected function _load_theme_class($theme) {

    // If the theme class exists, require it.
    if ( file_exists( $theme['file_path'] ) ) {
      require_once $theme['file_path'];
    } else {
      // If it doesn't exist, throw an appropriate error message.
      if ( empty( $theme['name'] ) ) {
        throw new Vimeography_Exception(
          __('This Vimeography gallery does not have a theme assigned to it.', 'vimeography')
        );
      } else {
        throw new Vimeography_Exception(
          sprintf(
            __('The "%s" theme does not exist or is improperly structured.', 'vimeography'),
            $theme['name']
          )
        );
      }
    }

    // Build the conventional theme class name
    $class = 'Vimeography_Themes_' . ucfirst( $theme['name'] );

    if ( ! class_exists( $class ) ) {
      throw new Vimeography_Exception(
        sprintf(
          __('The "%s" theme class does not exist or is improperly named.', 'vimeography'),
          $theme['name']
        )
      );
    }

    $this->_view  = new $class;
  }

  /**
   * Loads the Mustache template or partial for the active theme.
   *
   * @param  array  $theme    Meta headers for the active theme
   * @param  array  $settings Gallery shortcode settings
   * @return void
   */
  protected function _load_theme_template($theme, $settings) {
    $loaders = array_merge(
      self::_load_theme_template_override( $theme['plugin_override_path'] ),
      array( new Mustache_Loader_FilesystemLoader( $theme['plugin_path'] ) )
    );

    $partial_loaders = array_merge(
      self::_load_theme_template_override( $theme['partials_override_path'] ),
      array( new Mustache_Loader_FilesystemLoader( $theme['partials_path'] ) )
    );

    $mustache = new Mustache_Engine( array(
      'loader'          => new Mustache_Loader_CascadingLoader( $loaders ),
      'partials_loader' => new Mustache_Loader_CascadingLoader( $partial_loaders ),
    ) );

    $this->_template = ( isset( $settings['partial'] ) ) ?
      $mustache->loadPartial( strtolower( $settings['partial'] ) ) :
      $mustache->loadTemplate( strtolower( $theme['name'] ) );

    // Backwards-Compatibility for Vimeography Pro < 0.7
    $this->_theme = $this->_template;
  }

  /**
   * Attempts to load a theme template override, if it exists.
   *
   * @param  string $path  path to the theme template override, eg. 'wp-content/themes/bones/vimeography/bugsauce/'
   * @return array
   */
  protected static function _load_theme_template_override($override_path) {
    try {
      return array( new Mustache_Loader_FilesystemLoader( $override_path ) );
    } catch (Mustache_Exception_RuntimeException $e) {
      return array();
    }
  }

  /**
   * Enables the active theme to hook into the _enqueue_scripts actions.
   *
   * This is what allows each theme to be able to load all of its own
   * asset dependencies, including custom javascripts and stylesheets.
   *
   * @param  object $view  The theme class instance
   * @return void
   */
  protected function _load_theme_dependencies($view) {
    add_action('wp_enqueue_scripts',    array( get_class( $view ), 'load_scripts' ) );
    add_action('admin_enqueue_scripts', array( get_class( $view ), 'load_scripts' ) );

    if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
      // Action has already been run, we're late to the party.
      is_admin() ? do_action('admin_enqueue_scripts') : do_action('wp_enqueue_scripts');
    }
  }

  /**
   * [load_theme description]
   * @return [type] [description]
   */
  public function load_theme() {
    self::_set_active_theme( $this->_settings );
    self::_load_theme_class( $this->_active_theme );
    self::_load_theme_template( $this->_active_theme, $this->_settings );
    self::_load_theme_dependencies( $this->_view );
    $this->_view->gallery_id = $this->_gallery_id;
  }

  /**
   * Renders the data inside of the theme's Mustache template.
   * We did it!
   *
   * @param  array $data Video set data from Vimeo
   * @return string | html
   */
  public function render($result) {
    // Set remaining view variables and render away
    if ( isset( $this->_settings['width'] ) ) {
      $this->_view->gallery_width = $this->_settings['width'];
    }

    $this->_view->data     = $result->video_set;

    if (! empty( $this->_view->data[0] ) ) {
      $this->_view->featured = clone $this->_view->data[0];
    }

    return $this->_template->render($this->_view);
  }

  /**
   * Allows for examination of the data currently being sent to the gallery.
   *
   * @param  string $data A JSON string of Vimeo data.
   * @return string       Prints the string.
   */
  protected function debug($data)
  {
    echo '<h1>Vimeography Debug</h1>';
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die;
  }

}
