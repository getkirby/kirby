<template>
	<k-overlay
		ref="overlay"
		:autofocus="autofocus"
		:dimmed="!nested"
		:loading="loading"
		:visible="visible"
		class="k-drawer-overlay"
		@cancel="cancel"
		@ready="ready"
	>
		<k-drawer-box :id="id" :nested="nested">
			<k-drawer-form @submit="submit">
				<k-drawer-notification
					v-if="notification"
					v-bind="notification"
					@close="notification = null"
				/>
				<k-drawer-header
					:breadcrumb="breadcrumb"
					:icon="icon"
					:tab="tab"
					:tabs="tabs"
					:title="title"
					@openCrumb="openCrumb"
					@openTab="openTab"
				>
					<slot name="options" />
				</k-drawer-header>
				<k-drawer-body>
					<k-drawer-fields
						:fields="fieldset"
						:value="model"
						@input="input"
						@invalid="invalid"
						@submit="submit"
					/>
				</k-drawer-body>
			</k-drawer-form>
		</k-drawer-box>
	</k-overlay>
</template>

<script>
import Drawer from "./Drawer.vue";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	data() {
		return {
			fieldset: {},
			// Since fiber drawers don't update their `value` prop
			// on an emitted `input` event, we need to ensure a local
			// state of all updated values
			model: this.value
		};
	},
	watch: {
		tab() {
			this.fieldset = this.tab.fields;
			this.$refs.overlay.focus();
		},
		value(value) {
			this.model = value;
		}
	},
	methods: {
		input(value) {
			this.model = value;
			this.$emit("input", this.model);
		},
		invalid() {
			this.$emit("invalid", this.model);
		},
		submit() {
			this.$emit("submit", this.model);
			this.close();
		}
	}
};
</script>
