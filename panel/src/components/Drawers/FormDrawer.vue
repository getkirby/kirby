<template>
	<k-overlay ref="drawer" type="drawer" @cancel="cancel">
		<form
			class="k-form-drawer k-drawer"
			method="dialog"
			@submit.prevent="submit"
		>
			<k-drawer-notification />
			<k-drawer-header>
				<slot name="options" />
			</k-drawer-header>
			<k-drawer-body>
				<k-drawer-fields
					:fields="$panel.drawer.tab?.fields"
					:value="value"
					@input="input"
					@submit="submit"
				/>
			</k-drawer-body>
		</form>
	</k-overlay>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields]
};
</script>
