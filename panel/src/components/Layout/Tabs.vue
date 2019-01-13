<template>
  <k-box v-if="tabs.length === 0" text="This page has no blueprint setup yet" theme="info" />
  <k-sections
    v-else-if="tab"
    :parent="parent"
    :blueprint="blueprint"
    :columns="tab.columns"
    @submit="$emit('submit', $event)"
  />
</template>

<script>
export default {
  props: {
    parent: String,
    blueprint: String,
    tabs: Array
  },
  data() {
    return {
      tab: null
    };
  },
  watch: {
    '$route'() {
      this.open();
    },
    blueprint() {
      this.open();
    }
  },
  mounted() {
    this.open();
  },
  methods: {
    open(tabName) {

      if (this.tabs.length === 0) {
        return;
      }

      if (!tabName) {
        tabName = this.$route.hash.replace('#', '');
      }

      if (!tabName) {
        tabName = this.tabs[0].name;
      }

      let nextTab = null;

      this.tabs.forEach(tab => {
        if (tab.name === tabName) {
          nextTab = tab;
        }
      });

      if (!nextTab) {
        nextTab = this.tabs[0];
      }

      this.tab = nextTab;
      this.$emit("tab", this.tab);
    }
  }
};
</script>
