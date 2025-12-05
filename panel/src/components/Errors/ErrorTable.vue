<template>
	<dl v-if="details.length" class="k-error-details">
		<template v-for="(detail, index) in details" :key="'detail-' + index">
			<dt>
				{{ detail.label }}
			</dt>
			<dd>
				<template v-if="typeof detail.message === 'object'">
					<ul>
						<li v-for="(msg, msgIndex) in detail.message" :key="msgIndex">
							{{ msg }}
						</li>
					</ul>
				</template>
				<template v-else>
					{{ detail.message }}
				</template>
			</dd>
		</template>
	</dl>
</template>

<script>
export default {
	props: {
		details: Array
	}
};
</script>

<style>
.k-error-details {
	background: var(--input-color-back);
	display: block;
	overflow: auto;
	padding: 1rem;
	border-radius: var(--rounded);
	font-size: var(--text-sm);
	line-height: 1.25em;
	margin-top: 0.75rem;
}
.k-error-details dt {
	color: var(--color-red-500);
	margin-bottom: 0.25rem;
}
.k-error-details dd {
	overflow: hidden;
	overflow-wrap: break-word;
	text-overflow: ellipsis;
}
.k-error-details dd:not(:last-of-type) {
	margin-bottom: 1.5em;
}
.k-error-details li {
	white-space: pre-line;
}
.k-error-details li:not(:last-child) {
	border-bottom: 1px solid var(--color-border);
	padding-bottom: 0.25rem;
	margin-bottom: 0.25rem;
}
</style>
