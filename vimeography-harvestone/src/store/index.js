import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

const store = new Vuex.Store({
	state: {},
	mutations: {
		['LOAD_VIDEO'] (state, payload) {
			state.activeVideo = payload.url;
		},
		['PLAYER_READY'] (state, payload) {
			state.player = payload.player;
		},
		['PLAYER_PLAY'] (state, payload) {
			return;
		},
		['PLAYER_PAUSE'] (state, payload) {
			return;
		},
		['PLAYER_ENDED'] (state, payload) {
			return;
		},
		['PLAYER_TIME_UPDATE'] (state, payload) {
			return;
		},
		['PLAYER_PROGRESS'] (state, payload) {
			return;
		},
		['PLAYER_SEEKED'] (state, payload) {
			return;
		},
		['PLAYER_VOLUME_CHANGE'] (state, payload) {
			return;
		},
		['PLAYER_LOADED'] (state, payload) {
			return;
		}
	},
	actions: {
		playerReady (store, player) {
			store.commit({
			  type: 'PLAYER_READY',
			  player
			})
		},
		playerPlay (store, data) {
			store.commit({
			  type: 'PLAYER_PLAY',
			  data
			})
		},
		playerPause (store, data) {
			store.commit({
			  type: 'PLAYER_PAUSE',
			  data
			})
		},
		playerEnded (store, data) {
			store.commit({
			  type: 'PLAYER_ENDED',
			  data
			})
		},
		playerTimeUpdate (store, data) {
			store.commit({
			  type: 'PLAYER_TIME_UPDATE',
			  data
			})
		},
		playerProgress (store, data) {
			store.commit({
			  type: 'PLAYER_PROGRESS',
			  data
			})
		},
		playerSeeked (store, data) {
			store.commit({
			  type: 'PLAYER_SEEKED',
			  data
			})
		},
		playerVolumeChange (store, data) {
			store.commit({
			  type: 'PLAYER_VOLUME_CHANGE',
			  data
			})
		},
		playerLoaded (store, data) {
			store.commit({
			  type: 'PLAYER_LOADED',
			  data
			})
		},
		loadVideo (store, url) {
			store.commit({
			  type: 'LOAD_VIDEO',
			  url
			})
		}
	}
});

store.replaceState({
	...window.vimeography.galleries[0],
	activeVideo: window.vimeography.galleries[0].video_set[0].link
});


export default store;
