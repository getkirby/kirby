<template>
  <nav 
    :aria-label="label" 
    class="k-breadcrumb"
  >
    <ol>
      <li
        v-for="(crumb, index) in links"
        :key="index"
      >
        <k-link
          :title="crumb.text || crumb.label"
          :to="crumb.link"
          :aria-current="isLast(index) ? 'page' : false"
          class="k-breadcrumb-link"
        >
          {{ crumb.text || crumb.label }}
        </k-link>
      </li>
    </ol>
  </nav>
</template>

<script>
export default {
  props: {
    /**
     * Array of link objects (with `link` and `text` or `label` items)
     */
    links: {
      type: Array,
      default() {
        return [];
      }
    },
    /**
     * ARIA label
     */
    label: {
      type: String,
      default: "Breadcrumb",
    },
  },
  methods: {
    isLast(index) {
      return (this.links.length - 1) === index;
    }
  }
}
</script>

<style lang="scss">
.k-breadcrumb ol {
  display: flex;
  align-items: center;
}
.k-breadcrumb-link {
  display: block;
  font-size: $font-size-small;
  padding: .75rem .5rem;
  line-height: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-breadcrumb li {
  display: flex;
  align-items: center;
}
.k-breadcrumb li:not(:last-child)::after {
  content: "/";
  flex-shrink: 0;
  opacity: .25;
}
.k-breadcrumb li:not(:last-child) {
  max-width: 15vw;
}
</style>
