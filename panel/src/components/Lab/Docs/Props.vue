<template>
	<section v-if="props.length" class="k-lab-docs-section">
		<k-headline class="h3">Props</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 10rem">Name</th>
					<th style="width: 10rem">Type</th>
					<th style="width: 10rem">Default</th>
					<th>Description</th>
				</thead>
				<tbody>
					<tr v-for="prop in props" :key="prop.name">
						<td>
							<k-text>
								<code>{{ prop.name }}</code>
							</k-text>
						</td>
						<td>
							<k-text class="k-lab-docs-types">
								<code
									v-for="type in prop.type?.split('|')"
									:key="type"
									:data-type="type"
								>
									{{ type }}
								</code>
							</k-text>
						</td>
						<td>
							<k-text v-if="prop.default">
								<code>{{ prop.default }}</code>
							</k-text>
						</td>
						<td class="k-lab-docs-description">
							<k-text
								v-if="prop.description?.length"
								:html="prop.description"
							/>

							<k-box
								v-if="prop.deprecated?.length"
								icon="protected"
								theme="warning"
								class="k-lab-docs-deprecated"
							>
								<k-text :html="'<strong>Deprecated:</strong> ' + prop.deprecated" />
							</k-box>

							<k-text v-if="prop.values?.length">
								<p class="k-lab-docs-values">
									<strong>Values</strong><br />
									<span>
										<code v-for="value in prop.values" :key="value">
											{{ value.replaceAll("`", "") }}
										</code>
									</span>
								</p>
							</k-text>
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
		props: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>
