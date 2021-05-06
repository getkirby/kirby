<template>
  <p class="k-url-field-preview">
    {{ column.before }}
    <k-link :to="link" target="_blank" @click.native.stop>
      {{ value }}
    </k-link>
    {{ column.after }}
  </p>
</template>

<script>
export default {
  props: {
    column: {
      type: Object,
      default() {
        return {};
      }
    },
    value: String,
  },
  computed: {
    link() {
      return this.value;
    }
  }
}
</script>

<style>
.k-url-field-preview {
  padding: 0 .75rem;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-url-field-preview a {
  color: var(--color-focus);
  text-decoration: underline;
  transition: color .3s;
  white-space: nowrap;
  max-width: 100%;
}
.k-url-field-preview a:hover {
  color: var(--color-black);
}

</style>
