<?php

class Vimeography_Renderer extends Vimeography_Core
{
  /**
   * The gallery ID to be sent to the Mustache template.
   * @var integer
   */
  private $_gallery_id;

  /**
   * The name of the theme, used to load the proper template.
   * @var string
   */
  private $_theme;

  /**
   * The gallery width to be sent to the Mustache template.
   * @var string
   */
  private $_gallery_width;

  public function __construct($settings)
  {
    $this->_gallery_id    = $settings['id'];
    $this->_theme         = $settings['theme'];
    $this->_gallery_width = $settings['width'];
  }

  /**
   * Renders the data inside of the theme's Mustache template.
   * We did it!
   *
   * @param  array $data [description]
   * @return string       [description]
   */
  protected function render($data)
  {
    if (! isset($this->_theme))
      throw new Vimeography_Exception('You must specify a theme in either the admin panel or the shortcode.');

    if (!@require_once(VIMEOGRAPHY_THEME_PATH . $this->_theme . '/'.$this->_theme.'.php'))
      throw new Vimeography_Exception('The "'.$this->_theme.'" theme does not exist or is improperly structured.');

    $class = 'Vimeography_Themes_'.ucfirst($this->_theme);

    if (! class_exists($class))
      throw new Vimeography_Exception('The "'.$this->_theme.'" theme class does not exist or is improperly structured.');

    $mustache = new $class;
    $theme    = $this->_load_theme($this->_theme);

    $mustache->data          = json_decode($data);
    $mustache->gallery_id    = $this->_gallery_id;
    $mustache->featured      = $mustache->data[0];
    $mustache->gallery_width = $this->_gallery_width;

    return $mustache->render($theme);
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
    print_r(json_decode($data));
    echo '</pre>';
    die;
  }

  /**
   * Retrieves the contents of a Vimeography theme's mustache template.
   *
   * @access private
   * @static
   * @param mixed $name
   * @return void
   */
  private static function _load_theme($name)
  {
    $path = VIMEOGRAPHY_THEME_PATH . $name . '/videos.mustache';
    if (! $result = @file_get_contents($path))
      throw new Vimeography_Exception('The gallery template for the "'.$name.'" theme cannot be found.');
    return $result;
  }
}