<template>
  <nav :aria-label="label" class="k-breadcrumb">
    <ol>
      <li
        v-for="(crumb, index) in links"
        :key="index"
      >
        <k-link
          :title="crumb.text || crumb.label"
          :to="crumb.link"
          :aria-current="isLast(index) ? 'page' : false"
          class="truncate"
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
    label: {
      type: String,
      default: "Breadcrumb",
    },
    links: {
      type: Array,
      default() {
        return [];
      }
    }
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
.k-breadcrumb a {
  display: block;
  font-size: $font-size-small;
  padding: 0.75rem .5rem;
  line-height: 1;
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
