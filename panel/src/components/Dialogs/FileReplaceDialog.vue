<template>
  <k-dialog
    ref="dialog"
    :button="$t('replace')"
    theme="negative"
    icon="upload"
    @submit="submit"
  >
    <k-text>
      The following file(s) already exist(s):
      {{ files }}
    </k-text>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      files: []
    };
  },
  methods: {
    open(files) {
      this.files = files;  
      
      this.$refs.dialog.open();
    },
    submit() {
      
    }
  }
};
</script>
