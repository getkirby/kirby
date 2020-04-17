<script>
import TableUrlCell from "./TableUrlCell.vue";

export default {
  extends: TableUrlCell,
  computed: {
    link() {
      return this.value && this.value.length > 0 ? 'mailto:' + this.value : null;
    }
  }
}
</script>
