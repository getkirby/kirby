<template>
	<k-toolbar
		:buttons="buttons"
		class="k-block-options"
		@mousedown.native.prevent
	/>
</template>

<script>
/**
 * Floating options menu for a block that
 * appears when the block is focused/selected.
 *
 * @example <k-block-options :is-editable="true" />
 */
export default {
	props: {
		/**
		 * Block is slected together with other blocks
		 */
		isBatched: Boolean,
		/**
		 * Block can be edited
		 */
		isEditable: Boolean,
		/**
		 * No more blocks can be added
		 */
		isFull: Boolean,
		/**
		 * Block is hidden
		 */
		isHidden: Boolean,
		/**
		 * Block can be merged with other blocks
		 * @since 4.0.0
		 */
		isMergable: Boolean,
		/**
		 * Block can be split into multiple blocks
		 * @since 4.0.0
		 */
		isSplitable: Boolean
	},
	computed: {
		buttons() {
			if (this.isBatched) {
				return [
					{
						icon: "template",
						title: this.$t("copy"),
						click: () => this.$emit("copy")
					},
					{
						when: this.isMergable,
						icon: "merge",
						title: this.$t("merge"),
						click: () => this.$emit("merge")
					},
					{
						icon: "trash",
						title: this.$t("remove"),
						click: () => this.$emit("removeSelected")
					}
				];
			}

			return [
				{
					when: this.isEditable,
					icon: "edit",
					title: this.$t("edit"),
					click: () => this.$emit("open")
				},
				{
					icon: "add",
					title: this.$t("insert.after"),
					disabled: this.isFull,
					click: () => this.$emit("chooseToAppend")
				},
				{
					icon: "trash",
					title: this.$t("delete"),
					click: () => this.$emit("remove")
				},
				{
					icon: "sort",
					title: this.$t("sort.drag"),
					class: "k-sort-handle",
					key: (e) => this.sort(e)
				},
				{
					icon: "dots",
					title: this.$t("more"),
					dropdown: [
						{
							icon: "angle-up",
							label: this.$t("insert.before"),
							disabled: this.isFull,
							click: () => this.$emit("chooseToPrepend")
						},
						{
							icon: "angle-down",
							label: this.$t("insert.after"),
							disabled: this.isFull,
							click: () => this.$emit("chooseToAppend")
						},
						"-",
						{
							when: this.isEditable,
							icon: "edit",
							label: this.$t("edit"),
							click: () => this.$emit("open")
						},
						{
							icon: "refresh",
							label: this.$t("field.blocks.changeType"),
							click: () => this.$emit("chooseToConvert")
						},
						{
							when: this.isSplitable,
							icon: "split",
							label: this.$t("split"),
							click: () => this.$emit("split")
						},
						"-",
						{
							icon: "template",
							label: this.$t("copy"),
							click: () => this.$emit("copy")
						},
						{
							icon: "download",
							label: this.$t("paste.after"),
							disabled: this.isFull,
							click: () => this.$emit("paste")
						},
						"-",
						{
							icon: this.isHidden ? "preview" : "hidden",
							label: this.isHidden ? this.$t("show") : this.$t("hide"),
							click: () => this.$emit(this.isHidden ? "show" : "hide")
						},
						{
							icon: "copy",
							label: this.$t("duplicate"),
							click: () => this.$emit("duplicate")
						},
						"-",
						{
							icon: "trash",
							label: this.$t("delete"),
							click: () => this.$emit("remove")
						}
					]
				}
			];
		}
	},
	methods: {
		open() {
			this.$refs.options.open();
		},
		sort(event) {
			event.preventDefault();

			switch (event.key) {
				case "ArrowUp":
					this.$emit("sortUp");
					break;
				case "ArrowDown":
					this.$emit("sortDown");
					break;
			}
		}
	}
};
</script>

<style>
.k-block-options {
	--toolbar-size: 30px;

	box-shadow: var(--shadow-toolbar);
}
.k-block-options > .k-button:not(:last-of-type) {
	border-inline-end: 1px solid var(--color-background);
}
</style>
