<template>
  <nav
    :aria-label="label"
    class="k-breadcrumb"
  >
    <k-dropdown class="k-breadcrumb-dropdown items-center justify-center">
      <k-button
        icon="road-sign"
        @click="$refs.dropdown.toggle()"
      />
      <k-dropdown-content
        ref="dropdown"
        :options="dropdown"
        theme="light"
      />
    </k-dropdown>

    <ol class="hidden items-center">
      <li
        v-for="(crumb, index) in links"
        :key="index"
        class="flex items-center"
      >
        <k-link
          :title="crumb.text || crumb.label"
          :to="crumb.link"
          :aria-current="isLast(index) ? 'page' : false"
          class="k-breadcrumb-link flex items-center text-sm"
        >
          <k-loader
            v-if="crumb.loading"
            class="k-breadcrumb-icon mr-2"
          />
          <k-icon
            v-else-if="crumb.icon"
            :type="crumb.icon"
            class="k-breadcrumb-icon mr-2"
          />
          <span class="k-breadcrumb-link-text truncate">
            {{ crumb.text || crumb.label }}
          </span>
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
  computed: {
    dropdown() {
      return this.$helper.clone(this.links).map(link => {
        link.icon = "angle-right";
        return link;
      });
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
.k-breadcrumb-dropdown {
  height: 2.5rem;
  width: 2.5rem;
  display: flex;
}

@media screen and (min-width: $breakpoint-sm) {
  .k-breadcrumb ol {
    display: flex;
  }
  .k-breadcrumb-dropdown {
    display: none;
  }
}

.k-breadcrumb-link {
  min-width: 0;
  align-self: stretch;
  padding: .625rem .5rem;
  line-height: 1.25rem;
}
.k-breadcrumb li {
  flex-shrink: 3;
  min-width: 0;
}
.k-breadcrumb li:last-child {
  flex-shrink: 1;
}
.k-breadcrumb li:not(:last-child)::after {
  content: "/";
  opacity: .5;
  flex-shrink: 0;
}
</style>
