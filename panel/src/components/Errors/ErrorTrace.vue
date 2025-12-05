<template>
	<div class="k-error-trace">
		<ol class="k-stack">
			<li v-for="(item, index) in trace" :key="'trace-' + index">
				<a :href="item.editor !== false ? item.editor : null">
					<p v-if="item.file">
						<span style="color: var(--color-gray-200)">{{
							item.relativeRoot
						}}</span>
						<span>:</span>
						<span style="color: var(--color-purple-400)">{{ item.line }}</span>
					</p>
					<p v-else>Internal</p>
					<p>
						<span v-if="item.class" style="color: var(--color-yellow-400)">
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
</template>

<script>
export default {
	props: {
		trace: Array
	}
};
</script>

<style>
.k-error-trace {
	line-height: 1.5;
	background: var(--color-black);
	border-radius: var(--rounded);
	font-size: 0.7rem;
	font-family: var(--font-mono);
	overflow-x: auto;
	overflow-y: hidden;
	padding: var(--spacing-3);
}
.k-error-trace ol {
	gap: var(--spacing-3);
	list-style: numeric;
	margin-left: 5ch;
}
.k-error-trace li::marker {
	color: var(--color-gray-600);
}
.k-error-trace li a {
	display: block;
}
.k-error-trace p {
	color: var(--color-gray-600);
	display: flex;
	white-space: nowrap;
	min-width: 0;
}
</style>
