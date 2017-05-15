import React from 'react';
import { render } from 'react-dom';

// Router
import { BrowserRouter, Match } from 'react-router';
import Gallery from './components/Gallery';

// Assets
import 'stylesheets/base';
import 'flickitycss';

const Root = (props) => {
  return (
    <BrowserRouter>
      <div>
        <Match pattern="*" component={() => (<Gallery details={props.gallery} />)} />
        <Match pattern="/video/:videoId" component={Gallery} />
      </div>
    </BrowserRouter>
  );
};

for (let gallery of window.vimeography.galleries) {
  if (gallery.theme == 'bugsauce') {
    render(<Root gallery={gallery}/>, document.querySelector(`#vimeography-gallery-${gallery.id}`) );
  }
}