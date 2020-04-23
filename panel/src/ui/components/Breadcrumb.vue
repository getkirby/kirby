<template>
  <nav
    :aria-label="label"
    class="k-breadcrumb"
  >

    <k-dropdown class="k-breadcrumb-dropdown">
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
          <k-icon
            v-if="crumb.icon"
            :type="crumb.icon"
            class="k-breadcrumb-icon"
          />
          <span class="k-breadcrumb-link-text">
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
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2.5rem;
  width: 2.5rem;
}

.k-breadcrumb ol {
  display: none;
  align-items: center;
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
  display: flex;
  font-size: $text-sm;
  padding: .625rem .5rem;
  align-self: stretch;
  line-height: 1.25rem;
  align-items: center;
  min-width: 0;
}
.k-breadcrumb-link-text {
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-breadcrumb-icon {
  margin-right: .5rem;
}
.k-breadcrumb li {
  display: flex;
  flex-shrink: 3;
  min-width: 0;
  align-items: center;
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
