<?php

class Vimeography_Renderer
{
  protected $_theme;

  /**
   * The gallery ID to be sent to the Mustache template.
   *
   * @var string
   */
  private $_gallery_id;

  /**
   * The gallery width to be sent to the Mustache template.
   *
   * @var string
   */
  private $_gallery_width;

  /**
   * Creates the rendering engine
   *
   * $settings should contain at least
   *  - theme = theme name to use
   *  - width
   * optionals are:
   *  - partial =  if not full theme would be renderd
   *
   * @param unknown $settings
   * @param unknown $token
   * @throws Vimeography_Exception
   */
  public function __construct($settings, $token)
  {
    if (! isset($settings['theme']))
      throw new Vimeography_Exception('You must specify a theme in either the admin panel or the shortcode.');

    $theme = $settings['theme'];

    if (! require_once(VIMEOGRAPHY_THEME_PATH . $theme . '/' . $theme . '.php' ) )
      throw new Vimeography_Exception('The "' . $theme . '" theme does not exist or is improperly structured.');

    $class = 'Vimeography_Themes_'.ucfirst( $theme );

    if (! class_exists($class))
      throw new Vimeography_Exception('The "' . $theme . '" theme class does not exist or is improperly structured.');

    $mustache = new Mustache_Engine( array(
      'loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_THEME_PATH . '/' . $theme),
      'partials_loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_THEME_PATH . '/' . $theme . '/partials'),
    ) );

    $this->_view  = new $class;
    $this->_theme = (isset($settings['partial'])) ? $mustache->loadPartial($settings['partial']) : $mustache->loadTemplate( $theme );

    $this->_view->gallery_id    = $token;
    $this->_view->gallery_width = $settings['width'];
  }

  /**
   * Renders the data inside of the theme's Mustache template.
   * We did it!
   *
   * @param  array $data [description]
   * @return string       [description]
   */
  public function render($data)
  {
    $this->_view->data     = $data;
    $this->_view->featured = $this->_view->data[0];

    return $this->_theme->render($this->_view);
  }

  /**
   * [set_paging description]
   * @param [type] $paging [description]
   */
  public function set_paging($paging)
  {
    $this->_view->paging = $paging;
  }

  /**
   * Allows for examination of the data currently being sent to the gallery.
   *
   * @param  string $data A JSON string of Vimeo data.
   * @return void       Prints the string.
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