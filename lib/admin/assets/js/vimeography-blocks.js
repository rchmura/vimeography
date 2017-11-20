// var el = wp.element.createElement,
//     registerBlockType = wp.blocks.registerBlockType,
//     blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

// registerBlockType( 'vimeography/gallery', {
//   title: 'Vimeography Gallery',

//   icon: '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 540.8 420.9" style="enable-background:new 0 0 540.8 420.9;" xml:space="preserve"><g><path fill="#2A2E35" d="M540.8,303.7H0V21.1C0,9.4,9.4,0,21.1,0h498.6c11.6,0,21.1,9.4,21.1,21.1V303.7z"/><path fill="#2A2E35" d="M162.8,420.9H21.1C9.4,420.9,0,411.5,0,399.9v-70.3h162.8V420.9z"/><rect x="189" y="329.5" fill="#41B3C0" width="162.8" height="91.4"/><path fill="#2A2E35" d="M519.7,420.9H378v-91.4h162.8v70.3C540.8,411.5,531.3,420.9,519.7,420.9z"/></g></svg>',

//   category: 'layout',

//   edit: function() {
//     return el( 'p', { style: blockStyle }, 'Hello editor.' );
//   },

//   save: function( props ) {
//     console.log('hi from vimeography blocks');
//     console.dir(props);
//     return el( 'p', { style: blockStyle }, 'Hello saved content.' );
//   },
// } );
