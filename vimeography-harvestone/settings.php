<?php

  $settings = array(
    array(
      'type'       => 'colorpicker',
      'label'      => __('Inactive Thumbnail Border Color'),
      'id'         => 'inactive-thumbnail-border-color',
      'value'      => '#cccccc',
      'pro'        => false,
      'namespace'  => true,
      'properties' =>
        array(
          array('target' => '.vimeography-harvestone .vimeography-thumbnail-container .vimeography-link', 'attribute' => 'borderColor'),
        )
    ),
    array(
      'type'       => 'colorpicker',
      'label'      => __('Active Thumbnail Border Color'),
      'id'         => 'active-thumbnail-border-color',
      'value'      => '#5580e6',
      'pro'        => false,
      'namespace'  => true,
      'properties' =>
        array(
          array('target' => '.vimeography-harvestone .vimeography-thumbnail-container .vimeography-link.vimeography-link-active', 'attribute' => 'borderColor'),
        )
    ),
    array(
      'type'       => 'colorpicker',
      'label'      => __('Spinner Color'),
      'id'         => 'spinner-color',
      'value'      => '#0077dd',
      'pro'        => false,
      'namespace'  => true,
      'properties' =>
        array(
          array('target' => '.vimeography-harvestone .vimeography-player:before', 'attribute' => 'borderTopColor'),
        )
    ),
  );