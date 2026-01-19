<template>
	<k-toolbar
		:buttons="buttons"
		class="k-block-options"
		data-inline="true"
		@mousedown.prevent
	/>
</template>

<script>
export const props = {
	props: {
		/**
		 * Block is slected together with other blocks
		 */
		isBatched: Boolean,
		/**
		 * No more blocks can be added
		 */
		isFull: Boolean,
		/**
		 * Block is displayed as hidden
		 */
		isHidden: Boolean,
		/**
		 * Block can be merged with all other selected blocks
		 * @since 4.0.0
		 */
		isMergable: Boolean
	}
};

/**
 * Floating options menu for a block that
 * appears when the block is focused/selected.
 *
 * @example <k-block-options :is-editable="true" />
 */
export default {
	mixins: [props],
	props: {
		/**
		 * Block can be edited
		 */
		isEditable: Boolean,
		/**
		 * Block can be split into multiple blocks
		 * @since 4.0.0
		 */
		isSplitable: Boolean
	},
	emits: [
		"chooseToAppend",
		"chooseToConvert",
		"chooseToPrepend",
		"copy",
		"duplicate",
		"hide",
		"merge",
		"open",
		"paste",
		"remove",
		"removeSelected",
		"show",
		"split",
		"sortDown",
		"sortUp"
	],
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
			switch (event.key) {
				case "ArrowUp":
					event.preventDefault();
					this.$emit("sortUp");
					break;
				case "ArrowDown":
					event.preventDefault();
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
	border: 1px solid light-dark(var(--color-border), var(--color-gray-900));
	box-shadow: var(--shadow-xl);
}
.k-block-options > .k-button:not(:last-of-type) {
	border-inline-end: 1px solid var(--toolbar-border);
}
.k-block-options .k-dropdown {
	margin-top: 0.5rem;
}
</style>
