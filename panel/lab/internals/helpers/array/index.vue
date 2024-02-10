<template>
	<k-lab-examples>
		<k-text>
			Access the following array helpers in your Vue components through
			<code>this.$helpers.array</code>
		</k-text>

		<k-lab-example label="$helpers.array.fromObject()">
			<k-text>
				<p>
					Creates an array from an object:
					<code>this.$helpers.array.fromObject(object)</code>
				</p>
				<!-- @code -->
				<k-box theme="code">Input: {{ fromObjectInput }}</k-box>
				<k-box theme="code">
					Result: {{ $helper.array.fromObject(fromObjectInput) }}
				</k-box>
				<!-- @code-end -->
			</k-text>
		</k-lab-example>

		<k-lab-example label="$helpers.array.search()">
			<k-text>
				<p>
					Filters an array by a provided query:
					<code>
						this.$helpers.array.search(array, "{{ searchQuery }}", { min: 2,
						field: "name" })
					</code>
				</p>
				<!-- @code -->
				<k-box theme="code">{{ searchInput }}</k-box>
				<k-input
					type="text"
					placeholder="Query"
					@input="
						searchQuery = $event;
						searchResult = $helper.array.search(searchInput, $event, {
							min: 2,
							field: 'name'
						});
					"
				/>
				<k-box theme="code">{{ searchResult }}</k-box>
				<!-- @code-end -->
			</k-text>
		</k-lab-example>

		<k-lab-example label="$helpers.array.sortBy()">
			<k-text>
				<p>
					Sorts an array by one or more fields and directions:
					<code>this.$helpers.array.sortBy(array, "name desc")</code>
				</p>
				<!-- @code -->
				<k-box theme="code">{{ searchInput }}</k-box>
				<k-box theme="code">{{
					$helper.array.sortBy(searchInput, "name desc")
				}}</k-box>
				<!-- @code-end -->
			</k-text>
		</k-lab-example>

		<k-lab-example label="$helpers.array.split()">
			<k-text>
				<p>
					Splits an array into groups by a delimiter entry:
					<code>this.$helpers.array.split(array, "-")</code>
				</p>
				<!-- @code -->
				<k-box theme="code">{{ splitInput }}</k-box>
				<k-box theme="code">{{ $helper.array.split(splitInput, "-") }}</k-box>
				<!-- @code-end -->
			</k-text>
		</k-lab-example>

		<k-lab-example label="$helpers.array.wrap()">
			<k-text>
				<p>
					Wraps a value in an array (ensures the value will be an array):
					<code>this.$helpers.array.wrap(value)</code>
				</p>
				<k-box theme="code">"aaa", ["aaa"]</k-box>
				<!-- @code -->
				<k-box theme="code">
					{{ $helper.array.wrap("aaa") }},
					{{ $helper.array.wrap(["aaa"]) }}
				</k-box>
				<!-- @code-end -->
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			searchResult: null,
			searchQuery: ""
		};
	},
	computed: {
		fromObjectInput() {
			return { a: 1, b: 2, c: 3 };
		},
		searchInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 2, name: "Homer Simpson" },
				{ id: 3, name: "Jane Doe" }
			];
		},
		splitInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 3, name: "Jane Doe" },
				"-",
				{ id: 2, name: "Homer Simpson" }
			];
		}
	}
};
</script>
