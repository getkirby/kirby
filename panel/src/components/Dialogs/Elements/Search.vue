<template>
	<k-input
		ref="input"
		:autofocus="autofocus"
		:placeholder="placeholder"
		:value="value"
		class="k-dialog-search"
		icon="search"
		type="search"
		@input="$emit('search', $event)"
	>
		<template #icon>
			<k-button :icon="icon" tabindex="-1" @click="onIconClick" />
		</template>
	</k-input>
</template>

<script>
/**
 * @displayName DialogSearch
 * @since 4.0.0
 */
export default {
	props: {
		autofocus: {
			default: true,
			type: Boolean
		},
		placeholder: {
			type: String
		},
		value: {
			type: String
		}
	},
	emits: ["search"],
	computed: {
		icon() {
			return this.value ? "cancel-small" : "search";
		}
	},
	methods: {
		onIconClick() {
			if (this.value) {
				this.$emit("search", null);
			} else {
				this.$refs.input.focus();
			}
		}
	}
};
</script>

<style>
/** Dialog search field **/
.k-dialog-search {
	margin-bottom: 0.75rem;
	--input-color-border: transparent;
	--input-color-back: light-dark(var(--color-gray-300), var(--color-gray-950));
}
</style>
