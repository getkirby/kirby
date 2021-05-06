<template>
  <ul v-if="value" class="k-pages-field-preview">
    <li v-for="page in value" :key="page.id">
      <figure>
        <k-link :title="page.id" :to="$api.pages.link(page.id)" @click.native.stop>
          <k-icon type="page" back="pattern" class="k-pages-field-preview-image" />
          <figcaption>
            {{ page.text }}
          </figcaption>
        </k-link>
      </figure>
    </li>
  </ul>
</template>

<script>
export default {
  props: {
    value: Array
  }
};
</script>

<style>
.k-pages-field-preview {
  padding: 0 .25rem 0 .75rem;
  display: flex;
}
.k-pages-field-preview li {
  line-height: 0;
  margin-right: .5rem;
}
.k-pages-field-preview .k-link {
  display: flex;
  align-items: stretch;
  background: var(--color-background);
  box-shadow: var(--shadow);
}
.k-pages-field-preview-image {
  width: 1.525rem;
  height: 1.525rem;
  color: var(--color-gray-500) !important;
}
.k-pages-field-preview figcaption {
  flex-grow: 1;
  line-height: 1.5em;
  padding: 0 .5rem;
  border: 1px solid var(--color-border);
  border-left: 0;
  border-radius: var(--rounded-xs);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
