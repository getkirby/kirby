<script>
import ModelBase from "./ModelBase.vue";

/**
 * @internal
 */
export default {
	extends: ModelBase,
	props: {
		buttons: Array,
		next: Object,
		prev: Object
	},
	mounted() {
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
		this.$events.on("view.save", this.onViewSave);
	},
	unmounted() {
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("view.save", this.onViewSave);
	},
	methods: {
		onViewSave(e) {
			e?.preventDefault?.();
			this.onSubmit();
		},
		toNext(e) {
			if (this.next && e.target.localName === "body") {
				this.$go(this.next.link);
			}
		},
		toPrev(e) {
			if (this.prev && e.target.localName === "body") {
				this.$go(this.prev.link);
			}
		}
	}
};
</script>
