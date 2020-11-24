<template>
  <div class="k-sandbox">
    <nav class="k-sandbox-menu">
      <k-headline>Kirby UI</k-headline>
      <ul>
        <li v-for="(item, index) in menu" :key="index">
          <k-link
            :aria-current="component === item.slug"
            :to="'/sandbox/' + item.slug"
          >
            <k-icon type="box" /> {{ item.title }}
          </k-link>
        </li>
      </ul>
    </nav>
    <div class="k-sandbox-preview">
      <iframe :src="'/sandbox/preview/' + component" class="k-sandbox-iframe" />
    </div>
    <div class="k-sandbox-code">
      <pre>{{ code }}</pre>
    </div>
  </div>
</template>

<script>
import components from "./components.js";

export default {
  data() {
    return {
      components
    }
  },
  computed: {
    code() {
      let code = null;
      this.menu.forEach(component => {
        if (component.slug === this.component) {
          code = component.html;
        }
      })

      return code;
    },
    component() {
      return this.$route.params.component;
    },
    menu() {
      return components.map(component => {
        return {
          title: component.key.replace(".vue", "").replace("./", ""),
          slug: this.$helper.string.camelToKebab(component.name),
          html: component.html
        }
      });
    }
  }
};

</script>

<style lang="scss">
.k-sandbox {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: grid;
  grid-template-columns: 15rem auto;
  grid-template-rows: auto 20rem;
  grid-template-areas: "menu preview"
                       "menu code";
  height: 100%;
}
.k-sandbox-menu {
  background: $color-white;
  grid-area: menu;
}
.k-sandbox-preview {
  position: relative;
  grid-area: preview;
}
.k-sandbox-iframe {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  border: 0;
  width: 100%;
  height: 100%;
}
.k-sandbox-menu .k-headline {
  padding: .75rem;
}
.k-sandbox-menu a {
  display: flex;
  align-items: center;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: $text-sm;
  padding: .325rem .75rem;
}
.k-sandbox-menu a .k-icon {
  margin-right: .5rem;
}
.k-sandbox-menu a[aria-current] {
  background: $color-blue-200;
}
.k-sandbox-code {
  padding: 1.5rem;
  grid-area: code;
  background: $color-gray-900;
  color: $color-white;
  font-size: 1.25em;
  line-height: 1.25em;
  font-family: $font-mono;
  overflow: auto;
}
</style>
