<template>
  <a
    v-if="to && !disabled"
    ref="link"
    :disabled="disabled"
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
  <span
    v-else
    :title="title"
    class="k-link"
    data-disabled
  >
    <slot />
  </span>
</template>

<script>
export default {
  props: {
    disabled: Boolean,
    rel: String,
    tabindex: String,
    target: String,
    title: String,
    to: String,
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
      if (this.$route !== undefined && this.to[0] === '/') {
        return (this.$router.options.url || '') + this.to;
      }
      return this.to;
    }
  },
  methods: {
    isRoutable(e) {
      // the router is not installed
      if (this.$route === undefined) {
        return false;
      }

      // don't redirect with control keys
      if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) {
        return false;
      }

      // don't redirect when preventDefault called
      if (e.defaultPrevented) {
        return false;
      }

      // don't redirect on right click
      if (e.button !== undefined && e.button !== 0) {
        return false;
      }

      // don't redirect if a target is set
      if (this.target) {
        return false;
      }

      return true;
    },
    onClick(event) {
      if (this.disabled === true) {
        event.preventDefault();
        return false;
      }

      if (this.isRoutable(event)) {
        event.preventDefault();
        this.$router.push(this.to);
      }

      this.$emit("click", event);
    },
    focus() {
      this.$refs.link.focus();
    }
  }
};
</script>
