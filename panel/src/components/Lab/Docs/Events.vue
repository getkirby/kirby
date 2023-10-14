<template>
	<section v-if="events.length" class="k-lab-docs-section">
		<k-headline class="h3">Events</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 10rem">Event</th>
					<th>Description</th>
					<th v-if="events.filter((event) => event.properties.length).length">
						Properties
					</th>
				</thead>
				<tbody>
					<tr v-for="event in events" :key="event.name">
						<td>
							<k-text>
								<code>{{ event.name }}</code>
							</k-text>
						</td>
						<td>
							<k-text :html="event.description" />
						</td>
						<td v-if="event.properties.length">
							<k-lab-docs-params :params="event.properties" />
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
		events: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>
