<template>
  <a
    v-if="to && !disabled"
    ref="link"
    :href="href"
    :rel="relAttr"
    :tabindex="tabindex"
    :target="target"
    :title="title"
    class="k-link"
    v-on="listeners"
  >
    <slot />
  </a>
  <span v-else :title="title" class="k-link" data-disabled>
    <slot />
  </span>
</template>

<script>
import tab from "@/mixins/tab.js";

/**
 * Our Link component is a wrapper around a native HTML `a` element, but it is also used to swap this with a Vue `router-link` element in our application whenever it makes sense. It comes with a set of additional useful helpers.
 * @example <k-link to="https://getkirby.com">Kirby Website</k-link>
 */
export default {
  mixins: [tab],
  props: {
    /**
     * Disabled links will have no pointer events and cannot be clicked or focused.
     */
    disabled: Boolean,
    rel: String,
    tabindex: [String, Number],
    target: String,
    title: String,
    /**
     * The path or absolute URL for the link.
     */
    to: [String, Function]
  },
  data() {
    return {
      relAttr: this.target === "_blank" ? "noreferrer noopener" : this.rel,
      listeners: {
        ...this.$listeners,
        click: this.onClick
      }
    };
  },
  computed: {
    href() {
      if (typeof this.to === "function") {
        return "";
      }

      if (this.to[0] === "/" && !this.target) {
        return this.$url(this.to);
      }

      return this.to;
    }
  },
  methods: {
    isRoutable(e) {
      // don't route with control keys
      if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) {
        return false;
      }

      // don't route when preventDefault called
      if (e.defaultPrevented) {
        return false;
      }

      // don't route on right click
      if (e.button !== undefined && e.button !== 0) {
        return false;
      }

      // don't route if a target is set
      if (this.target) {
        return false;
      }

      // don't route if it's an absolute link
      if (typeof this.href === "string" && this.href.indexOf("://") > 0) {
        return false;
      }

      return true;
    },
    onClick(event) {
      if (this.disabled === true) {
        event.preventDefault();
        return false;
      }

      if (typeof this.to === "function") {
        event.preventDefault();
        this.to();
      }

      if (this.isRoutable(event)) {
        event.preventDefault();
        this.$go(this.to);
      }

      this.$emit("click", event);
    }
  }
};
</script>

<style>
.k-link {
  outline: none;
}
</style>
