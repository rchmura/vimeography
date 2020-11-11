<template>
  <teleport to="body">
    <div class="modal">
      <div class="contents">
        <div>
          <label><strong>New Gallery Title</strong></label>
          <input type="text" name="gallery_title" v-model="galleryTitle" />

          <label><strong>Show the videos from</strong></label>
          <input type="text" name="gallery_source" v-model="gallerySourceUrl" />

          <p>
            <label>
              <input type="checkbox" v-model="copyAppearance" />
              Also copy gallery appearance settings</label
            >
          </p>

          <div class="actions">
            <button disabled v-if="this.loading">Working…</button>
            <button v-else @click="submit">Duplicate gallery</button>
            <button class="secondary" @click="closeModal">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import axios from "axios";

export default {
  name: "DuplicateGallery",
  data() {
    return {
      galleryId: this.galleryToDuplicate.id,
      galleryTitle: this.galleryToDuplicate.title + ` copy`,
      gallerySourceUrl: this.galleryToDuplicate.source_url,
      copyAppearance: false,
      loading: false,
      errored: false,
    };
  },
  created() {
    axios.defaults.headers.post["X-WP-Nonce"] = window.wpApiSettings.nonce;
  },
  props: {
    modalOpen: String,
    galleryToDuplicate: Object,
  },
  methods: {
    submit() {
      this.loading = true;

      axios
        .post(
          window.wpApiSettings.root +
            "vimeography/v1/galleries/" +
            this.galleryId +
            "/duplicate",
          {
            id: this.galleryId,
            title: this.galleryTitle,
            source_url: this.gallerySourceUrl,
            copy_appearance: this.copyAppearance,
          }
        )
        .then(() => {
          this.closeModal();
          this.$emit("display-notification", {
            type: "success",
            message: "Gallery duplicated",
          });
          this.$emit("reload-galleries");
        })
        .catch((error) => {
          this.errored = true;
          console.log(error);
        })
        .finally(() => (this.loading = false));
    },
    closeModal() {
      this.$emit("close-modal");
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.modal {
  position: fixed;
  z-index: 99999;
  background: rgb(57 66 80 / 0.5);
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.contents {
  position: relative;
  background: #fff;
  padding: 2rem;
  border-radius: 0.25rem;
  box-shadow: 0 2px 5px rgb(57 66 80 / 0.5);
}

.actions {
  display: flex;
  justify-content: space-between;
}

button {
  background: rgb(55, 49, 143);
  border: 0;
  color: #fff;
  padding: 0.65rem 1rem;
  border-radius: 0.25rem;
  font-weight: 500;
}

button.secondary {
  background: none;
  color: red;
}
</style>
