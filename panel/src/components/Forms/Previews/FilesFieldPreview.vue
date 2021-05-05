<template>
  <ul v-if="value" class="k-files-field-preview">
    <li v-for="file in value" :key="file.url">
      <k-link :title="file.filename" :to="file.link" @click.native.stop>
        <k-image v-if="file.type === 'image'" v-bind="imageOptions(file)" />
        <k-icon v-else v-bind="file.icon" />
      </k-link>
    </li>
  </ul>
</template>

<script>
import previewThumb from "@/helpers/previewThumb.js";

export default {
  props: {
    value: Array,
    field: Object
  },
  methods: {
    imageOptions(file) {
      const image = previewThumb(file.image);
      
      if (!image.src) {
        return {
          src: file.url
        };
      }
      
      return {
        ...image,
        back: "pattern",
        cover: false,
        ...this.field.image || {}
      }
    }
  }
}
</script>

<style>
.k-files-field-preview {
  display: grid;
  grid-gap: .5rem;
  grid-template-columns: repeat(auto-fill, 1.525rem);
  padding: 0 .75rem;
}
.k-files-field-preview li {
  line-height: 0;
}
.k-files-field-preview li .k-icon {
  height: 100%;
}
</style>
