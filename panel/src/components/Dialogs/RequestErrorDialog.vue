<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		:visible="visible"
		class="k-request-error-dialog"
		size="large"
		@cancel="$emit('cancel')"
	>
		<k-box icon="alert" theme="negative">{{ instance.message }}</k-box>

		<dl class="k-details">
			<div>
				<dt>URL</dt>
				<dd>
					<k-token type="dark">{{ instance.request.url }}</k-token>
				</dd>
			</div>
			<div>
				<dt>Method</dt>
				<dd>
					<k-token type="dark">{{ instance.request.method }}</k-token>
				</dd>
			</div>
			<div>
				<dt>Code</dt>
				<dd>
					<k-token type="purple">{{ instance.response.status }}</k-token>
				</dd>
			</div>
			<div>
				<dt>Type</dt>
				<dd class="k-text">
					<k-token type="object">{{ details.exception }}</k-token>
				</dd>
			</div>
			<div>
				<dt>File</dt>
				<dd>
					<k-token type="dark">{{ details.file }}</k-token>
				</dd>
			</div>
			<div>
				<dt>Line</dt>
				<dd>
					<k-token type="blue">{{ details.line }}</k-token>
				</dd>
			</div>
		</dl>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import RequestError from "@/errors/RequestError.js";

export default {
	mixins: [Dialog],
	props: {
		instance: RequestError
	},
	emits: ["cancel"],
	computed: {
		details() {
			return this.instance.response.json;
		}
	},
	mounted() {
		console.log(this.instance.response);
		console.log(this.instance.request);
	}
};
</script>

<style>
.k-request-error-dialog .k-box {
	margin-bottom: var(--spacing-3);
}
.k-details {
	background: var(--table-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
}
.k-details div {
	display: grid;
	grid-template-columns: 5rem 1fr;
}
.k-details dt,
.k-details dd {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
	display: flex;
	align-items: center;
}
.k-details div:not(:last-child) dt,
.k-details div:not(:last-child) dd {
	border-block-end: 1px solid var(--table-color-border);
}
.k-details dt {
	background: var(--table-color-th-back);
	border-inline-end: 1px solid var(--table-color-border);
}
</style>
