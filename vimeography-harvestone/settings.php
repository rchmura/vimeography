<?php

  $settings = array(
    array(
      'type'       => 'colorpicker',
      'label'      => __('Active Thumbnail Border Color'),
      'id'         => 'active-thumbnail-border-color',
      'value'      => '#0088CC',
      'pro'        => FALSE,
      'namespace'  => TRUE,
      'properties' =>
        array(
          array('target' => '.vimeography-bugsauce .vimeography-thumbnails .vimeography-slides li.vimeography-bugsauce-active-slide img', 'attribute' => 'borderColor'),
        )
    ),
  );