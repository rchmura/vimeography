( function( blocks, components, i18n, element ) {
  var el = element.createElement;

  blocks.registerBlockType(

    'vimeography/gallery', {

    title: i18n.__( 'Vimeography Gallery' ),
    description: i18n.__( 'Display your videos in a beautiful gallery layout.' ),
    keywords: [ i18n.__( 'gallery' ), i18n.__( 'video' ) ],
    icon: 'slides',
    category: 'common',

    // Necessary for saving block content.
    // Note: you should eventually add all possible shortcode atts here.
    attributes: {
      id: {
        type: 'number'
      }
    },

    edit: function( props ) {

      var focus = props.focus;
      var attributes = props.attributes;

      return [
        !! focus && el(
          blocks.InspectorControls,
          { key: 'inspector' },
          el(
            'div',
            {},
            el( 'p', {}, 'Choose the gallery to display in the main block editor.' ),
          )
        ),
        el( 'div', { className: props.className },
          el( components.SelectControl, {
            label: 'Select a Video Gallery…',
            value: attributes.id ? attributes.id : '1',
            options: window.vimeography_galleries.map( function (gallery) {
              return { value: gallery.id, label: gallery.title }
            } ),
            onChange: function( newId ) {
              props.setAttributes( { id: newId } );
            },
          })
        )
      ];
    },

    save: function( props ) {
      var attributes = props.attributes;

      return (
        el( 'div', { className: props.className },
          el( 'div', { className: 'vimeography-gallery-block' },
            attributes.id && el( 'p', {}, attributes.id )
          )
        )
      );
    },
  } );

} )(
  window.wp.blocks,
  window.wp.components,
  window.wp.i18n,
  window.wp.element,
);