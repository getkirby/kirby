<template>
  <nav :aria-label="label" class="k-breadcrumb">
    <k-dropdown class="k-breadcrumb-dropdown">
      <k-button icon="road-sign" @click="$refs.dropdown.toggle()" />
      <k-dropdown-content ref="dropdown" :options="dropdown" theme="light" />
    </k-dropdown>

    <ol>
      <li v-for="(crumb, index) in segments" :key="index">
        <k-link
          :title="crumb.text || crumb.label"
          :to="crumb.link"
          :aria-current="isLast(index) ? 'page' : false"
          class="k-breadcrumb-link"
        >
          <k-loader v-if="crumb.loading" class="k-breadcrumb-icon" />
          <k-icon
            v-else-if="crumb.icon"
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
    crumbs: {
      type: Array,
      default() {
        return [];
      }
    },
    label: {
      type: String,
      default: "Breadcrumb"
    },
    view: Object
  },
  computed: {
    dropdown() {
      return this.segments.map((link) => ({
        ...link,
        text: link.label,
        icon: "angle-right"
      }));
    },
    segments() {
      return [
        {
          link: this.view.link,
          label: this.view.breadcrumbLabel,
          icon: this.view.icon,
          loading: this.$store.state.isLoading
        },
        ...this.crumbs
      ];
    }
  },
  methods: {
    isLast(index) {
      return this.crumbs.length - 1 === index;
    }
  }
};
</script>

<style>
.k-breadcrumb {
  padding-inline: 0.5rem;
}
.k-breadcrumb-dropdown {
  height: 2.5rem;
  width: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-breadcrumb ol {
  display: none;
  align-items: center;
}

@media screen and (min-width: 30em) {
  .k-breadcrumb ol {
    display: flex;
  }
  .k-breadcrumb-dropdown {
    display: none;
  }
}

.k-breadcrumb-link {
  display: flex;
  align-items: center;
  font-size: var(--text-sm);
  min-width: 0;
  align-self: stretch;
  padding-block: 0.625rem;
  line-height: 1.25rem;
}
.k-breadcrumb li {
  display: flex;
  align-items: center;
  flex-shrink: 3;
  min-width: 0;
}
.k-breadcrumb li:last-child {
  flex-shrink: 1;
}
.k-breadcrumb li:not(:last-child)::after {
  content: "/";
  padding-inline: 0.5rem;
  opacity: 0.5;
  flex-shrink: 0;
}
.k-breadcrumb li:not(:first-child):not(:last-child) {
  max-width: 15vw;
}
.k-breadcrumb-icon {
  margin-inline-end: 0.5rem;
}
.k-breadcrumb-link-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
