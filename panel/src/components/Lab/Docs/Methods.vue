<template>
	<section v-if="methods.length" class="k-lab-docs-section">
		<k-headline class="h3">Methods</k-headline>

		<div class="k-table">
			<table>
				<thead>
					<tr>
						<th style="width: 10rem">Method</th>
						<th>Description</th>
						<th style="width: 16rem">Params</th>
						<th style="width: 10rem">Returns</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="method in methods" :key="method.name">
						<td>
							<k-text>
								<code>{{ method.name }}</code>
								<div v-if="method.since?.length" class="k-lab-docs-since">
									since {{ method.since }}
								</div>
							</k-text>
						</td>
						<td>
							<k-lab-docs-warning
								title="Deprecated"
								:text="method.deprecated"
							/>
							<k-text :html="method.description" />
						</td>
						<td>
							<k-lab-docs-params :params="method.params" />
						</td>
						<td>
							<k-lab-docs-types :types="[method.returns]" />
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
