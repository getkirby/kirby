<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="Not found">
			<k-button-group>
				<k-button variant="filled" @click="$panel.view.open('/does-not-exist')">
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/does-not-exist')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/does-not-exist')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('does-not-exist')">
					Request
				</k-button>
				<k-button variant="filled" @click="apiCall()"> API call </k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="Backend issues">
			<k-button-group>
				<k-button variant="filled" @click="$panel.view.open('/lab/errors')">
					View
				</k-button>
				<k-button variant="filled" @click="$panel.dialog.open('/lab/errors')">
					Dialog
				</k-button>
				<k-button variant="filled" @click="$panel.drawer.open('/lab/errors')">
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('requests/lab/errors')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="JS issues">
			<k-button-group>
				<k-button variant="filled" @click="throwError()">
					Throw error (console)
				</k-button>
				<k-button variant="filled" @click="throwAndCatchError()">
					Throw & catch error
				</k-button>
			</k-button-group>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		apiCall() {
			try {
				this.$panel.api.get("/does-not-exist");
			} catch (e) {
				this.$panel.error(e);
			}
		},
		request(path) {
			try {
				this.$panel.get(path);
			} catch (e) {
				this.$panel.error(e);
			}
		},
		throwAndCatchError() {
			try {
				throw new Error("This is a custom error");
			} catch (e) {
				this.$panel.error(e);
			}
		},
		throwError() {
			throw new Error("This is a custom error");
		}
	}
};
</script>
