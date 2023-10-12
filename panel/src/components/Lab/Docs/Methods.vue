<template>
	<section v-if="methods.length" class="k-lab-docs-section">
		<k-headline class="h3">Methods</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 8rem">Method</th>
					<th>Description</th>
				</thead>
				<tbody>
					<tr v-for="method in methods" :key="method.name">
						<td style="width: 12rem">
							<k-text>
								<code>{{ method.name }}</code>
							</k-text>
						</td>
						<td>
							<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
							<k-text v-html="method.description" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</section>
</template>

<script>
export const props = {
	props: {
		methods: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>
