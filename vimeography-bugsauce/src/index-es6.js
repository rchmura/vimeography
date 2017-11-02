import Gallery from './Gallery'
import 'stylesheets/base'

import 'flickitycss'

for (let gallery of window.vimeography.galleries) {
  let g = new Gallery(gallery)
}