<script>
import { mapState, mapActions } from 'vuex'
import Spinner from 'vue-simple-spinner'

import debounce from 'lodash/debounce';

const template = `
  <label :class="$style.container">
    <span :class="$style.text">Search:</span>
    <input :class="$style.input" name="search" type="text" v-on:input="debounceInput" />

    <div :class="$style.icon">
      <svg v-show="!this.searching" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="10.5" cy="10.5" r="7.5"></circle><line x1="21" y1="21" x2="15.8" y2="15.8"></line>
      </svg>

      <spinner size="small" v-show="this.searching"></spinner>
    </div>
  </label>
`;

const Search = {
  template,
  components: {
    Spinner
  },
  methods: {
    ...mapActions([
      'performSearch'
    ]),
    debounceInput: debounce(function (e) {
      this.performSearch(e.target.value);
    }, 500),
  },
  computed: mapState({
    searching: state => state.videos.loading,
  }),
}

export default Search;
</script>

<style module>
  .container {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    position: relative;
  }

  .text {
    font-weight: bold;
    margin-right: 0.5rem;
    display: none;
  }

  input.input {
    border: 0;
    padding: 0.5rem;
    border-radius: 4px;
    width: 240px;
    border: 1px solid #fafafa;
    margin: 0;
    height: auto;
  }

  .icon {
    position: absolute;
    right: 8px;
    top: 8px;
  }

  .icon svg {
    stroke: #333;
  }
</style>
