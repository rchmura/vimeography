<?php

namespace Vimeography;

class Logger
{
  public function __construct()
  {
    add_action(
      "vimeography.request.response",
      array($this, "maybe_log_response"),
      10,
      6
    );
  }

  /**
   * Maybe log response
   */
  public function maybe_log_response(
    $response,
    $gallery_id,
    $gallery_settings,
    $endpoint,
    $params,
    $headers
  ) {
    $enabled = apply_filters('vimeography.logs.enabled', false, $gallery_id);

    if (!$enabled) {
      return;
    }

    global $post;

    // Only log front-end request
    if (!is_a($post, 'WP_Post')) {
      return;
    }

    $response_copy = unserialize(serialize($response));
    $response_code = $response_copy['status'];
    $response_headers = $response_copy['headers'];

    foreach ($response['body']->data as $index => $video) {
      $omitted = "[OMITTED BY \Vimeography\Logger]";

      unset($response_copy['body']->data[$index]->embed);
      unset($response_copy['body']->data[$index]->badges);
      unset($response_copy['body']->data[$index]->description);
      unset($response_copy['body']->data[$index]->pictures);
      unset($response_copy['body']->data[$index]->metadata);
      unset($response_copy['body']->data[$index]->user);
      unset($response_copy['body']->data[$index]->tags);
      unset($response_copy['body']->data[$index]->stats);
      unset($response_copy['body']->data[$index]->duration);
      unset($response_copy['body']->data[$index]->width);
      unset($response_copy['body']->data[$index]->height);

      $response_copy['body']->data[$index]->fields_omitted_by_logger =
        "embed,badges,description,pictures,metadata,user,tags,stats,duration,width,height";
    }

    $payload = array(
      "generated_at" => date("Y-m-d H:i:s"),
      "post_id" => $post->ID,
      "post_title" => $post->post_title,
      "post_permalink" => get_permalink($post->ID),
      "gallery_id" => $gallery_id,
      "gallery_settings" => $gallery_settings,
      "request_endpoint" => $endpoint,
      "request_params" => $params,
      "request_headers" => $headers,
      "response_code" => $response_code,
      "response_headers" => $response_headers,
      "response_body" => $response_copy['body']
    );

    // expires in 15 minutes
    set_site_transient(
      "vimeography_gallery_" . $gallery_id . "_response",
      $payload,
      900
    );
  }
}
