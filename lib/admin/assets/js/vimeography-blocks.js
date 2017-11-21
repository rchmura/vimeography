var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    InspectorControls = wp.blocks.InspectorControls,
    BlockDescription = wp.blocks.BlockDescription,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'vimeography/gallery', {
  title: 'Vimeography Gallery',

  icon: 'editor-code',

  category: 'layout',

  edit: function() {
    return [
      el( 'p', { style: blockStyle }, 'Hello editor.' ),
      el(
          InspectorControls,
          { key: 'inspector' },
          el(
            BlockDescription,
            {},
            el( 'p', {}, 'Learn about this thing.' ),
          )
      )
    ];
  },

  save: function( props ) {
    return el( 'p', { style: blockStyle }, 'Hello saved content.' );
  },
} );
