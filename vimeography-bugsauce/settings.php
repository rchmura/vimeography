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
  array(
    'type'       => 'colorpicker',
    'label'      => __('Inactive Thumbnail Border Color'),
    'id'         => 'inactive-thumbnail-border-color',
    'value'      => '#0088CC',
    'pro'        => FALSE,
    'namespace'  => TRUE,
    'properties' =>
      array(
        array('target' => '.vimeography-bugsauce .vimeography-thumbnails .vimeography-slides li img', 'attribute' => 'borderColor'),
      )
  ),
  array(
    'type'       => 'colorpicker',
    'label'      => __('Pager Arrow Color'),
    'id'         => 'pager-arrow-color',
    'value'      => '#000000',
    'pro'        => FALSE,
    'namespace'  => TRUE,
    'properties' =>
      array(
        array('target' => '.vimeography-bugsauce .vimeography-bugsauce-direction-nav a.vimeography-bugsauce-prev span', 'attribute' => 'borderRightColor'),
        array('target' => '.vimeography-bugsauce .vimeography-bugsauce-direction-nav a.vimeography-bugsauce-next span', 'attribute' => 'borderLeftColor'),
      )
  ),
  array(
    'type'       => 'colorpicker',
    'label'      => __('Loader Color'),
    'id'         => 'loader-color',
    'value'      => '#000000',
    'pro'        => FALSE,
    'namespace'  => TRUE,
    'important'  => TRUE,
    'properties' =>
      array(
        array('target' => '.vimeography-bugsauce .vimeography-main .vimeography-spinner div div', 'attribute' => 'backgroundColor'),
      )
  ),
  array(
    'type'       => 'slider',
    'label'      => __('Pager Arrow Size'),
    'id'         => 'pager-arrow-size',
    'value'      => '5',
    'pro'        => TRUE,
    'namespace'  => TRUE,
    'properties' =>
      array(
        array('target' => '.vimeography-bugsauce .vimeography-bugsauce-direction-nav a.vimeography-bugsauce-prev span', 'attribute' => 'borderWidth'),
        array('target' => '.vimeography-bugsauce .vimeography-bugsauce-direction-nav a.vimeography-bugsauce-next span', 'attribute' => 'borderWidth'),
      ),
    'min'       => '5',
    'max'       => '10',
    'step'      => '1',
  ),
);