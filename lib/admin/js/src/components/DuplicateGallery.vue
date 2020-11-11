<template>
  <teleport to="body">
    <div class="modal">
      <div class="contents">
        <div>
          <form
            id="vimeography-duplicate-gallery-form"
            class="form-wrap"
            method="get"
            action=""
          >
            <!-- {{{duplicate_gallery_nonce}}} -->
            <input
              type="hidden"
              name="vimeography-action"
              value="duplicate_gallery"
            />
            <input
              type="hidden"
              name="page"
              value="vimeography-edit-galleries"
            />
            <input
              type="hidden"
              id="vimeography-duplicate-gallery-id"
              name="gallery_id"
              v-model="galleryId"
            />

            <label for="vimeography-duplicate-gallery-title"
              ><strong>New Gallery Title</strong></label
            >
            <input
              type="text"
              id="vimeography-duplicate-gallery-title"
              name="gallery_title"
              v-model="galleryTitle"
            />

            <label for="vimeography-duplicate-gallery-source"
              ><strong>Show the videos from</strong></label
            >
            <input
              type="text"
              id="vimeography-duplicate-gallery-source"
              name="gallery_source"
              :value="gallerySourceUrl"
            />

            <p>
              <label for="vimeography-duplicate-gallery-appearance"
                ><input
                  id="vimeography-duplicate-gallery-appearance"
                  name="duplicate_appearance"
                  type="checkbox"
                  checked="checked"
                />
                Also copy gallery appearance settings</label
              >
            </p>
          </form>

          <div class="actions">
            <button>Duplicate gallery</button>
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
export default {
  name: "DuplicateGallery",
  data() {
    return {
      galleryId: this.galleryToDuplicate.id,
      galleryTitle: this.galleryToDuplicate.title + ` copy`,
      gallerySourceUrl: this.galleryToDuplicate.source_url,
    };
  },
  props: {
    modalOpen: String,
    galleryToDuplicate: Object,
  },
  methods: {
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
