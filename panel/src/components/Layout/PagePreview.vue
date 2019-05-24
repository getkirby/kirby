<template>
  <div class="k-page-preview">
    <iframe
      ref="panel"
      :src="view"
    />
    <iframe
      ref="preview"
      name="k-page-preview"
      @load="onLoaded"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {
      hasAccess: false,
      scroll: 0
    }
  },
  computed: {
    changes() {
      return this.$store.getters["form/values"](this.id);
    },
    id() {
      return this.$store.state.form.current;
    },
    url() {
      return this.$store.getters["preview/url"];
    },
    view() {
      return window.location.href;
    }
  },
  watch: {
    changes() {
      this.load();
    },
    url() {
      this.load();
    }
  },
  mounted() {
    this.canAccess();
    this.load();
    this.$events.$on("model.update", this.load);
    this.$events.$on("page.changeTitle", this.load);
  },
  destroyed() {
    this.$events.$off("model.update", this.load);
    this.$events.$off("page.changeTitle", this.load);
    window.location.href = this.$store.state.preview.after;
  },
  methods: {
    canAccess() {
      try {
        const doc = this.$refs.preview.contentDocument || this.$refs.preview.content.document;
        this.hasAccess = doc.body.innerHTML !== null;
      } catch(err){
      }
    },
    load() {
      if (this.url) {
        // if permitted, store scroll position
        if (this.hasAccess) {
          this.scroll = this.$refs.preview.contentWindow.pageYOffset;
        }

        // create fake form element
        let form = document.createElement("form");
        form.action = this.url;
        form.target = "k-page-preview";

        // create fake input element with JSOn stringified changes
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = "preview";
        input.value = JSON.stringify(this.changes);

        // submit form to iframe
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
      }
    },
    onLoaded() {
      // if permitted, restore scroll position
      if (this.hasAccess) {
        this.$refs.preview.contentWindow.scrollTo({top: this.scroll});
      }
    }
  }
};
</script>

<style lang="scss">
.k-page-preview {
  display: flex;
  align-items: stretch;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  > iframe {
    width: 50%;
    border: 0;
  }

  > iframe:last-child {
    border-left: 1px solid $color-border;
    background: $color-white;
  }
}
</style>
