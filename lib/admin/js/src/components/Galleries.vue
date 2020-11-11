<template>
  <div>
    <h3>Edit Galleries</h3>

    <section v-if="errored">
      <p>
        We're sorry, we're not able to retrieve this information at the moment,
        please try back later
      </p>
    </section>

    <section v-else>
      <div v-if="loading">Loading...</div>

      <table v-else>
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Video Source</th>
            <th>Shortcode</th>
            <th>Gallery Theme</th>
            <th>Created on</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="gallery in galleries" :key="gallery.id">
            <td>{{ gallery.id }}</td>
            <td>
              <strong
                ><a :href="editGalleryUrl(gallery.id)">{{
                  gallery.title
                }}</a></strong
              >
            </td>
            <td>
              <a :href="gallery.source_url" target="_blank">{{
                gallery.source_url
              }}</a>
            </td>
            <td>[vimeography id="{{ gallery.id }}"]</td>
            <td>{{ gallery.theme_name }}</td>
            <td>{{ gallery.date_created }}</td>
            <td>
              <div class="actions">
                <a :href="editGalleryUrl(gallery.id)" title="Edit Gallery">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="#ad915e"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                    />
                  </svg>
                </a>

                <a href="#" @click.prevent="duplicateGallery(gallery)">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="#4d60bf"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                  </svg>
                </a>

                <a
                  @click="confirmDelete"
                  :href="deleteGalleryUrl(gallery.id)"
                  title="Delete Gallery"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="#B73657"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    /></svg
                ></a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Galleries",
  data() {
    return {
      galleries: null,
      loading: true,
      errored: false,
    };
  },
  methods: {
    editGalleryUrl(id) {
      return `?page=vimeography-edit-galleries&id=${id}`;
    },
    duplicateGallery(gallery) {
      this.$emit("duplicate-gallery", gallery);
    },
    deleteGalleryUrl(id) {
      return `?page=vimeography-edit-galleries&vimeography-action=delete_gallery&gallery_id=${id}`;
    },
    confirmDelete(e) {
      if (
        !confirm(
          "WARNING: You are about to delete this gallery. 'Cancel' to stop, 'OK' to delete."
        )
      ) {
        e.preventDefault();
      }
    },
  },
  mounted() {
    axios
      .get(window.wpApiSettings.root + "vimeography/v1/galleries")
      .then((response) => (this.galleries = response.data))
      .catch((error) => {
        this.errored = true;
        console.log(error);
      })
      .finally(() => (this.loading = false));
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h3 {
  margin: 0 0 10px;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  display: inline-block;
  margin: 0 10px;
}

a {
  color: #6c42b9;
}

table {
  padding: 1rem;
  border-collapse: separate;
  border-spacing: 0px 12px;
  width: 100%;
  max-width: 1500px;
}

th,
td {
  padding: 0 0.25rem 0.5rem;
  margin: 0 0.25rem 0.5rem;
  border-bottom: 1px solid #eee;
}

.actions {
  display: flex;
  align-items: center;
}

svg {
  width: 24px;
  height: 24px;
  padding: 0.25rem 0.35rem;
}
</style>
