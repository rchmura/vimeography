<?php

class Vimeography_Renderer
{
  /**
   * [$_theme description]
   * @var [type]
   */
  protected $_theme;

  /**
   * Creates the rendering engine
   *
   * $settings should contain at least
   *  - theme = theme name to use
   * optionals are:
   *  - partial =  if not full theme would be renderd
   *
   * @param unknown $settings
   * @throws Vimeography_Exception
   */
  public function __construct($settings, $gallery_id)
  {
    if (! isset($settings['theme']))
      throw new Vimeography_Exception(__('You must specify a theme in either the admin panel or the shortcode.'));

    $vimeography = Vimeography::get_instance();

    if (! $vimeography->active_theme)
      $vimeography->set_active_theme($settings['theme']);

    $theme = $vimeography->active_theme;

    if (file_exists($theme['file_path']))
    {
      require_once $theme['file_path'];
    }
    else
    {
      throw new Vimeography_Exception('The "' . $theme['name'] . '" theme does not exist or is improperly structured.');
    }

    $class = 'Vimeography_Themes_'.ucfirst( $theme['name'] );

    if (! class_exists($class))
      throw new Vimeography_Exception('The "' . $theme['name'] . '" theme class does not exist or is improperly structured.');

    $mustache = new Mustache_Engine( array(
      'loader'          => new Mustache_Loader_FilesystemLoader($theme['plugin_path']),
      'partials_loader' => new Mustache_Loader_FilesystemLoader($theme['partials_path']),
    ) );

    $this->_view  = new $class;
    $this->_theme = (isset($settings['partial'])) ? $mustache->loadPartial(strtolower($settings['partial'])) : $mustache->loadTemplate( strtolower($theme['name']) );

    add_action('wp_enqueue_scripts',    array($class, 'load_scripts'));
    add_action('admin_enqueue_scripts', array($class, 'load_scripts'));

    // Action has already been run, we're late to the party.
    if (is_admin())
    {
      do_action('admin_enqueue_scripts');
    }
    else
    {
      do_action('wp_enqueue_scripts');
    }

    $this->_view->gallery_id = $gallery_id;

    if (isset($settings['width']))
      $this->_view->gallery_width = $settings['width'];

  }

  /**
   * Renders the data inside of the theme's Mustache template.
   * We did it!
   *
   * @param  array $data [description]
   * @return string       [description]
   */
  public function render($result)
  {
    $this->_view->data     = $result->video_set;
    $this->_view->featured = $this->_view->data[0];

    return $this->_theme->render($this->_view);
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