<template>
	<k-drawer
		ref="drawer"
		v-bind="$props"
		class="k-file-drawer"
		@cancel="cancel"
		@option="option"
		@submit="submit"
	>
		<slot name="options" slot="options" />
		<k-file-preview v-bind="preview" @input="onInput" @submit="onSubmit" />

		<div class="k-file-drawer-body">
			<k-drawer-fields
				:fields="fields"
				:value="value"
				@input="input"
				@submit="submit"
			/>
			<k-upload ref="upload" @success="uploaded" />
		</div>
	</k-drawer>
</template>

<script>
import FormDrawer from "./FormDrawer.vue";

export default {
	mixins: [FormDrawer],
	props: {
		model: {
			type: Object
		},
		fields: {
			type: Object
		},
		preview: {
			type: Object
		}
	},
	methods: {
		onInput() {},
		onSubmit() {},
		option(option) {
			switch (option) {
				case "replace":
					this.$refs.upload.open({
						url: this.$panel.urls.api + "/" + this.model.id,
						accept: "." + this.model.extension + "," + this.model.mime,
						multiple: false
					});
					break;
			}
		},
		uploaded() {
			this.$panel.drawer.reload();
		}
	}
};
</script>

<style>
.k-file-drawer .k-drawer-body {
	padding: 0;
}
.k-file-drawer .k-drawer-header {
	background: var(--drawer-color-back);
}
.k-file-drawer .k-file-preview {
	margin-bottom: 0;
	border-radius: 0;
}
.k-file-drawer-body {
	padding: var(--drawer-body-padding);
}
</style>
