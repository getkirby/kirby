<template>
  <k-pages-dialog ref="dialog" @submit="submit">
      <span slot="header">
        Select new parent for <b>{{ page.title }}</b>
      </span>
    </k-pages-dialog>
</template>

<script>

export default {
  data() {
    return {
      page: {}
    }
  },
  computed: {
    api() {
      return this.$api.pages.link(this.page.id, "move");
    }
  },
  methods: {
    open(id) {   
      this.$api.pages
        .get(id, {select: "id,title,parent,parents"})
        .then(page => {
          this.page = page;
          this.$refs.dialog.open({
            endpoint: this.api,
            multiple: false,
            search: true,
            parent: page.parents[1] ? page.parents[1].id : null,
            selected: page.parent ? [page.parent.id] : [],
          });
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    submit(selected) {
      const parent = selected[0].id;
      
      this.$api.pages
        .patch(this.api, { parent: parent })
        .then(page => {
          
          // move unsaved changes in content store
          this.$store.dispatch("content/move", [
            "pages/" + this.page.id, 
            "pages/" + page.id
          ]);
          
          const payload = {
            message: ":)",
            event: "page.moved"
          };

          // if in PageView, redirect
          if (
            this.$route.params.path &&
            this.page.id === this.$route.params.path.replace(/\+/g, "/")
          ) {
            payload.route = this.$api.pages.link(page.id);
            delete payload.event;
          }

          this.success(payload);
        });
            
    }
  }
};
</script>

