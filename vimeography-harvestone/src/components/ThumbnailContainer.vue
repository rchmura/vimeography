<script>
/** Theme specific file. */
import { mapActions, mapState } from "vuex";

// Import Swiper and modules
// import { Swiper, Navigation, Pagination } from "swiper/js/swiper.esm.js";
import Swiper, { Navigation, Pagination } from "swiper";

import Thumbnail from "./Thumbnail.vue";

// swiper core styles
import 'swiper/css';

// modules styles
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const template = `
    <div class="vimeography-thumbnail-container" v-observe-visibility="visibilityChanged">
      <div class="swiper">
        <div class="swiper-wrapper">
          <thumbnail
            v-for="(video, index) in videos"
            v-bind:video="video"
            v-bind:index="index"
            v-bind:key="video.id">
          </thumbnail>
        </div>
      </div>

      <div class="swiper-button-prev" ref="prev"></div>
      <div class="swiper-button-next" ref="next"></div>
    </div>
  `;

const ThumbnailContainer = {
  props: ["videos", "activeVideoId"],
  template,
  components: {
    Thumbnail,
  },
  methods: {
    ...mapActions(["paginate"]),
    reload: function() {
      setTimeout(
        function() {
          this.swiper.update();
          this.swiper.navigation.update();
          this.swiper.updateSize();
          this.swiper.updateSlides();
          this.swiper.updateProgress();
          this.swiper.updateSlidesClasses();
        }.bind(this),
        250
      );
    },
    visibilityChanged: function(isVisible) {
      if (isVisible) {
        this.reload();
      }
    },
  },
  computed: {
    ...mapState({
      pro: (state) => state.gallery.pro,
    }),
  },
  updated: function() {
    this.reload();
  },
  watch: {
    activeVideoId(id) {
      let index = this.$store.getters.getVideoIndex(id);
      this.swiper.slideTo(index);
    },
  },
  mounted: function() {
    let initialSlide =
      this.$store.getters.getVideoIndex(this.activeVideoId) + 1;

    this.swiper = new Swiper(this.$el.childNodes[0], {
      modules: [Navigation, Pagination],
      initialSlide,
      slidesPerView: "auto",
      spaceBetween: 10,
      slideToClickedSlide: true,

      /*
          Namespace swiper classes
          Note: this will require you to write or copy entirely custom CSS
          Only do this if absolutely necessary

          containerModifierClass: '',
          slideClass: 'vimeography-thumbnail',
          slideActiveClass: 'vimeography-thumbnail-active',
          slideDuplicatedActiveClass: '',
          slideVisibleClass: '',
          slideDuplicateClass: '',
          slideNextClass: '',
          slideDuplicatedNextClass: '',
          slidePrevClass: '',
          slideDuplicatedPrevClass: '',
          wrapperClass: 'swiper-wrapper',
        */

      navigation: {
        nextEl: this.$refs.next,
        prevEl: this.$refs.prev,
      },
      observer: true,
      // observeParents: true,

      breakpoints: {
        320: {
          slidesPerGroup: 1,
          spaceBetween: 10,
        },
        480: {
          slidesPerGroup: 2,
          spaceBetween: 10,
        },
        640: {
          slidesPerGroup: 3,
          spaceBetween: 10,
        },
      },
    });

    this.swiper.on("progress", (progress) => {
      if (!this.pro) {
        console.log(
          "Vimeography PRO is not installed, pagination is unavailable."
        );
        return;
      }

      let paging = this.$store.getters.paging;

      console.log("Vimeography: gallery progress is " + progress);

      if (progress < 0.25) {
        this.paginate(paging.previous);
      }

      if (progress > 0.75) {
        this.paginate(paging.next);
      }
    });
  },
};

export default ThumbnailContainer;
</script>

<style lang="scss" scoped>
.vimeography-thumbnail-container {
  position: relative;
}

.swiper-slide {
  flex-shrink: 0;
  height: 100%;
  width: auto;
  position: relative;
}

.swiper-button-prev,
.swiper-button-next {
  cursor: pointer;
  width: 12px;
  height: 20px;
  margin-top: -10px;
  background-size: 12px 20px;

  &:after {
    font-size: 20px;
  }
}

.swiper-button-prev {
  left: -20px;
}

.swiper-button-next {
  right: -20px;
}
</style>
