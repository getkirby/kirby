<template>
	<section v-if="events.length" class="k-lab-docs-section">
		<k-headline class="h3">Events</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 10rem">Event</th>
					<th>Description</th>
				</thead>
				<tbody>
					<tr v-for="event in events" :key="event.name">
						<td style="width: 12rem">
							<k-text>
								<code>{{ event.name }}</code>
							</k-text>
						</td>
						<td>
							<k-text :html="event.description" />
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
