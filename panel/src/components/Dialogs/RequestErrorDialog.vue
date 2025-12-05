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
		<k-stack>
			<k-box icon="alert" theme="negative">{{ instance.message }}</k-box>

			<k-stack style="gap: var(--spacing-3)">
				<k-headline>Error</k-headline>
				<dl class="k-details">
					<div>
						<dt>URL</dt>
						<dd>
							<k-code-token type="black">{{
								instance.request.url
							}}</k-code-token>
						</dd>
					</div>
					<div>
						<dt>Method</dt>
						<dd>
							<k-code-token type="black">{{
								instance.request.method
							}}</k-code-token>
						</dd>
					</div>
					<div>
						<dt>Code</dt>
						<dd>
							<k-code-token type="blue">{{
								instance.response.status
							}}</k-code-token>
						</dd>
					</div>
					<div>
						<dt>Type</dt>
						<dd class="k-text">
							<k-code-token type="object">{{ details.exception }}</k-code-token>
						</dd>
					</div>
					<div v-if="details.file">
						<dt>File</dt>
						<dd>
							<k-code-token type="black">{{ details.file }}</k-code-token>
						</dd>
					</div>
					<div v-if="details.line">
						<dt>Line</dt>
						<dd>
							<k-code-token type="purple">{{ details.line }}</k-code-token>
						</dd>
					</div>
				</dl>
			</k-stack>

			<k-stack
				v-if="$panel.debug && details.trace"
				style="gap: var(--spacing-3)"
			>
				<k-headline>Trace</k-headline>

				<div class="k-trace">
					<ol>
						<li v-for="(item, index) in details.trace" :key="index">
							<a
								:href="
									item.file ? `cursor://file/${item.file}:${item.line}` : null
								"
							>
								<p v-if="item.file">
									<span style="color: var(--color-gray-200)">{{
										item.relativeRoot
									}}</span>
									<span>:</span>
									<span style="color: var(--color-purple-400)">{{
										item.line
									}}</span>
								</p>
								<p v-else>Internal</p>
								<p>
									<span
										v-if="item.class"
										style="color: var(--color-yellow-400)"
									>
										{{ item.class }}
									</span>
									<span v-if="item.type">
										{{ item.type }}
									</span>
									<span style="color: var(--color-blue-400)">
										{{ item.function }}
									</span>
									<span>()</span>
								</p>
							</a>
						</li>
					</ol>
				</div>
			</k-stack>
		</k-stack>
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
.k-details {
	background: var(--table-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
}
.k-details > div {
	display: grid;
	grid-template-columns: 5rem 1fr;
}
.k-details dt,
.k-details dd {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
	display: flex;
	align-items: center;
	font-family: var(--font-mono);
}
.k-details div:not(:last-child) dt,
.k-details div:not(:last-child) dd {
	border-block-end: 1px solid var(--table-color-border);
}
.k-details dt {
	background: var(--table-color-th-back);
	border-inline-end: 1px solid var(--table-color-border);
	font-size: var(--text-xs);
}
k-stack {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-6);
	container-type: inline-size;
}
.k-trace {
	line-height: 1.5;
	background: var(--color-black);
	border-radius: var(--rounded);
	font-size: 0.7rem;
	font-family: var(--font-mono);
	overflow-x: auto;
	overflow-y: hidden;
	padding: var(--spacing-3);
}
.k-trace ol {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-3);
	list-style: numeric;
	margin-left: 5ch;
}
.k-trace li::marker {
	color: var(--color-gray-600);
}
.k-trace li a {
	display: block;
}
.k-trace p {
	color: var(--color-gray-600);
	display: flex;
	white-space: nowrap;
	min-width: 0;
}
</style>
