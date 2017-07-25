import * as types from './mutations'

export const loadVideo = (store, videoId) => {
	store.commit({
	  type: types.LOAD_VIDEO,
	  videoId
	})
}
